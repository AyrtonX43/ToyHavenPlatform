# Quick Start Guide - Continue ToyShop Implementation

## Prerequisites

1. **Start your database server** (MySQL/MariaDB via XAMPP)
2. **Run migrations**:
   ```bash
   php artisan migrate
   ```

---

## Implementation Order (Recommended)

### Step 1: Enhance CheckoutController (Highest Priority)
**File:** `app/Http/Controllers/Toyshop/CheckoutController.php`

Add after line 560 in `paymentReturn()` method:
```php
// Generate receipt and send notifications
$receiptService = app(\App\Services\ReceiptService::class);
$receiptService->generateReceipt($order);

$order->user->notify(new \App\Notifications\PaymentSuccessNotification($order));
$order->user->notify(new \App\Notifications\OrderCreatedNotification($order));
```

Add after line 489 in `checkPaymentStatus()` method:
```php
// Generate receipt
$receiptService = app(\App\Services\ReceiptService::class);
$receiptService->generateReceipt($order);

$order->user->notify(new \App\Notifications\PaymentSuccessNotification($order));
```

---

### Step 2: Update Seller OrderController
**File:** `app/Http/Controllers/Seller/OrderController.php`

Add after line 94 in `updateStatus()` method:
```php
// Send notifications based on status
if ($request->status === 'shipped') {
    $order->user->notify(new \App\Notifications\OrderShippedNotification($order));
} elseif ($request->status === 'delivered') {
    $order->user->notify(new \App\Notifications\OrderDeliveredNotification($order));
    
    // Schedule delivery confirmation reminder for 3 days later
    \App\Jobs\SendDeliveryConfirmationReminder::dispatch($order)
        ->delay(now()->addDays(3));
}
```

---

### Step 3: Implement DeliveryConfirmationController
**File:** `app/Http/Controllers/Toyshop/DeliveryConfirmationController.php`

```php
<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfirmation;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryConfirmationController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->isDelivered()) {
            return back()->with('error', 'Order must be delivered before confirmation.');
        }

        if ($order->isDeliveryConfirmed()) {
            return back()->with('info', 'Delivery already confirmed.');
        }

        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $imagePath = $request->file('proof_image')->store(
            "delivery_proofs/{$order->user_id}",
            'public'
        );

        DeliveryConfirmation::create([
            'order_id' => $order->id,
            'proof_image_path' => $imagePath,
            'notes' => $request->notes,
            'confirmed_at' => now(),
            'auto_confirmed' => false,
        ]);

        // Schedule review request for 1 day later
        \App\Jobs\SendReviewRequestJob::dispatch($order)->delay(now()->addDay());

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Delivery confirmed successfully! You can now review this product.');
    }

    public function show($orderId)
    {
        $order = Order::with('deliveryConfirmation')
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('toyshop.orders.delivery-confirmation', compact('order'));
    }
}
```

---

### Step 4: Implement OrderDisputeController
**File:** `app/Http/Controllers/OrderDisputeController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderDisputeController extends Controller
{
    public function create($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->hasActiveDispute()) {
            return back()->with('error', 'This order already has an active dispute.');
        }

        return view('toyshop.orders.report-issue', compact('order'));
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->hasActiveDispute()) {
            return back()->with('error', 'This order already has an active dispute.');
        }

        $request->validate([
            'type' => 'required|in:not_received,damaged,wrong_item,incomplete,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $evidenceImages = [];
        if ($request->hasFile('evidence_images')) {
            foreach ($request->file('evidence_images') as $image) {
                $path = $image->store("disputes/{$order->id}", 'public');
                $evidenceImages[] = $path;
            }
        }

        $dispute = OrderDispute::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'seller_id' => $order->seller_id,
            'type' => $request->type,
            'description' => $request->description,
            'evidence_images' => $evidenceImages,
            'status' => 'open',
        ]);

        // Notify seller and moderators
        $order->seller->user->notify(new \App\Notifications\DisputeCreatedNotification($dispute));
        
        // Notify all moderators
        $moderators = \App\Models\User::where('role', 'moderator')->orWhere('role', 'admin')->get();
        foreach ($moderators as $moderator) {
            $moderator->notify(new \App\Notifications\DisputeCreatedNotification($dispute));
        }

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Dispute created successfully. A moderator will review your case.');
    }

    public function index()
    {
        $disputes = OrderDispute::with(['order', 'seller'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('toyshop.disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = OrderDispute::with(['order.items', 'seller', 'assignedTo'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.disputes.show', compact('dispute'));
    }
}
```

