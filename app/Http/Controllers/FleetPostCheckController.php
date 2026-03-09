<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\FleetPostCheck;
use App\Models\GMWorkStatus;
use Illuminate\Http\Request;

class FleetPostCheckController extends Controller
{
    public function index()
    {
        $inspections = Inspection::whereHas('gmWorkStatuses', function ($q) {
                $q->where('status', 'work_done');
            })
            ->orderBy('created_at', 'desc') 
            ->get();

        return view('fleet_post_checks.index', compact('inspections'));
    }

    public function show($inspectionId)
    {
        $inspection = Inspection::with([
            'gmWorkStatuses.inbuildIssue.issue',
            'gmWorkStatuses.inbuildIssue.fault'
        ])->findOrFail($inspectionId);

        return view('fleet_post_checks.show', compact('inspection'));
    }

    public function store(Request $request, $inspectionId)
    {
        $request->validate([
            'items'  => 'required|array',
            'status' => 'required|in:send_to_fm,send_back_to_garage',
        ]);

        $totalItems = count($request->items);
        $completedItems = collect($request->items)->filter(fn($item) => isset($item['verified']))->count();

        $allCompleted = $completedItems === $totalItems;

        // Validation rule
        if ($allCompleted && $request->status !== 'send_to_fm') {
            return back()->withErrors(['status' => 'All items are completed, you must send to Fleet Manager.'])->withInput();
        }
        if (!$allCompleted && $request->status !== 'send_back_to_garage') {
            return back()->withErrors(['status' => 'Some items are not completed, you must send back to Garage.'])->withInput();
        }

        foreach ($request->items as $itemId => $data) {
            FleetPostCheck::create([
                'inspection_id'     => $inspectionId,
                'gm_work_status_id' => $data['gm_work_status_id'] ?? null,
                'issue_id'          => $data['issue_id'] ?? null,
                'fault_id'          => $data['fault_id'] ?? null,
                'verified'          => isset($data['verified']),
                'remarks'           => $data['remarks'] ?? null,
                'status'            => $request->status,
            ]);
        }

        return redirect()->route('fleet_post_checks.index')->with('success', 'Post-check saved successfully.');
    }
}
