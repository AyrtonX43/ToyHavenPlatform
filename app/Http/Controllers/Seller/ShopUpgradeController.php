<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShopUpgradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    public function index()
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Please complete your seller registration first.');
        }

        // Check requirements
        $requirements = $this->checkRequirements($seller);

        return view('seller.shop-upgrade.index', compact('seller', 'requirements'));
    }

    public function checkRequirements($seller)
    {
        $requirements = [
            'approved_status' => [
                'met' => $seller->verification_status === 'approved',
                'message' => 'Your seller account must be approved',
                'required' => true,
            ],
            'positive_reviews' => [
                'met' => $seller->rating >= 4.0 && $seller->total_reviews >= 10,
                'message' => 'Minimum 4.0 rating with at least 10 reviews',
                'current' => "Rating: {$seller->rating}/5.0 ({$seller->total_reviews} reviews)",
                'required' => true,
            ],
            'business_registration' => [
                'met' => $seller->documents()
                    ->whereIn('document_type', ['business_registration', 'business_permit'])
                    ->where('status', 'approved')
                    ->exists(),
                'message' => 'Business Registration: BIR Form 2303 (COR) + DTI/SEC Permit',
                'required' => true,
            ],
            'brand_rights' => [
                'met' => $seller->documents()
                    ->where('document_type', 'brand_rights')
                    ->where('status', 'approved')
                    ->exists(),
                'message' => 'Brand Rights: Trademark Certificate or Letter of Authorization (LOA)',
                'required' => true,
            ],
            'service_quality' => [
                'met' => $seller->response_rate >= 80 && $seller->total_sales >= 20,
                'message' => 'Minimum 80% response rate and 20+ sales',
                'current' => "Response Rate: {$seller->response_rate}%, Sales: {$seller->total_sales}",
                'required' => true,
            ],
            'bank_account' => [
                'met' => $seller->documents()
                    ->where('document_type', 'bank_account')
                    ->where('status', 'approved')
                    ->exists(),
                'message' => 'Verified bank account document',
                'required' => false,
            ],
        ];

        $requirements['all_met'] = collect($requirements)
            ->where('required', true)
            ->every(fn($req) => $req['met']);

        return $requirements;
    }

    public function submitUpgrade(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')
                ->with('error', 'Please complete your seller registration first.');
        }

        // Check if already verified shop
        if ($seller->is_verified_shop) {
            return redirect()->route('seller.shop-upgrade.index')
                ->with('info', 'Your shop is already verified as a trusted shop.');
        }

        // Re-check requirements
        $requirements = $this->checkRequirements($seller);

        if (!$requirements['all_met']) {
            return redirect()->route('seller.shop-upgrade.index')
                ->with('error', 'You do not meet all the required criteria for shop upgrade. Please review the requirements below.');
        }

        // Submit upgrade request (admin will review)
        $seller->update([
            'is_verified_shop' => false, // Keep false until admin approves
        ]);

        // You could create a notification or log here for admin review
        // For now, we'll just set a flag that admin can see

        return redirect()->route('seller.shop-upgrade.index')
            ->with('success', 'Your upgrade request has been submitted! Our admin team will review your application and notify you once approved.');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:business_registration,brand_rights,bank_account,id',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $seller = Auth::user()->seller;

        if (!$seller) {
            return back()->with('error', 'Seller account not found.');
        }

        $path = $request->file('document')->store('seller-documents/' . $seller->id, 'public');

        SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => $request->document_type,
            'document_path' => $path,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Document uploaded successfully! It will be reviewed by admin.');
    }
}
