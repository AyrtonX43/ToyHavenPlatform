<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorAction;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Trade-related reportable types that moderators can access.
     */
    protected array $tradeReportableTypes = [
        'App\Models\Trade',
        'App\Models\TradeListing',
    ];

    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reportable', 'reviewedBy'])
            ->whereIn('reportable_type', $this->tradeReportableTypes);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('report_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('moderator.reports.index', compact('reports'));
    }

    public function show($id)
    {
        $report = Report::with(['reporter', 'reportable', 'reviewedBy'])->findOrFail($id);

        if (!in_array($report->reportable_type, $this->tradeReportableTypes)) {
            abort(403, 'You do not have access to this report.');
        }

        return view('moderator.reports.show', compact('report'));
    }

    public function review(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if (!in_array($report->reportable_type, $this->tradeReportableTypes)) {
            abort(403, 'You do not have access to this report.');
        }

        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $report->update([
            'status' => 'reviewed',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ModeratorAction::log(auth()->id(), 'report_reviewed', $report, 'Trade report marked as reviewed', ['report_id' => $report->id]);

        return back()->with('success', 'Report marked as reviewed.');
    }

    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if (!in_array($report->reportable_type, $this->tradeReportableTypes)) {
            abort(403, 'You do not have access to this report.');
        }

        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $report->update([
            'status' => 'resolved',
            'admin_notes' => $request->admin_notes ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ModeratorAction::log(auth()->id(), 'report_resolved', $report, 'Trade report resolved', ['report_id' => $report->id]);

        return back()->with('success', 'Report marked as resolved.');
    }

    public function dismiss(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if (!in_array($report->reportable_type, $this->tradeReportableTypes)) {
            abort(403, 'You do not have access to this report.');
        }

        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $request->admin_notes ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ModeratorAction::log(auth()->id(), 'report_dismissed', $report, 'Trade report dismissed', ['report_id' => $report->id]);

        return back()->with('success', 'Report dismissed.');
    }
}
