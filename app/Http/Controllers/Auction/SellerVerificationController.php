<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerDocument;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerVerificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $verification = $user->auctionSellerVerification;

        if ($verification && $verification->isApproved()) {
            return redirect()->route('auctions.seller.index')
                ->with('info', 'You are already verified to list auctions.');
        }

        $plan = $user->currentPlan();
        $hasVip = $plan && (strtolower($plan->slug) === 'vip' || $plan->canCreateAuction());

        return view('auctions.seller-verification.index', compact('verification', 'hasVip'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $plan = $user->currentPlan();

        if (! $plan || (strtolower($plan->slug) !== 'vip' && ! $plan->canCreateAuction())) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need a VIP membership to list auctions.');
        }

        $existing = $user->auctionSellerVerification;
        if ($existing && $existing->isApproved()) {
            return redirect()->route('auctions.seller.index');
        }

        if ($existing && $existing->isPending()) {
            return redirect()->route('auctions.verification.index')
                ->with('info', 'Your verification is already under review.');
        }

        $type = $request->query('type', 'individual');
        if (! in_array($type, ['individual', 'business'])) {
            $type = 'individual';
        }

        $seller = $user->seller;
        $syncedData = null;

        if ($seller && $seller->verification_status === 'approved') {
            $sellerDocs = $seller->documents()->whereIn('document_type', [
                'id', 'bank_account', 'business_registration', 'brand_rights',
            ])->get()->keyBy('document_type');

            $fullAddress = collect([
                $seller->address,
                $seller->city,
                $seller->province,
                $seller->postal_code,
            ])->filter()->implode(', ');

            $syncedData = [
                'phone' => $seller->phone,
                'address' => $fullAddress,
                'documents' => [],
            ];

            if ($sellerDocs->has('id')) {
                $syncedData['documents']['government_id_1'] = [
                    'path' => $sellerDocs['id']->document_path,
                    'label' => 'Government ID (from ToyShop)',
                ];
            }

            if ($sellerDocs->has('bank_account')) {
                $syncedData['documents']['bank_statement'] = [
                    'path' => $sellerDocs['bank_account']->document_path,
                    'label' => 'Bank Statement (from ToyShop)',
                ];
            }

            if ($type === 'business' && $sellerDocs->has('business_registration')) {
                $syncedData['documents']['business_permit'] = [
                    'path' => $sellerDocs['business_registration']->document_path,
                    'label' => 'Business Permit (from ToyShop)',
                ];
            }
        }

        return view('auctions.seller-verification.create', compact('type', 'syncedData'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $plan = $user->currentPlan();

        if (! $plan || (strtolower($plan->slug) !== 'vip' && ! $plan->canCreateAuction())) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need a VIP membership to list auctions.');
        }

        $sellerType = $request->input('seller_type', 'individual');
        if (! in_array($sellerType, ['individual', 'business'])) {
            $sellerType = 'individual';
        }

        $seller = $user->seller;
        $syncedDocPaths = [];
        $syncMapping = [
            'id' => 'government_id_1',
            'bank_account' => 'bank_statement',
            'business_registration' => 'business_permit',
        ];

        if ($seller && $seller->verification_status === 'approved') {
            $sellerDocs = $seller->documents()->whereIn('document_type', array_keys($syncMapping))->get();
            foreach ($sellerDocs as $doc) {
                $auctionDocType = $syncMapping[$doc->document_type] ?? null;
                if ($auctionDocType) {
                    $syncedDocPaths[$auctionDocType] = $doc->document_path;
                }
            }
        }

        $rules = [
            'seller_type' => 'required|in:individual,business',
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'address' => 'required|string|max:500',
            'selfie' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ];

        $govId1Rule = isset($syncedDocPaths['government_id_1'])
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
            : 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        $rules['government_id_1'] = $govId1Rule;

        $bankRule = isset($syncedDocPaths['bank_statement'])
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
            : 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        $rules['bank_statement'] = $bankRule;

        if ($sellerType === 'individual') {
            $rules['government_id_2'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['government_id_3'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } else {
            $permitRule = isset($syncedDocPaths['business_permit'])
                ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
                : 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['business_permit'] = $permitRule;
            $rules['bir_certificate'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['official_receipt_sample'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['dti_registration'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['sec_registration'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        $old = $user->auctionSellerVerification;
        if ($old && in_array($old->status, ['rejected', 'requires_resubmission'])) {
            $old->documents()->delete();
            $old->delete();
        }

        $verification = AuctionSellerVerification::create([
            'user_id' => $user->id,
            'seller_id' => $user->seller?->id,
            'seller_type' => $sellerType,
            'status' => 'pending',
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $storagePath = 'auction_verifications/' . $verification->id;

        $selfiePath = $request->file('selfie')->store($storagePath, 'public');
        $verification->update(['selfie_path' => $selfiePath]);

        $documentTypes = $sellerType === 'individual'
            ? AuctionSellerVerification::INDIVIDUAL_DOCUMENTS
            : AuctionSellerVerification::BUSINESS_DOCUMENTS;

        foreach ($documentTypes as $docType) {
            if ($request->hasFile($docType)) {
                $path = $request->file($docType)->store($storagePath, 'public');
                AuctionSellerDocument::create([
                    'verification_id' => $verification->id,
                    'document_type' => $docType,
                    'document_path' => $path,
                    'status' => 'pending',
                ]);
            } elseif (isset($syncedDocPaths[$docType])) {
                $originalPath = $syncedDocPaths[$docType];
                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $newPath = $storagePath . '/' . $docType . '_synced.' . $extension;

                if (Storage::disk('public')->exists($originalPath)) {
                    Storage::disk('public')->copy($originalPath, $newPath);
                    AuctionSellerDocument::create([
                        'verification_id' => $verification->id,
                        'document_type' => $docType,
                        'document_path' => $newPath,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return redirect()->route('auctions.verification.index')
            ->with('success', 'Verification submitted successfully! Our team will review your documents.');
    }

    public function status()
    {
        $user = Auth::user();
        $verification = $user->auctionSellerVerification;

        if (! $verification) {
            return redirect()->route('auctions.verification.index');
        }

        $verification->load('documents');

        return view('auctions.seller-verification.status', compact('verification'));
    }
}
