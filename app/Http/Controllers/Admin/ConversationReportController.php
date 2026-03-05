<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationReport;
use App\Notifications\TradeReportedNotification;
use Illuminate\Http\Request;

class ConversationReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ConversationReport::with(['conversation.user1', 'conversation.user2', 'reporter', 'reportedUser'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20);

        return view('admin.conversation-reports.index', compact('reports'));
    }

    public function show(ConversationReport $report)
    {
        $report->load(['conversation.user1', 'conversation.user2', 'reporter', 'reportedUser']);
        return view('admin.conversation-reports.show', compact('report'));
    }

    public function update(Request $request, ConversationReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
            'admin_notes' => 'nullable|string|max:5000',
            'action' => 'nullable|in:none,suspend',
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($validated['status'] === 'resolved' && $report->reported_user_id) {
            $user = $report->reportedUser;
            if ($user) {
                if (($validated['action'] ?? '') === 'suspend') {
                    $count = ($user->trade_suspension_offence_count ?? 0) + 1;
                    $user->update([
                        'trade_suspension_offence_count' => $count,
                        'trade_suspended' => true,
                        'trade_suspended_at' => now(),
                        'trade_suspended_until' => $count >= 3 ? null : now()->addDays($count === 1 ? 5 : 30),
                        'trade_suspension_reason' => 'Report #' . $report->id . ': ' . ($validated['admin_notes'] ?? 'Violation'),
                        'trade_suspended_by' => auth()->id(),
                    ]);
                    $user->notify(new TradeReportedNotification($report, $count));
                } else {
                    $user->notify(new TradeReportedNotification($report));
                }
            }
        }

        return back()->with('success', 'Report updated.');
    }
}