---

### Step 5: Add Routes
**File:** `routes/web.php`

Add at the end of the file:
```php
// Delivery Confirmation Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/orders/{order}/confirm-delivery', [\App\Http\Controllers\Toyshop\DeliveryConfirmationController::class, 'store'])
        ->name('orders.confirm-delivery');
    Route::get('/orders/{order}/delivery-confirmation', [\App\Http\Controllers\Toyshop\DeliveryConfirmationController::class, 'show'])
        ->name('orders.delivery-confirmation');
});

// Order Dispute Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/report-issue', [\App\Http\Controllers\OrderDisputeController::class, 'create'])
        ->name('orders.report-issue');
    Route::post('/orders/{order}/disputes', [\App\Http\Controllers\OrderDisputeController::class, 'store'])
        ->name('disputes.store');
    Route::get('/disputes', [\App\Http\Controllers\OrderDisputeController::class, 'index'])
        ->name('disputes.index');
    Route::get('/disputes/{dispute}', [\App\Http\Controllers\OrderDisputeController::class, 'show'])
        ->name('disputes.show');
});

// Receipt Download
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/receipt', function($id) {
        $order = \App\Models\Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        $receiptService = app(\App\Services\ReceiptService::class);
        return $receiptService->downloadReceipt($order);
    })->name('orders.receipt');
});

// Moderator Routes
Route::prefix('moderator')->middleware(['moderator'])->name('moderator.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Moderator\DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Orders
    Route::get('/orders', [\App\Http\Controllers\Moderator\OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Moderator\OrderController::class, 'show'])
        ->name('orders.show');
    Route::put('/orders/{order}/status', [\App\Http\Controllers\Moderator\OrderController::class, 'updateStatus'])
        ->name('orders.update-status');
    
    // Disputes
    Route::get('/disputes', [\App\Http\Controllers\Moderator\DisputeController::class, 'index'])
        ->name('disputes.index');
    Route::get('/disputes/{dispute}', [\App\Http\Controllers\Moderator\DisputeController::class, 'show'])
        ->name('disputes.show');
    Route::post('/disputes/{dispute}/assign', [\App\Http\Controllers\Moderator\DisputeController::class, 'assign'])
        ->name('disputes.assign');
    Route::post('/disputes/{dispute}/resolve', [\App\Http\Controllers\Moderator\DisputeController::class, 'resolve'])
        ->name('disputes.resolve');
});
```

---

### Step 6: Create Basic Views

#### Delivery Confirmation Form
**File:** `resources/views/toyshop/orders/confirm-delivery.blade.php`

```blade
@extends('layouts.toyshop')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Confirm Delivery</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-gray-600 text-sm">Please upload a photo as proof of delivery</p>
        </div>

        <form action="{{ route('orders.confirm-delivery', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Proof of Delivery Photo *</label>
                <input type="file" name="proof_image" accept="image/*" required 
                    class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Upload a clear photo showing the delivered package</p>
                @error('proof_image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="3" 
                    class="w-full border rounded px-3 py-2" 
                    placeholder="Any additional comments..."></textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Confirm Delivery
                </button>
                <a href="{{ route('orders.show', $order->id) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
```

#### Report Issue Form
**File:** `resources/views/toyshop/orders/report-issue.blade.php`

