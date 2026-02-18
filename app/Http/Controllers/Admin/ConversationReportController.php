<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationReport;
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
        return view('admin.conversation-reports.show', compact('report'));
    }

    public function update(Request $request, ConversationReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report updated.');
    }
}
