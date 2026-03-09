<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\FleetVehicleRelease;
use App\Models\VehicleType;
use App\Models\Rental;
use App\Models\Company;
use App\Models\GarageReport;
use App\Models\GMWorkStatus;
use App\Models\VehicleFreeze;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $departureDate = $request->input('departure_date'); 

        $rentals = Rental::with(['vehicle.vehicleType', 'company'])
            ->orderBy('arrival_date', 'asc')
            ->get();

        $ongoingRentals = $rentals->filter(function ($rental) use ($departureDate) {

            $isOngoingRental =
                $rental->status === 'rented' ||
                (
                    $rental->repair_type === 'emergency' &&
                    $rental->status !== 'emergency_completed' &&
                    $rental->emer_arrival_date &&
                    $rental->emer_departure_date &&
                    $rental->emer_booking_number &&
                    $rental->emer_customer_name &&
                    $rental->emer_no_of_passengers
                );

            if (! $isOngoingRental) {
                return false;
            }

            $depDate = \Carbon\Carbon::parse($rental->emer_departure_date ?? $rental->departure_date);

            if ($departureDate) {
                return $depDate->isSameDay($departureDate);
            }

            return true;
        });

        $futureBookings = Rental::where('status', 'rented')->get();

        $released = FleetVehicleRelease::where('status', 'vehicle_release')
            ->with('fleetPostCheck.inspection.vehicle')
            ->get()
            ->pluck('fleetPostCheck.inspection.vehicle')
            ->filter();

        $garageDone = GarageReport::where('status', 'owner_repair_done')
            ->with('inspection.vehicle')
            ->get()
            ->pluck('inspection.vehicle')
            ->filter();

        $frozenVehicleIds = VehicleFreeze::pluck('vehicle_id')->toArray();

        $vehicles = Vehicle::with('vehicleType')
            ->whereDoesntHave('inspections', function($query) {
                $query->where('vehicle_status', 'freeze');
            })
            ->whereNotIn('id', $frozenVehicleIds) 
            ->get()
            ->merge($released)
            ->merge($garageDone)
            ->unique('id')
            ->values();


        $frozenVehicles = Vehicle::whereHas('inspections', function($query) {
            $query->where('vehicle_status', 'freeze');
        })->with('vehicleType', 'inspections')->get();

        $vehicleTypes = VehicleType::all();

        $ongoingRepairs = GMWorkStatus::where('status', 'in_progress')
            ->with([
                'issueInventory.inventory',
                'issueInventory.garageInbuildIssue.issue',
                'issueInventory.garageInbuildIssue.fault',
                'issueInventory.garageReport.inspection.vehicle.vehicleType'
            ])
            ->get();

        $emergencyInspections = Inspection::where('repair_type', 'emergency')
            ->with(['vehicle.vehicleType', 'user'])
            ->latest()
            ->get();

        $arrivedRentals = Rental::with(['vehicle.vehicleType'])
            ->where('status', 'arrived')
            ->where('repair_type', '!=', 'emergency')
            ->latest('created_at')            
            ->get();

        return view('vehicle.bookings', compact(
            'rentals',
            'vehicles',
            'ongoingRentals',
            'futureBookings',
            'arrivedRentals',
            'vehicleTypes',
            'ongoingRepairs',
            'emergencyInspections',
            'departureDate', 
            'frozenVehicles'
        ));
    }

    public function checkBookingNumber(Request $request)
    {
        $exists = Rental::where('booking_number', $request->booking_number)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function markArrived(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);

        if ($request->arrival_type === 'emergency') {
            return redirect()->route('inspection.create', [
                'rental_id' => $rental->id,
                'vehicle'   => $rental->vehicle_id,
                'repair_type' => 'emergency'
            ]);
        }

        $rental->status = 'arrived';
        $rental->repair_type = 'routine';
        $rental->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->withProperties([
                'ip' => request()->ip(),
                'arrival_type' => 'routine',
            ])
            ->log('Vehicle marked as arrived (routine)');

        return redirect()
            ->route('vehicle.bookings')
            ->with('success', 'Vehicle marked as arrived.');
    }

    public function create($vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $companies = Company::all();

        // Already booked rentals
        $bookedDates = Rental::where('vehicle_id', $vehicleId)
            ->where('status', '!=', 'arrived') 
            ->get(['arrival_date', 'departure_date'])
            ->map(function ($rental) {
                return [
                    'from' => \Carbon\Carbon::parse($rental->arrival_date)->format('Y-m-d'),
                    'to'   => \Carbon\Carbon::parse($rental->departure_date)->format('Y-m-d'),
                ];
            });

        // Frozen dates
        $frozenDates = VehicleFreeze::where('vehicle_id', $vehicleId)
            ->get(['start_date', 'end_date'])
            ->map(function ($freeze) {
                return [
                    'from' => \Carbon\Carbon::parse($freeze->start_date)->format('Y-m-d'),
                    'to'   => $freeze->end_date
                        ? \Carbon\Carbon::parse($freeze->end_date)->format('Y-m-d')
                        : \Carbon\Carbon::now()->format('Y-m-d'),
                ];
            });

        return view('rentals.create', compact('vehicle', 'companies', 'bookedDates', 'frozenDates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'company_id' => 'required|exists:companies,id',
            'booking_number' => 'required',
            'driver_name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:10', 
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:arrival_date',
            'vehicle_pickup' => 'required|date',
            'passengers' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $company = Company::findOrFail($request->company_id);
        $date = now()->format('Ymd');  

        // Create the rental first
        $rental = Rental::create([
            'vehicle_id' => $request->vehicle_id,
            'company_id' => $company->id,
            'driver_name' => $request->driver_name,
            'booking_number' => $request->booking_number,
            'salutation' => $request->salutation,  
            'arrival_date' => $request->arrival_date,
            'departure_date' => $request->departure_date,
            'vehicle_pickup' => $request->vehicle_pickup,
            'passengers' => $request->passengers,
            'notes' => $request->notes,
            'status' => 'booked',
            'repair_type' => 'routine', 
            'reference_no' => '',
        ]);

        // Generate reference number
        $companyName = preg_replace('/[^A-Za-z0-9]/', '', $company->name); 
        $referenceNo = "FMS/{$companyName}/{$rental->id}-{$date}";
        $rental->update(['reference_no' => $referenceNo]);

        // Log the action
        activity()
            ->causedBy(auth()->user()) 
            ->performedOn($rental)    
            ->withProperties([
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ])
            ->log('Created Rental');

        return redirect()->route('vehicle.bookings')->with('success', 'Vehicle rented successfully.');
    }

    public function destroy(Rental $rental)
    {
        $rental->delete();
        return redirect()->route('vehicle.bookings')->with('success', 'Rental deleted.');
    }

    public function saveEmerDates(Request $request, $id)
    {
        $request->validate([
            'emer_booking_number' => 'required|string|max:255',
            'emer_customer_name' => 'required|string|max:255',
            'emer_no_of_passengers' => 'required|integer|min:1',
            'emer_arrival_date' => 'required|date',
            'emer_departure_date' => 'required|date|after_or_equal:emer_arrival_date',
        ]);

        $rental = Rental::findOrFail($id);
        $rental->emer_booking_number = $request->emer_booking_number;
        $rental->emer_customer_name = $request->emer_customer_name;
        $rental->emer_no_of_passengers = $request->emer_no_of_passengers;
        $rental->emer_arrival_date = $request->emer_arrival_date;
        $rental->emer_departure_date = $request->emer_departure_date;
        $rental->save();

        activity()
        ->causedBy(auth()->user())        
        ->performedOn($rental)            
        ->withProperties([
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'changes' => $rental->getChanges(), 
        ])
        ->log('Updated emergency dates for rental');

        return redirect()->route('vehicle.bookings')->with('success', 'Emergency dates saved successfully.');
    }

    public function completeEmergency($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->status = 'emergency_completed'; 
        $rental->save();

        // ✅ Log this action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->withProperties([
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'changes' => $rental->getChanges(),
            ])
            ->log('Marked emergency rental as completed');

        return redirect()
            ->route('vehicle.bookings')
            ->with('success', 'Emergency rental marked as completed.');
    }

    public function getVehicleRentedBookings($rentalId)
    {
        $rental = Rental::findOrFail($rentalId);

        $relatedRentals = Rental::with('vehicle')
            ->where('vehicle_id', $rental->vehicle_id)
            ->where('status', 'booked')
            ->get();

        return response()->json([
            'vehicle' => $rental->vehicle,
            'relatedRentals' => $relatedRentals
        ]);
    }

    public function cancel($id)
    {
        $rental = Rental::findOrFail($id);

        if ($rental->status !== 'booked') {
            return redirect()->back()->with(
                'error',
                'Only booked rentals can be cancelled.'
            );
        }

        // Keep a copy for activity log
        $rentalCopy = $rental->replicate();

        // Delete the rental
        $rental->delete();

        // Activity log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($rentalCopy)
            ->withProperties([
                'ip'        => request()->ip(),
                'url'       => request()->fullUrl(),
                'method'    => request()->method(),
                'rental_id' => $rentalCopy->id,
                'status'    => 'booked',
            ])
            ->log('Cancelled and deleted booked rental');

        return redirect()->back()->with(
            'success',
            'Booked rental cancelled successfully.'
        );
    }

    public function getVehicleTourBookings($rentalId)
    {
        $rental = Rental::findOrFail($rentalId);

        $relatedRentals = Rental::with('vehicle')
            ->where('vehicle_id', $rental->vehicle_id)
            ->where('status', 'rented')
            ->get();

        return response()->json([
            'vehicle' => $rental->vehicle,
            'relatedRentals' => $relatedRentals
        ]);
    }

    public function remove($id)
    {
        $rental = Rental::findOrFail($id);

        if ($rental->status !== 'rented') {
            return redirect()->back()->with(
                'error',
                'Only rented bookings can be cancelled.'
            );
        }

        // Keep a copy for activity log
        $rentalCopy = $rental->replicate();

        // Delete the rental
        $rental->delete();

        // Activity log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($rentalCopy)
            ->withProperties([
                'ip'        => request()->ip(),
                'url'       => request()->fullUrl(),
                'method'    => request()->method(),
                'rental_id' => $rentalCopy->id,
                'status'    => 'booked',
            ])
            ->log('Cancelled and deleted booked rental');

        return redirect()->back()->with(
            'success',
            'Booked rental cancelled successfully.'
        );
    }

    public function getAlternativeVehicles($rentalId)
    {
        $rental = Rental::with('vehicle')->findOrFail($rentalId);

        $bookedVehicleIds = Rental::where(function($query) use ($rental) {
                $query->whereBetween('arrival_date', [$rental->arrival_date, $rental->departure_date])
                    ->orWhereBetween('departure_date', [$rental->arrival_date, $rental->departure_date]);
            })
            ->where('status', 'rented')
            ->pluck('vehicle_id');

            $frozenVehicleIds = DB::table('vehicle_freezes')->pluck('vehicle_id')->toArray();

            $availableVehicles = Vehicle::with('vehicleType')
                ->whereNotIn('id', $bookedVehicleIds)      
                ->where('id', '!=', $rental->vehicle_id)   
                ->where('status', 'active')                
                ->whereNotIn('id', $frozenVehicleIds)      
                ->get();

        return response()->json([
            'rental' => $rental,
            'availableVehicles' => $availableVehicles
        ]);
    }

    public function assignAlternativeVehicle(Request $request, $rentalId)
    {
        $request->validate([
            'new_vehicle_id' => 'required|exists:vehicles,id',
            'alternative_start_date' => 'required|date',
            'change_reason' => 'required|string|max:500',
        ]);

        $rental = Rental::findOrFail($rentalId);

        Rental::where('id', $rental->id)->update([
            'status' => 'arrived',
            'repair_type' => 'routine',
            'alternative_start_date' => $request->alternative_start_date,
            'change_reason' => $request->change_reason,
            'is_old_vehicle' => true, 
        ]);

        // ----- Create alternative rental -----
        $newRental = $rental->replicate();

        $newRental->vehicle_id = $request->new_vehicle_id;
        $newRental->status = 'rented';
        $newRental->repair_type = 'routine';
        $newRental->reference_no = null;
        $newRental->alternative_start_date = $request->alternative_start_date;
        $newRental->change_reason = $request->change_reason;
        $newRental->is_old_vehicle = false; 

        $newRental->save();

        // ----- Log activity -----
        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->withProperties([
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'old_vehicle_id' => $rental->vehicle_id,
                'new_vehicle_id' => $newRental->vehicle_id,
                'alternative_start_date' => $request->alternative_start_date,
                'change_reason' => $request->change_reason,
            ])
            ->log('Assigned an alternative vehicle to rental');

        return redirect()
            ->route('vehicle.bookings')
            ->with('success', 'Alternative vehicle assigned successfully.');
    }

    public function extendDeparture(Request $request, $id)
    {
        $request->validate([
            'new_departure_date' => 'required|date|after_or_equal:arrival_date',
        ]);

        $rental = Rental::findOrFail($id);
        $oldDeparture = $rental->departure_date;

        $rental->departure_date = $request->new_departure_date;
        $rental->save();

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'old_departure_date' => $oldDeparture,
                'new_departure_date' => $rental->departure_date,
            ])
            ->log('Extended rental departure date');

        return redirect()->route('vehicle.bookings')->with('success', 'Departure date extended successfully.');
    }

    public function availableVehicles(Rental $rental, Request $request)
    {
        $arrival   = $request->arrival;
        $departure = $request->departure;

        $vehiclesQuery = Vehicle::with('vehicleCategory')
            ->whereDoesntHave('rentals', function ($q) use ($arrival, $departure) {
                $q->where(function ($query) use ($arrival, $departure) {
                    $query->whereBetween('arrival_date', [$arrival, $departure])
                        ->orWhereBetween('departure_date', [$arrival, $departure]);
                })
                ->whereNotIn('status', ['arrived']);
            });

        // ✅ FILTER BY VEHICLE TYPE (Car / Van)
        if ($request->filled('type_id')) {
            $vehiclesQuery->where('vehicle_type_id', $request->type_id);
        }

        // ✅ FILTER BY VEHICLE CATEGORY
        if ($request->filled('category_id')) {
            $vehiclesQuery->where('vehicle_category_id', $request->category_id);
        }

        $vehicles = $vehiclesQuery
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'reg_no' => $v->reg_no,
                    'make' => $v->make,
                    'model' => $v->model,
                    'vehicle_category_name' => $v->vehicleCategory->name ?? 'N/A',
                ];
            });

        return response()->json(['vehicles' => $vehicles]);
    }

    public function changeVehicle(Rental $rental, Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $rental->update([
            'vehicle_id' => $request->vehicle_id
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function unfreezeVehicle(Vehicle $vehicle)
    {
        $inspection = $vehicle->inspections()->latest()->first();

        if (! $inspection || $inspection->vehicle_status !== 'freeze') {
            return redirect()->back()->with('error', 'Vehicle is not frozen.');
        }

        $inspection->update([
            'vehicle_status' => 'completed'
        ]);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($inspection)
            ->withProperties([
                'vehicle_id' => $vehicle->id,
                'old_status' => 'freeze',
                'new_status' => 'completed',
                'ip' => request()->ip(),
            ])
            ->log('Vehicle unfreezed and inspection marked as completed');

        return redirect()->back()->with('success', 'Vehicle has been unfrozen successfully.');
    }

    public function markOnTour(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);

        if ($rental->repair_type === 'emergency') {
            $rental->status = 'emergency_completed';
        } else {
            $rental->status = 'arrived';
            $rental->repair_type = $request->arrival_type;
        }

        $rental->save();

        return response()->json(['success' => true]);
    }
}
