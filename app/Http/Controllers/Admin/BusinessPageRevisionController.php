<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPageRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessPageRevisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * List pending business page revisions for approval.
     */
    public function index(Request $request): View
    {
        $query = BusinessPageRevision::with(['seller.user', 'reviewedByUser'])
            ->where('status', BusinessPageRevision::STATUS_PENDING)
            ->orderBy('created_at', 'asc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        $revisions = $query->paginate(15)->withQueryString();

        return view('admin.business-page-revisions.index', compact('revisions'));
    }

    /**
     * Show a single revision (preview payload).
     */
    public function show(BusinessPageRevision $revision): View
    {
        $revision->load(['seller.user', 'seller.pageSettings', 'seller.socialLinks']);
        return view('admin.business-page-revisions.show', compact('revision'));
    }

    /**
     * Approve a revision and apply it to live data.
     */
    public function approve(Request $request, BusinessPageRevision $revision): RedirectResponse
    {
        if ($revision->status !== BusinessPageRevision::STATUS_PENDING) {
            return back()->with('error', 'This revision has already been processed.');
        }

        $revision->apply();

        return redirect()->route('admin.business-page-revisions.index')
            ->with('success', 'Business page changes approved and applied successfully.');
    }

    /**
     * Reject a revision.
     */
    public function reject(Request $request, BusinessPageRevision $revision): RedirectResponse
    {
        if ($revision->status !== BusinessPageRevision::STATUS_PENDING) {
            return back()->with('error', 'This revision has already been processed.');
        }

        $revision->reject($request->input('rejection_reason'));

        return redirect()->route('admin.business-page-revisions.index')
            ->with('success', 'Business page changes have been rejected.');
    }
}
