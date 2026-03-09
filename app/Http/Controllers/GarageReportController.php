<?php

namespace App\Http\Controllers;

use App\Models\GarageReport;
use App\Models\Inspection;
use App\Models\Issue;
use App\Models\GMReview;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FmWorkDecision;
use App\Models\FleetPostCheck;
use App\Models\FleetDecision;
use App\Models\DefectCategory;
use App\Models\IssueInventory;
use Illuminate\Support\Facades\DB;

class GarageReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = GarageReport::with(['inspection.vehicle', 'issue'])
            ->whereHas('inspection', function ($q) {
                $q->where('repair_type', '!=', 'emergency');
            })
            ->orderBy('inspection_id')
            ->get()
            ->groupBy('inspection_id');


        $fleetDecisions = FleetDecision::with([
                'garageReport.inspection.vehicle',
                'garageReport.inspection.garageReports.issue',
                'issue',
                'fault'
            ])
            ->where('decision', 'inbuild')
            ->orderBy('garage_report_id')
            ->get()
            ->groupBy('garage_report_id');


        $fmApproved = FmWorkDecision::with([
            'inspection.vehicle',
            'issueInventory.garageInbuildIssue.issue',
            'issueInventory.garageInbuildIssue.fault',
            'issueInventory.inventory'
        ])
        ->where('status', 'approved')
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('inspection_id');

        // collect inspection IDs
        $fmInspectionIds = $fmApproved->keys();

        $fmInbuildIssues = DB::table('garage_inbuild_issues')
            ->join('garage_reports', 'garage_inbuild_issues.garage_report_id', '=', 'garage_reports.id')
            ->leftJoin('issue_inventories', 'garage_inbuild_issues.id', '=', 'issue_inventories.inbuild_issue_id')
            ->leftJoin('inventories', 'inventories.id', '=', 'issue_inventories.inventory_id')
            ->whereIn('garage_reports.inspection_id', $fmInspectionIds)
            ->select(
                'garage_reports.inspection_id',
                'garage_inbuild_issues.id as inbuild_id',
                'garage_inbuild_issues.issue_id',
                'garage_inbuild_issues.fault_id',
                'inventories.name as inventory_name',
                'issue_inventories.quantity'
            )
            ->get()
            ->groupBy('inspection_id');

        // 5️⃣ Inventories keyed by id
        $inventories = Inventory::all()->keyBy('id');

        $pendingPostChecks = FleetPostCheck::with([
            'inspection.vehicle',
            'gmWorkStatus.gmReview',
            'issue',
            'fault'
        ])
        ->where('verified', false)
        ->where('status', 'send_back_to_garage')
        ->get()
        ->groupBy('inspection_id');

        return view('garage_reports.index', compact(
            'reports',
            'reports',
            'fleetDecisions',
            'fleetDecisions',
            'inventories',
            'fmApproved',
            'fmInbuildIssues',
            'pendingPostChecks'
        ));
    }

    public function destroy(GarageReport $garageReport)
    {
        $garageReport->delete();
        return back()->with('success','Report deleted.');
    }

    public function show($inspectionId)
    {
        $inspection = Inspection::with(['vehicle', 'garageReports.issue'])->findOrFail($inspectionId);
        $garageReports = $inspection->garageReports;

        return view('garage_reports.show', compact('inspection', 'garageReports'));
    }

    public function edit(GarageReport $garageReport)
    {
        if ($garageReport->status !== 'pending') {
            return redirect()->route('garage_reports.show', $garageReport->inspection_id)
                ->with('error', 'This report cannot be edited anymore.');
        }

        $issues = Issue::all();
        $defectCategories = DefectCategory::with('issues')->get();

        return view('garage_reports.edit', compact('garageReport', 'issues','defectCategories'));
    }

    public function update(Request $request, GarageReport $garageReport)
    {
        $request->validate([
            'issue_id' => 'nullable|array',
            'issue_id.*' => 'exists:issues,id',
            'issue_action' => 'nullable|array',
            'issue_action.*' => 'nullable|in:repair,replace,top-up,refill',
            'hours' => 'nullable|numeric',
            'next_step' => 'required|in:send_to_repair,make_available',

            'images' => 'nullable|array',
            'images.*' => 'nullable|array',
            'images.*.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $issueIds     = (array) $request->input('issue_id', []);
        $issueActions = (array) $request->input('issue_action', []);
        $hours        = $request->input('hours', null);

        $status = $request->next_step === 'send_to_repair'
            ? 'sent_to_fleet'
            : 'disappear';

        if ($request->next_step === 'make_available') {
            $hours = null;
        }

        $storeIssueImages = function (int $issueId) use ($request, $garageReport) {
            $urls = [];

            if ($request->hasFile("images.$issueId")) {
                foreach ($request->file("images.$issueId") as $img) {
                    $path = $img->store("garage_reports/inspection_{$garageReport->inspection_id}/issue_{$issueId}", 'public');
                    $urls[] = asset('storage/' . $path);
                }
            }

            return $urls; 
        };

        $firstIssueId = $issueIds[0] ?? null;
        $firstAction  = $firstIssueId ? ($issueActions[$firstIssueId] ?? null) : null;
        $firstImages  = $firstIssueId ? $storeIssueImages((int)$firstIssueId) : [];

        $garageReport->update([
            'issue_id' => $firstIssueId,
            'hours'    => $hours,
            'notes'    => $firstAction,
            'images'   => !empty($firstImages) ? $firstImages : null, 
            'status'   => $status,
        ]);

        $issueCount = count($issueIds);
        if ($issueCount > 1) {
            for ($i = 1; $i < $issueCount; $i++) {
                $id = (int) $issueIds[$i];
                $action = $issueActions[$id] ?? null;
                $images = $storeIssueImages($id);

                GarageReport::create([
                    'inspection_id' => $garageReport->inspection_id,
                    'issue_id'      => $id,
                    'hours'         => $hours,
                    'notes'         => $action,
                    'images'        => !empty($images) ? $images : null, 
                    'status'        => $status,
                ]);
            }
        }

        return redirect()
            ->route('garage_reports.index', $garageReport->inspection_id)
            ->with('success', 'Garage report updated.');
    }

    public function assignInventory(Request $request, GarageReport $garageReport)
    {
        try {
            DB::beginTransaction();

            foreach ($request->inventories as $inbuildId => $data) {
                $inventoryId = $data['inventory_id'];
                $quantity = (int) $data['quantity'];

                // Remove old assignment
                IssueInventory::where('garage_report_id', $garageReport->id)
                    ->where('inbuild_issue_id', $inbuildId)
                    ->delete();

                // Create new assignment
                if (!empty($inventoryId)) {
                    IssueInventory::create([
                        'garage_report_id' => $garageReport->id,
                        'inbuild_issue_id' => $inbuildId,
                        'inventory_id' => $inventoryId,
                        'quantity' => $quantity
                    ]);
                }
            }

            $garageReport->update(['status' => 'sent_back_to_garage']);

            DB::commit();

            return redirect()->back()->with('success', 'Inventory assigned successfully and report sent back to garage.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function completeAllFleetPostChecks($inspectionId)
    {
        $checks = FleetPostCheck::where('inspection_id', $inspectionId)
                    ->where('verified', false)
                    ->where('status', 'send_back_to_garage')
                    ->get();

        foreach ($checks as $check) {
            $check->update([
                'status' => 'send_to_fm',
                'verified' => true,
            ]);
        }

        return redirect()->back()->with('success', 'All pending fleet post-checks marked as completed.');
    }
}
