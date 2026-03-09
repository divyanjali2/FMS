<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GarageReport;
use App\Models\FleetPostCheck;
use App\Models\Procurement;
use App\Models\FmWorkDecision;
use App\Models\FleetDecision;
use App\Models\AccountantReview;
use Illuminate\Http\Request;
use App\Models\Installment;
use App\Models\Cashier;
use App\Models\PaymentCoordinator;
use Illuminate\Support\Facades\Log; 

class FleetDecisionController extends Controller
{
    public function index()
    {
        $reports = GarageReport::with(['inspection', 'inspection.vehicle'])
            ->whereIn('status', [
                'sent_to_fleet',
                'sent_to_garage',
                'sent_back_to_garage',
                'owner_repair',
                'owner_repair_done'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $postChecks = FleetPostCheck::with('inspection.vehicle')
            ->where('status', 'send_to_fm')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'postChecksPage'); 

        // Accountant Reviews
        $maintenanceTeamFromReviews = AccountantReview::with([
                'inspection.vehicle',
                'inventory',    
                'issue',       
                'fault',       
            ])
            ->where('status', 'send_to_fm')
            ->orderBy('created_at', 'desc')
            ->get();

        $maintenanceTeamFromProcurements = Procurement::with([
                'issueInventory.garageReport.inspection.vehicle',
                'issueInventory.inventory',
                'issueInventory.garageInbuildIssue.issue',
                'issueInventory.garageInbuildIssue.fault',
            ])
            ->where('procurement_status', 'send_to_fleet')
            ->orderBy('created_at', 'desc')
            ->get();


        $maintenanceTeam = $maintenanceTeamFromReviews->concat($maintenanceTeamFromProcurements)
            ->sortByDesc('created_at')
            ->values(); 

        $inspectionIds = $maintenanceTeam->pluck('inspection_id')->unique();

        $approvals = FmWorkDecision::pluck('inspection_id')->toArray();

        $procurements = Procurement::with('issueInventory')
            ->whereIn('inspection_id', $inspectionIds)
            ->get()
            ->groupBy('inspection_id');

        $cashiers = Cashier::with('vehicle')
            ->where('status', 'send_to_fm')
            ->latest()
            ->paginate(10, ['*'], 'cashierPage');

        $ownerRepairs = GarageReport::whereIn('status', ['owner_repair', 'owner_repair_done'])
            ->with('inspection.vehicle.vehicleType')
            ->latest()
            ->get();

        return view('fleet-decisions.index', compact('reports','postChecks','maintenanceTeam','procurements','approvals','cashiers','ownerRepairs'));
    }

    public function show(GarageReport $garageReport)
    {
        $garageReport->load([
            'inspection.vehicle',
            'inspection.faults.category.suppliers',
            'inspection.garageReports.issue.category.suppliers'
        ]);

        $fleetDecisions = FleetDecision::where('garage_report_id', $garageReport->id)
            ->where('type', 'fleet')
            ->get()
            ->mapWithKeys(function ($decision) {
                return [$decision->fault_id => $decision->decision];
            });

        $garageDecisions = FleetDecision::where('garage_report_id', $garageReport->id)
            ->where('type', 'garage')
            ->get()
            ->mapWithKeys(function ($decision) {
                return [$decision->issue_id => $decision->decision];
            });

        return view('fleet-decisions.show', compact('garageReport', 'fleetDecisions', 'garageDecisions'));
    }

    public function view(GarageReport $garageReport)
    {
        if ($garageReport->status !== 'sent_to_garage') {
            return redirect()->route('fleet-decisions.show', $garageReport);
        }

        $garageReport->load('inspection.vehicle', 'inspection.faults', 'issue');

        // Get all related garage reports for this inspection
        $relatedReportIds = GarageReport::where('inspection_id', $garageReport->inspection_id)
            ->pluck('id');

        // Get fleet decisions (for faults)
        $fleetDecisions = FleetDecision::whereIn('garage_report_id', $relatedReportIds)
            ->whereNotNull('fault_id')
            ->where('type', 'fleet')
            ->get()
            ->keyBy('fault_id')
            ->map(function($decision) {
                return $decision->decision;
            });

        // Get garage decisions (for issues)
        $garageDecisions = FleetDecision::whereIn('garage_report_id', $relatedReportIds)
            ->whereNotNull('issue_id')
            ->where('type', 'garage')
            ->get()
            ->keyBy('issue_id')
            ->map(function($decision) {
                return $decision->decision;
            });

        return view('fleet-decisions.view', compact('garageReport', 'fleetDecisions', 'garageDecisions'));
    }

    public function store(Request $request, GarageReport $garageReport = null)
    {
        // Handle marking as Owner Repair
        if ($request->has('owner_repair') && $request->owner_repair) {
            $garageReport = GarageReport::findOrFail($request->garage_report_id);

            if($garageReport->status === 'sent_to_fleet') {
                GarageReport::where('inspection_id', $garageReport->inspection_id)
                    ->update(['status' => 'owner_repair']);

                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Cannot mark as Owner Repair.']);
            }
        }

        // Handle marking Owner Repair Done
        if ($request->has('owner_repair_done') && $request->owner_repair_done) {
            $garageReport = GarageReport::findOrFail($request->garage_report_id);

            if($garageReport->status === 'owner_repair') {
                GarageReport::where('inspection_id', $garageReport->inspection_id)
                    ->where('status', 'owner_repair')
                    ->update(['status' => 'owner_repair_done']);

                return response()->json(['success' => true]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot mark as received unless status is Owner Repair.'
                ]);
            }
        }

        // Fetch Garage Report if not already set
        if (!$garageReport && $request->has('garage_report_id')) {
            $garageReport = GarageReport::findOrFail($request->garage_report_id);
        }

        // Validate request
        $request->validate([
            'fleet_decisions' => 'nullable|array',
            'fleet_decisions.*.decision' => 'in:inbuild,outsource',
            'fleet_decisions.*.supplier_id' => 'nullable|exists:suppliers,id',
            'garage_decisions' => 'nullable|array',
            'garage_decisions.*.decision' => 'in:inbuild,outsource',
            'garage_decisions.*.supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        // Clear existing inbuild issues for this garage report
        \DB::table('garage_inbuild_issues')->where('garage_report_id', $garageReport->id)->delete();

        // Handle Fleet Issues (Faults)
        $fleetDecisions = $request->fleet_decisions ?? [];
        foreach ($fleetDecisions as $faultId => $data) {
            $decision = $data['decision'] ?? null;
            $supplierId = $data['supplier_id'] ?? null;

            FleetDecision::updateOrCreate(
                [
                    'garage_report_id' => $garageReport->id,
                    'fault_id' => $faultId,
                    'type' => 'fleet'
                ],
                [
                    'inspection_id' => $garageReport->inspection_id, 
                    'decision' => $decision,
                    'supplier_id' => $supplierId,
                ]
            );

            if ($decision === 'inbuild') {
                \DB::table('garage_inbuild_issues')->insert([
                    'garage_report_id' => $garageReport->id,
                    'fault_id' => $faultId,
                    'type' => 'fleet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Handle Garage Issues
        $garageDecisions = $request->garage_decisions ?? [];
        foreach ($garageDecisions as $issueId => $data) {
            $decision = $data['decision'] ?? null;
            $supplierId = $data['supplier_id'] ?? null;

            FleetDecision::updateOrCreate(
                [
                    'garage_report_id' => $garageReport->id,
                    'issue_id' => $issueId,
                    'type' => 'garage'
                ],
                [
                    'inspection_id' => $garageReport->inspection_id, 
                    'decision' => $decision,
                    'supplier_id' => $supplierId,
                ]
            );

            if ($decision === 'inbuild') {
                \DB::table('garage_inbuild_issues')->insert([
                    'garage_report_id' => $garageReport->id,
                    'issue_id' => $issueId,
                    'type' => 'garage',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update status for all related reports of this inspection
        GarageReport::where('inspection_id', $garageReport->inspection_id)
            ->update(['status' => 'sent_to_garage']);

        return redirect()->route('fleet-decisions.index', $garageReport)
            ->with('success', 'Decisions saved successfully.');
    }

    public function approve(Request $request, $inspectionId)
    {
        $request->validate([
            'issue_inventory_ids' => 'required|array',
            'issue_inventory_ids.*' => 'exists:issue_inventories,id',
        ]);

        foreach ($request->issue_inventory_ids as $issueInventoryId) {
            FmWorkDecision::create([
                'inspection_id' => $inspectionId,
                'issue_inventory_id' => $issueInventoryId,
                'status' => 'approved',
            ]);
        }

        return redirect()->back()->with('success', 'Work approved successfully.');
    }

    public function storePayment(Request $request, $cashierId)
    {
        $request->validate([
            'procurement_ids'   => 'required|string', 
            'remaining_amount' => 'required|numeric',
        ]);

        // Convert procurement_ids to array
        $procIds = array_filter(explode(',', $request->input('procurement_ids')));
        $remainingAmount = (float) $request->input('remaining_amount');

        // Validate all procurement IDs exist
        $validCount = Procurement::whereIn('id', $procIds)->count();
        if ($validCount !== count($procIds)) {
            return redirect()->back()->withErrors([
                'procurement_id' => 'One or more selected procurements are invalid.'
            ]);
        }

        // Default status
        $status = 'send_to_cashier';

        // If installments are chosen → mark PaymentCoordinator as rejected
        if ($request->installment_check && $request->installment_type) {
            $status = 'rejected';
        }

        $payment = PaymentCoordinator::create([
            'cashier_id'      => $cashierId,
            'procurement_ids' => $procIds, 
            'total_price'     => $remainingAmount,
            'status'          => $status,
        ]);

        // Handle Installments
        if ($request->installment_check && $request->installment_type) {
            $installmentType = $request->installment_type;
            $options = [];

            if ($installmentType === 'equal') {
                $count = (int) $request->equal_count;
                $amount = (float) $request->equal_amount;
                for ($i = 0; $i < $count; $i++) {
                    $options[] = $amount;
                }
            } elseif ($installmentType === 'custom') {
                $options = array_map('floatval', explode(',', $request->custom_installments));
            }

            Installment::create([
                'cashier_id'             => $cashierId,
                'payment_coordinator_id' => $payment->id,
                'procurement_ids'        => $procIds, 
                'type'                   => $installmentType,
                'options'                => json_encode($options),
                'status'                 => 'send_to_gm',
            ]);
        }

        return redirect()->route('fleet-decisions.index')
            ->with('success', 'Payment processed successfully.');
    }

    public function paymentPage(Cashier $cashier)
    {
        $vehicleId = $cashier->vehicle_id;

        $procurementsByInspection = Procurement::with('inspection')
            ->whereHas('inspection', function($query) use ($vehicleId) {
                $query->where('vehicle_id', $vehicleId);
            })
            ->get()
            ->groupBy('inspection_id');

        $cashierAmount = $cashier->amount;

        return view('fleet-decisions.payment-page', compact('cashier', 'procurementsByInspection', 'cashierAmount'));
    }
}
