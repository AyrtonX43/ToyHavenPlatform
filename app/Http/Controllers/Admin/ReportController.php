<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reportable', 'reviewedBy']);
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by report type
        if ($request->type) {
            $query->where('report_type', $request->type);
        }
        
        // Filter by reportable type
        if ($request->reportable_type) {
            $query->where('reportable_type', $request->reportable_type);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('reason', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('reporter', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                 ->orWhere('email', 'like', '%' . $request->search . '%');
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
        
        $reports = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.reports.index', compact('reports'));
    }

    public function show($id)
    {
        $report = Report::with(['reporter', 'reportable', 'reviewedBy'])->findOrFail($id);
        return view('admin.reports.show', compact('report'));
    }
    
    public function review(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);
        
        $report->update([
            'status' => 'reviewed',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        
        return back()->with('success', 'Report marked as reviewed.');
    }

    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);
        
        $report->update([
            'status' => 'resolved',
            'admin_notes' => $request->admin_notes ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        
        return back()->with('success', 'Report marked as resolved.');
    }

    public function dismiss(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);
        
        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $request->admin_notes ?? $report->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        
        return back()->with('success', 'Report dismissed.');
    }
}
