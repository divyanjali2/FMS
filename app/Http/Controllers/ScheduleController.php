<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TransportService;
use App\Models\Chauffer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\VehicleType;

class ScheduleController extends Controller
{
    public function index()
    {
        // $chauffers = Chauffer::latest()->get();

        $now = Carbon::now();

        $vehicleTypes = VehicleType::orderBy('type_name')->get();

        $vehicles = Vehicle::query()
            ->where('status', '!=', 'disabled')
            ->whereDoesntHave('freezes', function ($q) use ($now) {
                $q->where('start_date', '<=', $now)
                  ->where(function ($qq) use ($now) {
                      $qq->whereNull('end_date')->orWhere('end_date', '>=', $now);
                  });
            })
            ->orderBy('reg_no')
            ->get();

        $transportServices = TransportService::with(['vehicle','chauffer'])
            ->latest()
            ->get();

        $chauffers = [];

        try {
            $response = Http::timeout(10)->get('https://exploredrive.lk/api/chauffers');

            if ($response->successful()) {
                $chauffers = $response->json();
            }
        } catch (\Throwable $e) {
            $chauffers = [];
        }

        return view('schedule.index', compact('chauffers', 'vehicles', 'transportServices','vehicleTypes'));
    }

    public function availableVehicles(Request $request)
    {
        $start = $request->start;
        $end   = $request->end;

        if (!$start) {
            return response()->json([]);
        }

        $startDate = Carbon::parse($start);
        $endDate   = $end ? Carbon::parse($end) : Carbon::parse($start);

        $vehicles = Vehicle::query()
            ->where('status', '!=', 'disabled')

            ->whereDoesntHave('freezes', function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                ->where(function ($qq) use ($startDate) {
                    $qq->whereNull('end_date')
                        ->orWhere('end_date', '>=', $startDate);
                });
            })

            ->whereDoesntHave('rentals', function ($q) use ($startDate, $endDate) {
                $q->where(function ($r) use ($startDate, $endDate) {
                    $r->whereDate('arrival_date', '<=', $endDate)
                    ->whereDate('departure_date', '>=', $startDate);
                });
            })

            ->orderBy('reg_no')
            ->get(['id', 'reg_no']);

        return response()->json($vehicles);
    }
}
