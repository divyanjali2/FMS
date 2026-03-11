<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Models\VehicleFreeze;
use App\Models\Inspection;
use Illuminate\Support\Facades\Log;
use App\Models\Rental;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\TransportService;
use App\Models\Vehicle;

class VehicleBookingController extends Controller
{
    private function canUserAccessVehicle($vehicleCompanyId)
    {
        $user = auth()->user();

        $srCompanies = [1, 2, 4, 5];
        $eliteCompanies = [3, 6];

        if ($user->is_sr && in_array($vehicleCompanyId, $srCompanies)) {
            return true;
        }

        if ($user->is_elite && in_array($vehicleCompanyId, $eliteCompanies)) {
            return true;
        }

        return false;
    }

    public function index()
    {
        $now = now();

        $today = Carbon::today();
        
        $todayArrivals = Rental::with(['vehicle.vehicleType', 'company'])
        ->whereDate('arrival_date', $today)
        ->where('repair_type', '!=', 'emergency')
        ->get();

        $todayDepartures = Rental::with(['vehicle.vehicleType', 'company'])
            ->where(function ($query) use ($today) {
                $query->whereDate('departure_date', $today)
                    ->orWhereDate('emer_departure_date', $today);
            })
            ->get();

                    $now = Carbon::now();

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
            $response = Http::timeout(10)->get('http://127.0.0.1:9000/api/chauffers');

            if ($response->successful()) {
                $chauffers = $response->json();
            }
        } catch (\Throwable $e) {
            $chauffers = [];
        }

        return view('vehicle_bookings.index', [
            'types'       => VehicleType::with('vehicleCategories')->get(),
            'vehicles' => Vehicle::with(['freezes', 'rentals.creator'])->where('status', 'active')->orderBy('reg_no')->get(),
            'freezes'     => VehicleFreeze::with('vehicle')->latest('start_date')->get(),
            'year'        => $now->year,
            'month'       => $now->month,
            'daysInMonth' => $now->daysInMonth,
            'emergencyInspections' => Inspection::where('repair_type', 'emergency')
                ->with(['vehicle.vehicleType', 'user'])
                ->latest()
                ->get(),
            'todayArrivals' => $todayArrivals,
            'todayDepartures' => $todayDepartures,
            'chauffers' => $chauffers,
            'transportServices' => $transportServices,
        ]);
    }

    public function bookingGrid($typeId, Request $request)
    {
        $year  = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::create($year, $month)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $vehicles = Vehicle::where('status', 'active') 
            ->where('vehicle_type_id', $typeId)
            ->when($request->filled('category'), fn ($q) =>
                $q->where('vehicle_category_id', $request->category)
            )
            ->when($request->filled('company'), function ($q) use ($request) {
                $company = $request->company;

                if ($company === 'Elite Rent A Car') {
                    $q->whereIn('company_id', [6, 3]);
                } elseif ($company === 'SR Rent A Car') {
                    $q->whereNotIn('company_id', [6, 3]);
                } else {
                    // All companies
                    // do nothing
                }
            })
            ->with([
                'company',
                'freezes',
                'rentals' => fn ($q) =>
                    $q->whereDate('arrival_date', '<=', $endOfMonth)
                    ->whereDate('departure_date', '>=', $startOfMonth),
                'rentals.creator',
                'rentals.inspections',
            ])
            ->get();

            $vehicles->transform(function ($vehicle) {
                $vehicle->can_interact = $this->canUserAccessVehicle($vehicle->company_id);
                return $vehicle;
            });

        $daysInMonth = $startOfMonth->daysInMonth;
        $monthName   = $startOfMonth->format('F Y');

        return response()->json([
            'html'      => view('vehicle_bookings.partials.grid', compact(
                'vehicles', 'year', 'month', 'daysInMonth', 'monthName'
            ))->render(),
            'monthName' => $monthName,
            'vehicles'  => $vehicles,
        ]);
    }

    public function markOnTour(Request $request, Rental $rental)
    {
        if (!$this->canUserAccessVehicle($rental->vehicle->company_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Log::info('[MARK ON TOUR] Request received', [
            'rental_id' => $rental->id ?? null,
            'current_status' => $rental->status ?? null,
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
        ]);

        if ($rental->status !== 'booked') {
            Log::warning('[MARK ON TOUR] Invalid status transition', [
                'rental_id' => $rental->id,
                'status' => $rental->status,
            ]);

            return response()->json([
                'message' => 'Only booked rentals can be marked as on tour.'
            ], 422);
        }

        $rental->update([
            'status' => 'rented',
        ]);

        Log::info('[MARK ON TOUR] Status updated successfully', [
            'rental_id' => $rental->id,
            'new_status' => 'rented',
            'updated_at' => $rental->updated_at,
        ]);

        return response()->json([
            'message' => 'Booking marked as on tour successfully.'
        ]);
    }

    public function changeVehicle(Request $request, Rental $rental)
    {
        if (!$this->canUserAccessVehicle($rental->vehicle->company_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Log::info('[CHANGE VEHICLE] Request received', [
            'rental_id' => $rental->id,
            'vehicle_id' => $request->vehicle_id,
            'user_id' => auth()->id(),
        ]);

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $rental->update([
            'vehicle_id' => $request->vehicle_id,
        ]);

        Log::info('[CHANGE VEHICLE] Vehicle updated successfully', [
            'rental_id' => $rental->id,
            'new_vehicle_id' => $rental->vehicle_id,
        ]);

        return response()->json([
            'message' => 'Vehicle updated successfully'
        ]);
    }

    public function changeVehicleList(Rental $rental)
    {
        $arrival   = \Carbon\Carbon::parse($rental->arrival_date);
        $departure = \Carbon\Carbon::parse($rental->departure_date);
        $vehicle   = $rental->vehicle;

        // Get frozen vehicle IDs
        $frozenVehicleIds = DB::table('vehicle_freezes')->pluck('vehicle_id');

        // Fetch active, non-frozen vehicles with rentals
        $vehicles = Vehicle::where('status', 'active')
            ->whereNotIn('id', $frozenVehicleIds)
            ->with('rentals')
            ->get()
            ->map(fn($v) => tap($v, function($v) use ($arrival, $departure, $vehicle) {
                $v->is_available = $v->rentals->every(fn($r) => 
                    \Carbon\Carbon::parse($r->arrival_date) > $departure ||
                    \Carbon\Carbon::parse($r->departure_date) < $arrival
                );
                $v->same_model = $v->vehicle_type_id === $vehicle->vehicle_type_id;
                $v->type_id = $v->vehicle_type_id;
            }))
            ->filter(fn($v) => $v->is_available || $v->id === $vehicle->id)
            ->sortByDesc('same_model')
            ->values();

        return response()->json([
            'rental'   => $rental->load('vehicle'),
            'vehicles' => $vehicles,
        ]);
    }

public function search(Request $request)
{
    $reg = $request->reg_no;
    $booking = $request->booking_no;

    $vehicles = Vehicle::query()
        ->when($reg, fn ($q) =>
            $q->where('reg_no', 'like', "%{$reg}%")
        )
        ->when($booking, fn ($q) =>
            $q->whereHas('rentals', fn ($r) =>
                $r->where('booking_number', 'like', "%{$booking}%")
            )
        )
        ->with(['rentals' => function ($q) use ($booking) {
            $q->where('booking_number', 'like', "%{$booking}%")
              ->select('id', 'vehicle_id', 'arrival_date');
        }])
        ->get();

    return response()->json($vehicles);
}


}