<?php

namespace App\Http\Controllers;

use App\Models\GMReview;
use App\Models\AccountantReview;
use App\Models\FleetDecision;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MDReview; 
use App\Models\GmDispatch; 
use App\Models\GMWorkStatus;
use App\Models\Installment;

class GMController extends Controller
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

        $installments = Installment::with(['cashier', 'procurement', 'paymentCoordinator'])
            ->latest()
            ->get();

        return view('gm.index', compact('inspections', 'installments'));
    }

    public function workStartedMultiple(Request $request, Inspection $inspection)
    {
        $request->validate([
            'statuses' => 'required|array',
            'statuses.*' => 'required|in:in_progress,work_done',
        ]);

        foreach ($request->statuses as $issueInventoryId => $status) {
            GMWorkStatus::updateOrCreate(
                [
                    'inspection_id' => $inspection->id,
                    'issue_inventory_id' => $issueInventoryId,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return back()->with('success', 'Work status updated successfully!');
    }

    public function approveInstallment(Installment $installment)
    {
        if ($installment->status !== 'paid') {
            // 1. Update Installment status
            $installment->update(['status' => 'paid']);

            // 2. Update linked PaymentCoordinator
            if ($installment->payment_coordinator_id) {
                \App\Models\PaymentCoordinator::where('id', $installment->payment_coordinator_id)
                    ->update(['status' => 'send_to_cashier']);
            }
        }

        return back()->with('success', 'Installment approved and sent to cashier.');
    }

}