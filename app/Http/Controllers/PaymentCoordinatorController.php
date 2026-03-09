<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use App\Models\Procurement;
use App\Models\PaymentCoordinator;
use Illuminate\Http\Request;

class PaymentCoordinatorController extends Controller
{
    public function index()
    {
        $cashiers = Cashier::with('vehicle')
            ->where('status', 'send_to_fm')
            ->latest()
            ->paginate(10);

        return view('fleet-decisions.index', compact('cashiers'));
    }

    public function store(Request $request, $cashierId)
    {
        $request->validate([
            'procurement_id' => 'required|exists:procurements,id',
        ]);

        $proc = Procurement::findOrFail($request->procurement_id);

        PaymentCoordinator::create([
            'cashier_id'    => $cashierId,
            'procurement_id'=> $proc->id,
            'total_price'   => $proc->price,
            'status'        => 'send_to_cashier',
        ]);

        return redirect()->route('payment-coordinator.index')->with('success', 'Payment sent to cashier.');
    }
}