```blade
@extends('layouts.toyshop')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Report an Issue</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-gray-600 text-sm">Describe the issue with your order</p>
        </div>

        <form action="{{ route('disputes.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Issue Type *</label>
                <select name="type" required class="w-full border rounded px-3 py-2">
                    <option value="">Select issue type</option>
                    <option value="not_received">Not Received</option>
                    <option value="damaged">Damaged Item</option>
                    <option value="wrong_item">Wrong Item</option>
                    <option value="incomplete">Incomplete Order</option>
                    <option value="other">Other</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description * (minimum 20 characters)</label>
                <textarea name="description" rows="5" required 
                    class="w-full border rounded px-3 py-2" 
                    placeholder="Please provide detailed information about the issue..."></textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Evidence Photos (Optional)</label>
                <input type="file" name="evidence_images[]" accept="image/*" multiple 
                    class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Upload photos showing the issue (max 5MB each)</p>
                @error('evidence_images.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                    Submit Report
                </button>
                <a href="{{ route('orders.show', $order->id) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

### Step 7: Update Order Show Page
**File:** `resources/views/toyshop/orders/show.blade.php`

Add after the order details section:

```blade
{{-- Receipt Download --}}
@if($order->hasReceipt())
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="font-semibold mb-3">Receipt</h3>
    <a href="{{ route('orders.receipt', $order->id) }}" 
        class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Download Receipt
    </a>
</div>
@endif

{{-- Delivery Confirmation --}}
@if($order->isDelivered())
    @if($order->isDeliveryConfirmed())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-green-800 mb-2">✓ Delivery Confirmed</h3>
            <p class="text-sm text-green-700">
                Confirmed on {{ $order->deliveryConfirmation->confirmed_at->format('F d, Y') }}
            </p>
        </div>
    @elseif(!$order->hasActiveDispute())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-3">Action Required</h3>
            <p class="text-sm text-yellow-700 mb-4">
                Please confirm that you have received this order by uploading a photo.
            </p>
            <div class="flex gap-3">
                <a href="{{ route('orders.confirm-delivery', $order->id) }}" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Confirm Delivery
                </a>
                <a href="{{ route('orders.report-issue', $order->id) }}" 
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Report Issue
                </a>
            </div>
        </div>
    @endif
@endif

{{-- Active Dispute --}}
@if($order->hasActiveDispute())
<div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
    <h3 class="font-semibold text-red-800 mb-2">Dispute Active</h3>
    <p class="text-sm text-red-700 mb-3">
        You have reported an issue with this order. Status: {{ $order->activeDispute->getStatusLabel() }}
    </p>
    <a href="{{ route('disputes.show', $order->activeDispute->id) }}" 
        class="text-red-600 hover:text-red-800 text-sm font-medium">
        View Dispute →
    </a>
</div>
@endif
```

---

## Testing Checklist

1. [ ] Start XAMPP MySQL
2. [ ] Run `php artisan migrate`
3. [ ] Create a test order and complete payment
4. [ ] Check if receipt PDF is generated
5. [ ] Mark order as delivered (as seller)
6. [ ] Confirm delivery with photo upload (as buyer)
7. [ ] Create a dispute (as buyer)
8. [ ] View dispute (as moderator)

---

## Common Issues & Solutions

### Issue: "Class 'Barryvdh\DomPDF\Facade\Pdf' not found"
**Solution:** Run `composer dump-autoload`

### Issue: "Storage directory not writable"
**Solution:** Run `php artisan storage:link` and check permissions

### Issue: "Moderator middleware not found"
**Solution:** Clear config cache: `php artisan config:clear`

---

## Next Priority Features

After completing the above, implement in this order:
1. Moderator Dashboard
2. Moderator Dispute Management
3. Background Jobs (auto-confirm, review requests)
4. Email Notifications
5. Seller Registration Enhancement

---

For full details, see `IMPLEMENTATION_SUMMARY.md`
