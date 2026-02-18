<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Report;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $request->validate([
            'reportable_type' => 'required|in:product,seller',
            'reportable_id' => 'required|integer',
            'report_type' => 'required|string',
            'reason' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'evidence' => 'nullable|array|max:5',
            'evidence.*' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Verify reportable exists
        if ($request->reportable_type === 'product') {
            $reportable = Product::findOrFail($request->reportable_id);
        } else {
            $reportable = Seller::findOrFail($request->reportable_id);
        }

        // Upload evidence
        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('reports/' . Auth::id(), 'public');
                $evidencePaths[] = $path;
            }
        }

        // Create report
        Report::create([
            'reporter_id' => Auth::id(),
            'reportable_type' => $request->reportable_type === 'product' ? Product::class : Seller::class,
            'reportable_id' => $request->reportable_id,
            'report_type' => $request->report_type,
            'reason' => $request->reason,
            'description' => $request->description,
            'evidence' => $evidencePaths,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Report submitted successfully! Our admin team will review it.');
    }

    public function showForm(Request $request)
    {
        $type = $request->get('type', 'product');
        $id = $request->get('id');

        return view('toyshop.report.create', compact('type', 'id'));
    }
}
