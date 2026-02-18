<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\BusinessPageRevision;
use App\Models\BusinessPageSetting;
use App\Models\BusinessSocialLink;
use App\Models\Seller;
use App\Notifications\VerifyBusinessEmailNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    /**
     * Show the business page settings form
     */
    public function index()
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('info', 'Please complete your seller registration first.');
        }

        $pageSettings = $seller->pageSettings ?? BusinessPageSetting::create(['seller_id' => $seller->id]);
        $socialLinks = BusinessSocialLink::where('seller_id', $seller->id)->orderBy('display_order')->get();
        $pendingRevisions = $seller->pendingBusinessPageRevisions()->get();

        return view('seller.business-page.index', compact('seller', 'pageSettings', 'socialLinks', 'pendingRevisions'));
    }

    /**
     * Update business page settings (submitted for admin approval).
     */
    public function updateSettings(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('error', 'Please complete your seller registration first.');
        }

        $request->validate([
            'page_name' => ['nullable', 'string', 'max:255'],
            'business_description' => ['nullable', 'string', 'max:5000'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'layout_type' => ['nullable', 'in:grid,list,featured'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $pageSettings = $seller->pageSettings ?? BusinessPageSetting::create(['seller_id' => $seller->id]);

        $payload = $request->only([
            'page_name',
            'business_description',
            'primary_color',
            'secondary_color',
            'layout_type',
            'meta_title',
            'meta_description',
            'is_published',
        ]);

        if ($request->has('keywords')) {
            $keywords = array_filter(array_map('trim', explode(',', $request->keywords)));
            $payload['keywords'] = !empty($keywords) ? $keywords : null;
        }

        if ($request->has('is_published')) {
            $payload['published_at'] = $request->is_published ? ($pageSettings->published_at ?? now()) : null;
        }

        $revision = BusinessPageRevision::create([
            'seller_id' => $seller->id,
            'type' => BusinessPageRevision::TYPE_GENERAL,
            'payload' => $payload,
            'status' => BusinessPageRevision::STATUS_PENDING,
        ]);

        if ($request->hasFile('profile_picture')) {
            $logoPath = $request->file('profile_picture')->store("business/revisions/{$revision->id}", 'public');
            $revision->update(['payload' => array_merge($revision->payload ?? [], ['logo_path' => $logoPath])]);
        }
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store("business/revisions/{$revision->id}", 'public');
            $revision->update(['payload' => array_merge($revision->payload ?? [], ['banner_path' => $bannerPath])]);
        }

        return redirect()->route('seller.business-page.index')
            ->with('success', 'Your business page changes have been submitted for admin approval. You will be notified once they are reviewed.')
            ->with('tab', 'general');
    }

    /**
     * Store or update social links (submitted for admin approval).
     */
    public function updateSocialLinks(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('error', 'Please complete your seller registration first.');
        }

        $request->validate([
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|in:facebook,instagram,twitter,youtube,tiktok,linkedin,pinterest,other',
            'social_links.*.url' => 'required|url|max:255',
            'social_links.*.display_name' => 'nullable|string|max:100',
            'social_links.*.is_active' => 'nullable|boolean',
        ]);

        $socialLinks = [];
        if ($request->has('social_links')) {
            foreach ($request->social_links as $index => $link) {
                if (!empty($link['url'])) {
                    $socialLinks[] = [
                        'platform' => $link['platform'],
                        'url' => $link['url'],
                        'display_name' => $link['display_name'] ?? null,
                        'display_order' => $index,
                        'is_active' => $link['is_active'] ?? true,
                    ];
                }
            }
        }

        BusinessPageRevision::create([
            'seller_id' => $seller->id,
            'type' => BusinessPageRevision::TYPE_SOCIAL,
            'payload' => ['social_links' => $socialLinks],
            'status' => BusinessPageRevision::STATUS_PENDING,
        ]);

        return redirect()->route('seller.business-page.index')
            ->with('success', 'Your social links have been submitted for admin approval.')
            ->with('tab', 'social');
    }

    /**
     * Preview business page
     */
    public function preview()
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('error', 'Please complete your seller registration first.');
        }

        $pageSettings = $seller->pageSettings;
        $socialLinks = BusinessSocialLink::where('seller_id', $seller->id)->where('is_active', true)->orderBy('display_order')->get();

        return view('seller.business-page.preview', compact('seller', 'pageSettings', 'socialLinks'));
    }

    /**
     * Update business contact (email and phone). Submitted for admin approval; verification happens after approval.
     */
    public function updateContact(Request $request): RedirectResponse
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('error', 'Please complete your seller registration first.');
        }

        $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+63[0-9]{10}$/'],
        ]);

        BusinessPageRevision::create([
            'seller_id' => $seller->id,
            'type' => BusinessPageRevision::TYPE_CONTACT,
            'payload' => [
                'email' => $request->email ?: null,
                'phone' => $request->phone ?: null,
            ],
            'status' => BusinessPageRevision::STATUS_PENDING,
        ]);

        return redirect()->route('seller.business-page.index')
            ->with('success', 'Your contact changes have been submitted for admin approval. Your business page will be updated after approval.')
            ->with('tab', 'contact');
    }

    /**
     * Verify business email (signed URL from email link).
     */
    public function verifyBusinessEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'seller' => 'required|exists:sellers,id',
            'email' => 'required|email',
        ]);

        $seller = Seller::findOrFail($request->seller);

        if ($request->email !== $seller->email) {
            return redirect()->route('login')
                ->with('error', 'This verification link is for a different email. Please use the latest link sent to your business email.');
        }

        $seller->update([
            'email_verified_at' => now(),
        ]);

        if (Auth::check() && Auth::user()->seller?->id === $seller->id) {
            return redirect()->route('seller.business-page.index')
                ->with('success', 'Your business email has been verified successfully!')
                ->with('tab', 'contact');
        }

        return redirect()->route('login')
            ->with('success', 'Your business email has been verified. Please log in to continue.');
    }

}
