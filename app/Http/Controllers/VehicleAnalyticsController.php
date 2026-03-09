<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Vehicle;
use Carbon\Carbon;

class VehicleAnalyticsController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $rentals = Rental::with(['vehicle.company'])
            ->whereDate('arrival_date', '<=', $endOfMonth)
            ->whereDate('departure_date', '>=', $startOfMonth)
            ->orderBy('arrival_date')
            ->get();

        $vehicles = Vehicle::with('company')
            ->where('status', '!=', 'disabled')
            ->get();

        return view('vehicle-analytics.index', compact('rentals', 'vehicles'));
    }
}