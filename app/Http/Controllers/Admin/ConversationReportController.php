<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationReport;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ConversationReport::with(['conversation.user1', 'conversation.user2', 'reporter'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20);

        return view('admin.conversation-reports.index', compact('reports'));
    }

    public function show(ConversationReport $report)
    {
        $report->load(['conversation.user1', 'conversation.user2', 'reporter']);
        $reportedUser = $report->conversation->user1_id == $report->reporter_id
            ? $report->conversation->user2
            : $report->conversation->user1;
        return view('admin.conversation-reports.show', compact('report', 'reportedUser'));
    }

    public function update(Request $request, ConversationReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
            'admin_notes' => 'nullable|string|max:5000',
            'penalty' => 'nullable|in:5,30,ban',
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if (!empty($validated['penalty'])) {
            $reportedUser = $report->conversation->user1_id == $report->reporter_id
                ? User::find($report->conversation->user2_id)
                : User::find($report->conversation->user1_id);

            if ($reportedUser) {
                $count = ($reportedUser->trade_penalty_count ?? 0) + 1;
                $reportedUser->update([
                    'trade_penalty_count' => $count,
                    'trade_suspended' => true,
                    'trade_suspended_at' => now(),
                    'trade_suspension_reason' => 'Report #' . $report->id . ': ' . ($validated['admin_notes'] ?? 'Trade violation'),
                    'trade_suspended_by' => auth()->id(),
                ]);

                if ($validated['penalty'] === 'ban') {
                    $reportedUser->update([
                        'trade_banned' => true,
                        'trade_suspended_until' => null,
                    ]);
                } elseif ($validated['penalty'] === '5') {
                    $reportedUser->update(['trade_suspended_until' => now()->addDays(5)]);
                } elseif ($validated['penalty'] === '30') {
                    $reportedUser->update(['trade_suspended_until' => now()->addDays(30)]);
                }
            }
        }

        return back()->with('success', 'Report updated.');
    }
}
