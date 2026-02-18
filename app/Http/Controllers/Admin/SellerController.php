<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerDocument;
use App\Notifications\SellerSuspendedNotification;
use App\Notifications\SellerApprovedNotification;
use App\Notifications\SellerRejectedNotification;
use App\Notifications\DocumentRejectedNotification;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Seller::with(['user', 'products', 'orders']);
        
        // Filter by verification status
        if ($request->status) {
            $query->where('verification_status', $request->status);
        }
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->active == '1');
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('business_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        // Date filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $sellers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.sellers.index', compact('sellers'));
    }

    public function show($id)
    {
        $seller = Seller::with([
            'user', 
            'products' => function($q) {
                $q->withCount('orderItems')->orderBy('created_at', 'desc');
            }, 
            'orders' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            }, 
            'reviews' => function($q) {
                $q->with('user')->orderBy('created_at', 'desc')->limit(10);
            },
            'documents'
        ])->findOrFail($id);
        
        // Calculate statistics
        $stats = [
            'total_products' => $seller->products()->count(),
            'active_products' => $seller->products()->where('status', 'active')->count(),
            'pending_products' => $seller->products()->where('status', 'pending')->count(),
            'total_orders' => $seller->orders()->count(),
            'total_sales' => $seller->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'total_revenue' => $seller->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'average_order_value' => $seller->orders()->where('payment_status', 'paid')->avg('total_amount'),
        ];
        
        return view('admin.sellers.show', compact('seller', 'stats'));
    }
    
    public function edit($id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        return view('admin.sellers.edit', compact('seller'));
    }
    
    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        
        $request->validate([
            'business_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        
        $seller->update($request->only([
            'business_name', 'email', 'phone', 'address', 
            'city', 'province', 'postal_code', 'description'
        ]));
        
        return redirect()->route('admin.sellers.show', $seller->id)
            ->with('success', 'Seller information updated successfully!');
    }

    public function approve($id)
    {
        $seller = Seller::with(['user', 'documents'])->findOrFail($id);
        
        // Check if all required documents are approved
        $requiredDocsCount = $seller->is_verified_shop ? 3 : 1; // ID + Business Permit + Bank Account for verified, or just ID for basic
        $approvedDocsCount = $seller->documents()->where('status', 'approved')->count();
        
        if ($approvedDocsCount < $requiredDocsCount) {
            return back()->with('error', 'Cannot approve seller. Please approve all required verification documents first.');
        }
        
        // Sync: Approve all pending documents when seller is approved
        $seller->documents()->where('status', 'pending')->update(['status' => 'approved']);
        
        $seller->update(['verification_status' => 'approved']);
        
        // Send notification to seller
        if ($seller->user) {
            $seller->user->notify(new SellerApprovedNotification($seller->business_name));
        }
        
        return back()->with('success', 'Seller approved successfully and notified via email!');
    }

    public function reject(Request $request, $id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        
        $request->validate([
            'rejection_type' => 'required|string',
            'reason' => 'required|string|max:1000',
        ]);
        
        // Combine rejection type and reason for storage
        $rejectionReason = $request->reason;
        if ($request->rejection_type && $request->rejection_type !== 'other') {
            $rejectionTypes = [
                'incomplete_documents' => 'Incomplete or Missing Documents',
                'invalid_documents' => 'Invalid or Unclear Documents',
                'business_info_mismatch' => 'Business Information Mismatch',
                'suspicious_activity' => 'Suspicious Activity Detected',
                'policy_violation' => 'Policy Violation',
                'duplicate_account' => 'Duplicate Account',
            ];
            $rejectionReason = 'Reason: ' . ($rejectionTypes[$request->rejection_type] ?? $request->rejection_type) . "\n\n" . $request->reason;
        }
        
        $seller->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $rejectionReason
        ]);
        
        // Sync: Reject all pending documents when seller is rejected
        $seller->documents()->where('status', 'pending')->update([
            'status' => 'rejected',
            'rejection_reason' => 'Seller application rejected: ' . $rejectionReason
        ]);
        
        // Send notification to seller
        if ($seller->user) {
            $seller->user->notify(new SellerRejectedNotification($rejectionReason, $seller->business_name));
        }
        
        return back()->with('success', 'Seller rejected and notified via email.');
    }

    public function suspend(Request $request, $id)
    {
        $seller = Seller::with('user')->findOrFail($id);

        // Prevent suspending your own seller account
        if ($seller->user_id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own seller account.');
        }

        $request->validate([
            'suspension_type' => 'required|string',
            'reason' => 'required|string|max:1000',
            'report_id' => 'nullable|exists:reports,id',
        ]);
        
        // Combine suspension type and reason
        $suspensionReason = $request->reason;
        if ($request->suspension_type && $request->suspension_type !== 'other') {
            $suspensionTypes = [
                'policy_violation' => 'Policy Violation',
                'fraudulent_activity' => 'Fraudulent Activity',
                'poor_product_quality' => 'Poor Product Quality',
                'customer_complaints' => 'Multiple Customer Complaints',
                'non_compliance' => 'Non-Compliance with Platform Rules',
                'payment_issues' => 'Payment or Transaction Issues',
                'inappropriate_content' => 'Inappropriate Content or Behavior',
                'safety_concerns' => 'Safety Concerns',
            ];
            $suspensionReason = 'Reason: ' . ($suspensionTypes[$request->suspension_type] ?? $request->suspension_type) . "\n\n" . $request->reason;
        }

        $seller->update([
            'is_active' => false,
            'suspension_reason' => $suspensionReason,
            'suspended_at' => now(),
            'suspended_by' => auth()->id(),
            'related_report_id' => $request->report_id,
        ]);

        // Deactivate all products from this seller
        $seller->products()->update(['status' => 'inactive']);

        // Also ban the user account if suspending seller
        if ($seller->user) {
            $seller->user->update([
                'is_banned' => true,
                'banned_at' => now(),
                'ban_reason' => 'Business account suspended: ' . $suspensionReason,
                'banned_by' => auth()->id(),
                'related_report_id' => $request->report_id,
            ]);
        }

        // Send notification to seller
        if ($seller->user) {
            $seller->user->notify(new SellerSuspendedNotification(
                $suspensionReason,
                $request->report_id,
                $seller->business_name
            ));
        }
        
        return back()->with('success', 'Seller suspended and notified via email.');
    }

    public function activate($id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        
        $seller->update([
            'is_active' => true,
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspended_by' => null,
            'related_report_id' => null,
        ]);

        // Note: Products remain inactive - seller needs to reactivate them manually
        // This prevents automatically reactivating products that were rejected

        // Unban the user account if activating seller
        if ($seller->user && $seller->user->is_banned) {
            $seller->user->update([
                'is_banned' => false,
                'banned_at' => null,
                'ban_reason' => null,
                'banned_by' => null,
                'related_report_id' => null,
            ]);
        }
        
        return back()->with('success', 'Seller activated. Note: Products remain inactive and need to be reactivated by the seller.');
    }

    /**
     * Approve a specific document
     */
    public function approveDocument(Request $request, $sellerId, $documentId)
    {
        $seller = Seller::findOrFail($sellerId);
        $document = SellerDocument::where('seller_id', $sellerId)->findOrFail($documentId);
        
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null
        ]);
        
        // Check if all required documents are now approved
        $requiredDocsCount = $seller->is_verified_shop ? 3 : 1;
        $approvedDocsCount = $seller->documents()->where('status', 'approved')->count();
        
        $message = 'Document approved successfully.';
        if ($approvedDocsCount >= $requiredDocsCount && $seller->verification_status === 'pending') {
            $message .= ' All required documents are now approved. You can approve the seller.';
        }
        
        return back()->with('success', $message);
    }

    /**
     * Reject a specific document
     */
    public function rejectDocument(Request $request, $sellerId, $documentId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $seller = Seller::with('user')->findOrFail($sellerId);
        $document = SellerDocument::where('seller_id', $sellerId)->findOrFail($documentId);
        
        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);
        
        // If seller was approved but a document is now rejected, set seller back to pending
        if ($seller->verification_status === 'approved') {
            $seller->update(['verification_status' => 'pending']);
        }
        
        // Send notification to seller about document rejection
        if ($seller->user) {
            $seller->user->notify(new DocumentRejectedNotification(
                $document->document_type,
                $request->reason,
                $seller->business_name
            ));
        }
        
        return back()->with('success', 'Document rejected successfully. The seller has been notified via email.');
    }
}
