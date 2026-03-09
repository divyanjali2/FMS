<?php

namespace App\Http\Controllers;

use App\Models\GMWorkStatus;
use App\Models\Vehicle;
use App\Models\FleetVehicleRelease;
use Illuminate\Http\Request;

class VehicleStatusController extends Controller
{
    public function index()
    {
        $allStatuses = GMWorkStatus::with([
            'inspection.vehicle',
            'issueInventory.inventory',
            'issueInventory.garageInbuildIssue.issue',
            'issueInventory.garageInbuildIssue.fault',
        ])->get();

        $inProgress = $allStatuses->groupBy('inspection_id')->map(function ($records) {
            return $records->filter(fn($r) => $r->status === 'in_progress');
        })->filter(fn($records) => $records->count() > 0);

        $released = FleetVehicleRelease::with([
            'fleetPostCheck.inspection.vehicle',
            'fleetPostCheck.inspection.gmWorkStatuses.issueInventory.inventory',
            'fleetPostCheck.inspection.gmWorkStatuses.issueInventory.garageInbuildIssue.issue',
            'fleetPostCheck.inspection.gmWorkStatuses.issueInventory.garageInbuildIssue.fault',
        ])
        ->where('status', 'vehicle_release')
        ->get();

        $issueFaultCounts = [];
        foreach ($allStatuses as $status) {
            $vehicleId = $status->inspection->vehicle->id ?? null;
            if (!$vehicleId) continue;

            $issueName = $status->issueInventory->garageInbuildIssue->issue->name ?? null;
            $faultName = $status->issueInventory->garageInbuildIssue->fault->name ?? null;

            if ($issueName) {
                $issueFaultCounts[$vehicleId]['issues'][$issueName] = ($issueFaultCounts[$vehicleId]['issues'][$issueName] ?? 0) + 1;
            }
            if ($faultName) {
                $issueFaultCounts[$vehicleId]['faults'][$faultName] = ($issueFaultCounts[$vehicleId]['faults'][$faultName] ?? 0) + 1;
            }
        }

        $vehicles = Vehicle::all();

        return view('vehicle-status.index', compact('inProgress', 'released', 'allStatuses', 'issueFaultCounts','vehicles'));
    }

    public function showGMWorkStatus($id)
    {
        $workStatus = GMWorkStatus::with(['inspection.vehicle', 'gmReview', 'issueInventory.inventory'])->findOrFail($id);
        return view('vehicle-status.index', compact('workStatus'));
    }

    public function showRelease($id)
    {
        $release = FleetVehicleRelease::with('fleetPostCheck.vehicle')->findOrFail($id);
        return view('vehicle-status.index', compact('release'));
    }

    public function vehicleHistory($vehicleId)
    {
        $statuses = GMWorkStatus::with(['issueInventory.garageInbuildIssue.issue', 'issueInventory.garageInbuildIssue.fault'])
            ->whereHas('inspection.vehicle', fn($q) => $q->where('id', $vehicleId))
            ->get();

        $history = [];

        foreach ($statuses as $status) {
            $issue = $status->issueInventory->garageInbuildIssue->issue->name ?? null;
            $fault = $status->issueInventory->garageInbuildIssue->fault->name ?? null;
            $name = $issue ?? $fault ?? 'N/A';

            if (!isset($history[$name])) {
                $history[$name] = [
                    'issue_fault' => $name,
                    'count' => 0,
                    'dates' => [],
                ];
            }

            $history[$name]['count']++;
            $history[$name]['dates'][] = $status->updated_at->format('Y-m-d');
        }

        return response()->json(array_values($history));
    }

}
