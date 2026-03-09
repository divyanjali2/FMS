<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IssueInventory;
use App\Models\GarageReport;
use App\Models\Supplier;
use App\Models\AccountantReview;
use App\Models\Procurement;
use Illuminate\Support\Facades\DB;
use App\Models\GRN;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcurementController extends Controller
{
    public function index()
    {
        $requests = DB::table('issue_inventories as ii')
            ->join('garage_inbuild_issues as gi', 'ii.inbuild_issue_id', '=', 'gi.id')
            ->leftJoin('issues as i', 'gi.issue_id', '=', 'i.id')
            ->leftJoin('faults as f', 'gi.fault_id', '=', 'f.id')
            ->join('garage_reports as gr', 'ii.garage_report_id', '=', 'gr.id')
            ->join('inspections as ins', 'gr.inspection_id', '=', 'ins.id')
            ->join('vehicles as v', 'ins.vehicle_id', '=', 'v.id')
            ->join('inventories as inv', 'ii.inventory_id', '=', 'inv.id')
            ->leftJoin('procurements as p', 'ii.id', '=', 'p.issue_inventory_id')
            ->select(
                'ii.id',
                'ii.garage_report_id',
                'ii.inventory_id',
                'ii.inbuild_issue_id',
                'ii.quantity',
                'ii.created_at',
                'ii.updated_at',
                'gi.garage_report_id as garage_inbuild_issue_id',  
                'i.name as issue_name',
                'f.name as fault_name',
                'ins.job_code',
                'v.reg_no',
                'inv.name as inventory_name',
                DB::raw('COUNT(p.id) as procurement_exists')
            )
            ->groupBy(
                'ii.id',
                'ii.garage_report_id',
                'ii.inventory_id',
                'ii.inbuild_issue_id',
                'ii.quantity',
                'ii.created_at',
                'ii.updated_at',
                'gi.garage_report_id',
                'i.name',
                'f.name',
                'ins.job_code',
                'v.reg_no',
                'inv.name'
            )
            ->orderBy('ii.created_at', 'desc')
            ->paginate(40);

        // GRN data
        $outsourcedGroups = Procurement::with('issueInventory')
            ->where('status', 'outsourced')
            ->where('procurement_status', '!=', 'cancelled') 
            ->whereHas('accountantReview', function($q) {
                $q->where('status', '=', 'send_to_procurement');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('inspection_id');

        // Existing GRNs to check if already filled
        $existingGRNs = GRN::pluck('inspection_id')->toArray();

        return view('procurements.index', compact('requests', 'outsourcedGroups', 'existingGRNs'));
    }

    public function edit($id)
    {
        $req = IssueInventory::with('inventory')->findOrFail($id);

        $allReqs = IssueInventory::with('inventory')
            ->where('garage_report_id', $req->garage_report_id)
            ->get();

        $suppliers = Supplier::all();

        return view('procurements.edit', compact('req', 'allReqs', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'statuses'   => 'required|array',
            'suppliers'  => 'array',
            'prices'     => 'array',
            'remarks'    => 'array',
        ]);

        $inspectionStatuses = []; 

        foreach ($validated['statuses'] as $reqId => $status) {
            $req = IssueInventory::with('inventory', 'garageReport')->findOrFail($reqId);
            $inspectionId = $req->garageReport->inspection_id;

            $billPath = null;
            if ($request->hasFile("bills.$reqId")) {
                $billPath = $request->file("bills.$reqId")->store('bills', 'public');
            }

            $sequence = Procurement::where('inspection_id', $inspectionId)
                ->whereNotNull('po_id')
                ->count() + 1;

            $poId = 'po-' . $inspectionId . '-' . $sequence;

            $procurement = Procurement::updateOrCreate(
                ['issue_inventory_id' => $reqId],
                [
                    'inspection_id'      => $inspectionId,
                    'status'             => $status,
                    'supplier_id'        => $validated['suppliers'][$reqId] ?? null,
                    'price'              => $validated['prices'][$reqId] ?? null,
                    'remark'             => $validated['remarks'][$reqId] ?? null,
                    'fulfilled_qty'      => $req->quantity,
                    'bill_path'          => $billPath,
                    'procurement_status' => 'send_to_accountant', // default
                    'po_id'              => $poId,
                ]
            );

            // Track inspection's statuses
            $inspectionStatuses[$inspectionId][] = $status;

            // Stock deduction
            if ($status === 'from_stock') {
                $inventory = $req->inventory;
                if (is_null($inventory->remaining_quantity)) {
                    $inventory->remaining_quantity = $inventory->available_quantity;
                    $inventory->save();
                }
                if ($inventory->remaining_quantity < $req->quantity) {
                    return back()->withErrors(['error' => 'Not enough stock available for ' . $inventory->name]);
                }
                $inventory->decrement('remaining_quantity', $req->quantity);
            }
        }

        // ✅ After loop, check each inspection
        foreach ($inspectionStatuses as $inspectionId => $statuses) {
            if (count($statuses) > 0 && collect($statuses)->every(fn($s) => $s === 'from_stock')) {
                Procurement::where('inspection_id', $inspectionId)
                    ->update(['procurement_status' => 'send_to_fleet']);
            }
        }

        return redirect()->route('procurements.index')->with('success', 'Procurement decisions saved. PO generated.');
    }

    public function storeGRN(Request $request, $inspectionId)
    {
        $validated = $request->validate([
            'received_qty' => 'required|array',
            'remarks' => 'array'
        ]);

        foreach ($validated['received_qty'] as $procId => $qty) {
            $proc = Procurement::findOrFail($procId);

            GRN::create([
                'inspection_id' => $inspectionId,
                'procurement_id' => $procId,
                'requested_qty' => $proc->fulfilled_qty,
                'received_qty' => $qty,
                'remark' => $validated['remarks'][$procId] ?? null,
            ]);
        }

        return redirect()->route('procurements.index')->with('success', 'GRN saved successfully.');
    }

    public function viewGRN($inspectionId)
    {
        $grns = GRN::where('inspection_id', $inspectionId)
            ->with([
                'procurement.supplier',
                'procurement.issueInventory.inventory',
                'procurement.issueInventory.garageReport.inspection.vehicle.company', 
            ])
            ->get();

        $grnsBySupplier = $grns
            ->groupBy('procurement.supplier_id');
            

        $vehicle = optional($grns->first()->procurement->issueInventory->garageReport->inspection->vehicle ?? null);

        return view('procurements.view-grn', compact('grnsBySupplier', 'inspectionId', 'vehicle'));
    }

    public function downloadGRN($inspectionId, $supplierId)
    {
        $grns = GRN::where('inspection_id', $inspectionId)
            ->whereHas('procurement', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->with([
                'procurement.issueInventory.inventory',
                'procurement.supplier',
                'procurement.issueInventory.garageReport.inspection.vehicle.company'
            ])
            ->get();

        if ($grns->isEmpty()) {
            return back()->withErrors(['error' => 'No GRN found for this supplier.']);
        }

        $supplierName = $grns->first()->procurement->supplier->name ?? 'Supplier';

        $grnNo = "INS-{$inspectionId}-SUP-{$supplierId}";
        $date = now()->format('F d, Y');
        $receivedDate = $grns->first()->created_at->format('F d, Y');

        $vehicle = optional($grns->first()->procurement->issueInventory->garageReport->inspection->vehicle ?? null);

        $pdf = Pdf::loadView('procurements.grn-pdf', compact(
            'grns', 'supplierName', 'grnNo', 'date', 'receivedDate', 'vehicle'
        ));

        return $pdf->download("GRN_{$supplierName}_{$inspectionId}.pdf");
    }

    public function recreatePO(Request $request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        $procurement->update([
            'procurement_status' => 'cancelled'
        ]);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'price'       => 'required|numeric|min:0',
            'remark'      => 'nullable|string',
            'bill'        => 'nullable|file|mimes:pdf,jpg,jpeg,png',
        ]);

        $billPath = $request->hasFile('bill') 
            ? $request->file('bill')->store('bills', 'public') 
            : null;

        $sequence = Procurement::where('inspection_id', $procurement->inspection_id)
            ->whereNotNull('po_id')
            ->count() + 1;

        $newPoId = 'po-' . $procurement->inspection_id . '-' . $sequence;

        $newProcurement = Procurement::create([
            'inspection_id'      => $procurement->inspection_id,
            'issue_inventory_id' => $procurement->issue_inventory_id,
            'status'             => 'outsourced',
            'supplier_id'        => $validated['supplier_id'],
            'price'              => $validated['price'],
            'remark'             => $validated['remark'] ?? null,
            'fulfilled_qty'      => $procurement->issueInventory->quantity,
            'bill_path'          => $billPath,
            'procurement_status' => 'send_to_accountant',
            'po_id'              => $newPoId,
        ]);

        AccountantReview::create([
            'inspection_id' => $newProcurement->inspection_id,
            'procurement_id'=> $newProcurement->id,
            'types'         => 'cash',             
            'status'        => 'send_to_procurement', 
        ]);

        return redirect()->route('procurements.index')->with('success', "PO {$procurement->po_id} cancelled. New PO {$newPoId} created.");
    }
}


