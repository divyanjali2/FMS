<?php

namespace App\Http\Controllers;

use App\Models\MDReview;
use App\Models\Inspection;
use App\Models\GMReview;
use Illuminate\Http\Request;

class MDReviewController extends Controller
{
    public function index()
    {
        $inspections = Inspection::whereHas('gmWorkStatuses.issueInventory.garageInbuildIssue', function ($q) {
            $q->whereHas('issue')->orWhereHas('fault');
        })
        ->with([
            'vehicle',
            'gmWorkStatuses.issueInventory.inventory',
            'gmWorkStatuses.issueInventory.garageInbuildIssue.issue',
            'gmWorkStatuses.issueInventory.garageInbuildIssue.fault'
        ])
        ->latest()
        ->get();

        return view('md.reviews.index', compact('inspections'));
    }

    public function decision(Request $request, GMReview $review)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,inquired',
            'comments' => 'nullable|string'
        ]);

        $review->update([
            'status' => $request->status,
            'comments' => $request->comments
        ]);

        return redirect()->back()->with('success', 'Decision submitted successfully.');
    }

    public function storeMultiple(Request $request, $inspectionId)
    {
        $request->validate([
            'gm_review_ids' => 'required|array',
            'md_comment' => 'nullable|string',
        ]);

        foreach ($request->gm_review_ids as $gmReviewId) {
            $gmReview = GMReview::findOrFail($gmReviewId);

            MDReview::updateOrCreate(
                ['gm_review_id' => $gmReview->id],
                [
                    'inspection_id' => $gmReview->inspection_id,
                    'procurement_id' => $gmReview->procurement_id,
                    'md_comment' => $request->md_comment,
                    'status' => 'sent_to_gm',
                ]
            );
        }

        return redirect()->back()->with('success', 'MD decision sent for GM.');
    }
}
