<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use App\Models\Vehicle;
use App\Models\Procurement;
use App\Models\PaymentCoordinator;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Installment;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $cashiers = Cashier::with('vehicle')
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $vehicles = Vehicle::with('cashier')->orderBy('reg_no')->get();

        $approvals = PaymentCoordinator::with('cashier.vehicle')
            ->whereIn('status', ['send_to_cashier', 'approved']) 
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('cashier.index', compact('cashiers', 'vehicles','approvals'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('reg_no')->get();
        return view('cashier.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'due_day'    => 'required|integer|min:1|max:30',
            'amount'     => 'required|numeric|min:0',
            'bank_name'  => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:255',
            'rental_agreement_start_date'   => 'required|date',
            'rental_agreement_end_date'   => 'required|date',
        ]);

        Cashier::create($validated);

        return redirect()->route('cashier.index')->with('success', 'Rental saved successfully.');
    }

    public function edit(Cashier $cashier)
    {
        $vehicles = Vehicle::orderBy('reg_no')->get();
        return view('cashier.edit', compact('cashier', 'vehicles'));
    }

    public function update(Request $request, Cashier $cashier)
    {
        $validated = $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'due_day'        => 'required|integer|min:1|max:30',
            'amount'         => 'required|numeric|min:0',
            'bank_name'      => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:255',
            'rental_agreement_start_date'   => 'required|date',
            'rental_agreement_end_date'   => 'required|date',
        ]);

        $cashier->update($validated);

        return redirect()->route('cashier.index')->with('success', 'Rental updated successfully.');
    }

    public function destroy(Cashier $cashier)
    {
        $cashier->delete();
        return redirect()->route('cashier.index')->with('success', 'Rental deleted successfully.');
    }

    public function sendToFM($id)
    {
        $cashier = Cashier::findOrFail($id);

        if ($cashier->status !== 'send_to_fm') {
            $cashier->update(['status' => 'send_to_fm']);
        }

        return redirect()->route('cashier.index')->with('success', 'Cashier entry sent to FM.');
    }

    public function approvePayment($id)
    {
        $payment = PaymentCoordinator::findOrFail($id);

        if ($payment->status !== 'approved') {
            $payment->update(['status' => 'approved']);
        }

        return redirect()->back()->with('success', 'Payment approved successfully.');
    }

    public function viewBill($id)
    {
        $approval = PaymentCoordinator::with('cashier.vehicle')->findOrFail($id);

        $expenses = collect();

        // Get the first procurement linked to this approval (if any)
        $firstProcurement = $approval->procurements()->first();

        if ($firstProcurement) {
            // Take the first two parts of PO ID (like 'po-1' from 'po-1-1')
            $poPrefix = implode('-', array_slice(explode('-', $firstProcurement->po_id), 0, 2));

            // Get all outsourced procurements that match this prefix
            $expenses = Procurement::where('po_id', 'like', $poPrefix . '-%')
                ->where('status', 'outsourced')
                ->get();
        }

        // Get installments linked to cashier
        $installments = Installment::where('cashier_id', $approval->cashier_id)->get();

        return view('cashier.bill', compact('approval', 'expenses', 'installments'));
    }

    public function downloadBill($id)
    {
        $approval = PaymentCoordinator::with('cashier.vehicle')->findOrFail($id);

        $expenses = collect();

        $firstProcurement = $approval->procurements()->first();

        if ($firstProcurement) {
            // Take the first two parts of PO ID (like 'po-1' from 'po-1-1')
            $poPrefix = implode('-', array_slice(explode('-', $firstProcurement->po_id), 0, 2));

            // Get all outsourced procurements that match this prefix
            $expenses = Procurement::where('po_id', 'like', $poPrefix . '-%')
                ->where('status', 'outsourced')
                ->get();
        }

        // Get installments linked to cashier
        $installments = Installment::where('cashier_id', $approval->cashier_id)->get();

        $pdf = Pdf::loadView('cashier.bill-download', compact('approval', 'expenses', 'installments'));
        $fileName = 'Rental-Bill-' . $approval->cashier->vehicle->reg_no . '.pdf';

        return $pdf->download($fileName);
    }
}

