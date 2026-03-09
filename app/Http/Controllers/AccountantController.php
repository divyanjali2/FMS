<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\GRN;
use App\Models\AccountantReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use PDF;

class AccountantController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $sentToGmInspectionIds = AccountantReview::where('status', 'send_to_fm')
            ->pluck('inspection_id');

        $pendingReviews = Procurement::where('procurement_status', 'send_to_accountant')
            ->where('status', 'outsourced')
            ->with(['accountantReview', 'grns.accountantReview']) 
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('inspection_id');

        $completedReviews = AccountantReview::with('inspection.vehicle')
            ->where('status', 'send_to_fm')
            ->get()
            ->groupBy('inspection_id');

        $grnsReviews = GRN::with('procurement.inspection.vehicle')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($grn) {
                return $grn->procurement->inspection_id ?? 'unknown';
            });

        return view('accountant.index', compact('pendingReviews', 'completedReviews', 'grnsReviews'));
    }

    public function show($inspectionId)
    {
        $procurements = Procurement::where('inspection_id', $inspectionId)
            ->where('procurement_status', 'send_to_accountant')
            ->where('status', 'outsourced')
            ->with(['issueInventory.inventory', 'supplier', 'issueInventory.garageReport.inspection.vehicle'])
            ->get();

        if ($procurements->isEmpty()) {
            return redirect()->route('accountant.index')->with('error', 'No procurements found for this inspection.');
        }

        $inspection = $procurements->first()->issueInventory->garageReport->inspection;
        $vehicle = $inspection->vehicle ?? null;
        $totalPrice = $procurements->sum('price');

        return view('accountant.show', compact('procurements', 'inspection', 'vehicle', 'totalPrice'));
    }

    public function sendToProcurement(Request $request, $inspectionId)
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'in:cash,credit',
        ]);

        $procurements = Procurement::where('inspection_id', $inspectionId)
            ->where('procurement_status', 'send_to_accountant')
            ->where('status', 'outsourced')
            ->get();

        foreach ($procurements as $procurement) {
            $paymentType = $request->types[$procurement->id] ?? null;

            AccountantReview::updateOrCreate(
                [
                    'inspection_id' => $inspectionId,
                    'procurement_id' => $procurement->id,
                ],
                [
                    'types' => $paymentType,
                    'status' => 'send_to_procurement', 
                ]
            );
        }

        return redirect()->route('accountant.index')->with('success', 'Procurements sent to Procurement Office.');
    }

    public function purchaseOrder($inspectionId)
    {
        $procurements = Procurement::where('inspection_id', $inspectionId)
            ->where('status', 'outsourced')
            ->where('procurement_status', '!=', 'cancelled') 
            ->with(['issueInventory.inventory', 'supplier', 'accountantReview', 'issueInventory.garageReport.inspection.vehicle.company']) // eager load company
            ->get();

        if ($procurements->isEmpty()) {
            return redirect()->route('accountant.index')->with('error', 'No procurements found.');
        }

        $inspection = $procurements->first()->issueInventory->garageReport->inspection;
        $vehicle = $inspection->vehicle; // company is now loaded

        // Group by supplier
        $procurementsBySupplier = $procurements->groupBy(fn($p) => $p->supplier_id);

        return view('accountant.purchase_order', compact(
            'inspection',
            'vehicle',
            'procurementsBySupplier'
        ));
    }

    public function approveGRN($inspectionId)
    {
        DB::transaction(function() use ($inspectionId) {

            // Get all GRNs for this inspection
            $grns = GRN::where('inspection_id', $inspectionId)->get();

            foreach ($grns as $grn) {
                $inventory = $grn->procurement->issueInventory->inventory;

                // Update inventory if it exists
                if ($inventory) {
                    $oldQty = $inventory->remaining_quantity;
                    $inventory->remaining_quantity += $grn->received_qty;
                    $inventory->save();

                    Log::info("Inventory ID {$inventory->id} updated: +{$grn->received_qty} received from GRN #{$grn->id}. Old quantity: {$oldQty}, New quantity: {$inventory->remaining_quantity}");
                }

                // Ensure accountant review exists
                $review = $grn->accountantReview;
                if (!$review) {
                    $review = AccountantReview::create([
                        'grn_id' => $grn->id,
                        'inspection_id' => $inspectionId,
                        'procurement_id' => $grn->procurement->id, 
                        'status' => 'send_to_fm',
                        'user_id' => auth()->id(),
                    ]);

                    Log::info("Created new AccountantReview ID {$review->id} for GRN #{$grn->id} and marked as 'send_to_fm'.");
                } else {
                    $review->status = 'send_to_fm';
                    $review->save();
                    Log::info("Accountant review ID {$review->id} for GRN #{$grn->id} marked as 'send_to_fm'.");
                }
            }
        });

        return redirect()->back()->with('success', 'GRN approved and inventory updated successfully.');
    }

    public function downloadPO($supplierId)
    {
        $procurements = Procurement::with([
            'issueInventory', 
            'issueInventory.inventory', 
            'issueInventory.garageReport.inspection.vehicle.company', 
            'supplier'
        ])->where('supplier_id', $supplierId)
        ->get();

        if ($procurements->isEmpty()) {
            return back()->with('error', 'No procurements found for this supplier.');
        }

        $supplier = $procurements->first()->supplier;
        $inspection = $procurements->first()->issueInventory->garageReport->inspection ?? null;
        $vehicle = $inspection->vehicle ?? null; 

        // Pass to view
        $pdf = PDF::loadView('accountant.po_template', compact('procurements', 'supplier', 'inspection', 'vehicle'));

        return $pdf->download('PO_'.$supplier->name.'_'.now()->format('Ymd_His').'.pdf');
    }
}