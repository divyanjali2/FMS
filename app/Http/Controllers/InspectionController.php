<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Vehicle;
use App\Models\GarageReport;
use App\Models\TempInspection;
use App\Models\Rental;
use App\Models\Fault;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function index()
    {
        $routineInspections = Inspection::with(['vehicle','user'])
            ->where('repair_type', 'routine')
            ->orderBy('inspection_date','desc')
            ->paginate(5, ['*'], 'routinePage');

        $raw = Inspection::with(['vehicle','user'])
            ->where('repair_type', 'emergency')
            ->orderBy('inspection_date','desc')
            ->get();

        $groupedByRental = $raw->groupBy('rental_id');

        $groups = $groupedByRental->map(function ($inspections, $rentalId) {
            $inspections = $inspections->sortByDesc('inspection_date')->values();
            $latestTwo = $inspections->take(2);

            $pendingFaults = TempInspection::whereIn('inspection_id', $latestTwo->pluck('id'))
                ->where('job_status', 'not completed')
                ->exists();

            if (!$pendingFaults) {
                return null;
            }

            return (object)[
                'rental_id'   => $rentalId,
                'inspections' => $latestTwo,
            ];
        })->filter()->values();

        $perPage = 5;
        $pageName = 'emergencyPage';
        $page = (int) request()->get($pageName, 1);
        $offset = ($page - 1) * $perPage;
        $sliced = $groups->slice($offset, $perPage)->values();

        $emergencyInspections = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $groups->count(),
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]
        );

        $routineInspections->withPath(request()->url());

        $departureDate = request('departure_date');

        $inspectionRentals = Rental::with(['vehicle.vehicleType'])
            ->whereIn('status', ['rented', 'arrived', 'emergency_completed'])
            ->when($departureDate, function ($query) use ($departureDate) {
                $query->whereDate('departure_date', $departureDate);
            })
            ->latest('created_at')
            ->get();

        $arrivedRentals = Rental::with(['vehicle.vehicleType'])
            ->where('status', 'arrived')
            ->where('repair_type', '!=', 'emergency')
            ->latest('created_at')
            ->get();

        $emergencyCompletedRentals = Rental::with(['vehicle.vehicleType'])
            ->where('status', 'emergency_completed')
            ->latest('created_at')
            ->get();

        $emergencyInspections->withPath(request()->url());

        return view('inspections.index', compact('routineInspections', 'emergencyInspections','arrivedRentals','emergencyCompletedRentals','inspectionRentals'));
    }

    public function sendToGarage(Inspection $inspection)
    {
        $inspection->status = 'sent_to_garage';
        $inspection->save();

        return redirect()->route('inspections.index')->with('success', 'Inspection sent to garage successfully.');
    }

    public function create($vehicleId = null)
    {
        $vehicle = $vehicleId ? Vehicle::findOrFail($vehicleId) : null;
        $faults = Fault::all()->groupBy('type');
        $repair_type = request()->get('repair_type', 'routine');

        $rental_id = request()->get('rental_id');

        $pendingTempInspections = collect();

        if ($rental_id) {
            $latestInspections = \App\Models\Inspection::where('rental_id', $rental_id)
                ->latest('id') 
                ->take(2)
                ->pluck('id');

            $pendingTempInspections = TempInspection::whereIn('inspection_id', $latestInspections)
                ->where('job_status', 'not completed')
                ->with('fault')
                ->get();
        }

        return view('inspections.create', compact(
            'vehicle',
            'faults',
            'repair_type',
            'pendingTempInspections',
            'rental_id'
        ));
    }

    public function edit(Inspection $inspection)
    {
        $vehicles = Vehicle::all();
        $faults = Fault::all()->groupBy('type');

        return view('inspections.edit', compact('inspection', 'vehicles', 'faults'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'repair_type' => strtolower(trim($request->repair_type ?? 'routine'))
        ]);

        $fuelFaultIds = \App\Models\Fault::where('name', 'FUEL')->pluck('id')->toArray();

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'inspection_date' => 'required|date',
            'odometer_reading' => 'required|integer',
            'insurance_claim' => 'nullable|boolean',
            'remarks' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'repair_type' => 'nullable|in:routine,emergency',
            'vehicle_status' => 'required|in:freeze,arrived',
            'vehicle_condition' => 'required|in:under_maintenance,available',
            'service_type' => 'required|in:wash_vehicle,full_service',
            // optionally validate rental_id if present
            'rental_id' => 'nullable|exists:rentals,id',
        ]);

        $data = $request->except('faults');
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $year = \Carbon\Carbon::parse($request->inspection_date)->format('Y');
        $vehicleFolder = str_replace(' ', '_', strtoupper($vehicle->reg_no));
        $dateFolder = \Carbon\Carbon::parse($request->inspection_date)->format('Y-m-d');

        $currentRental = \App\Models\Rental::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['rented', 'arrived'])
            ->latest('created_at')
            ->first();

        // Handle images
        $data['images'] = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store("inspections/{$year}-{$vehicleFolder}/{$dateFolder}", 'public');
                $data['images'][] = asset('storage/' . $path);
            }
        }

        $data['job_code'] = 'JOB-' . strtoupper(uniqid());
        $data['created_by'] = auth()->id();
        $data['vehicle_status'] = $request->vehicle_status;
        $data['vehicle_condition'] = $request->vehicle_condition;

        if ($request->filled('rental_id')) {
            $data['rental_id'] = $request->input('rental_id');
        } elseif ($currentRental) {
            $data['rental_id'] = $currentRental->id;
        } else {
            $data['rental_id'] = null;
        }

        $data['status'] = 'Sent to Garage';

        // Create inspection record
        $inspection = Inspection::create($data);

        if ($request->repair_type === 'emergency' && $inspection->rental_id) {

            $rental = \App\Models\Rental::find($inspection->rental_id);

            if ($rental && $rental->status === 'rented') {
                $rental->update([
                    'status'      => 'arrived',
                    'repair_type' => 'emergency',
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($rental)
                    ->withProperties([
                        'inspection_id' => $inspection->id,
                        'vehicle_id'    => $inspection->vehicle_id,
                    ])
                    ->log('Rental marked as arrived via emergency inspection');
            }
        }

        // Handle faults for BOTH routine and emergency
        $faults = $request->input('faults', []);
        $hasCheckedFaults = false;

        if (!empty($faults)) {
            foreach ($faults as $faultId => $faultData) {
                if (isset($faultData['checked']) && $faultData['checked'] == 1) {
                    if (in_array((int)$faultId, $fuelFaultIds, true)) {
                        $status = isset($faultData['percentage']) && $faultData['percentage'] !== ''
                            ? (int) $faultData['percentage']
                            : null;
                    } else {
                        $status = $faultData['status'] ?? 'missing';
                    }
                    if ($request->repair_type === 'emergency') {
                        // Create temp inspection
                        TempInspection::create([
                            'inspection_id' => $inspection->id,
                            'fault_id'      => $faultId,
                            'status'        => $status,
                            'type'          => 'emergency',
                            'job_status'    => 'not completed',
                        ]);
                    } else {
                        DB::table('inspection_faults')->insert([
                            'inspection_id' => $inspection->id,
                            'fault_id'      => $faultId,
                            'status'        => $status,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }

                    $hasCheckedFaults = true;
                }
            }
        }

        if ($request->repair_type === 'emergency' && !$hasCheckedFaults) {
            TempInspection::create([
                'inspection_id' => $inspection->id,
                'fault_id'      => null,
                'status'        => null,
                'type'          => 'emergency',
                'job_status'    => 'not completed',
            ]);
        }

        GarageReport::create([
            'inspection_id' => $inspection->id,
            'status' => 'pending'
        ]);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($inspection)
            ->withProperties([
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'inspection_id' => $inspection->id,
                'vehicle_id' => $inspection->vehicle_id,
                'rental_id' => $inspection->rental_id ?? null,
            ])
            ->log($request->repair_type === 'emergency'
                ? 'Created emergency inspection'
                : 'Created routine inspection');

        return redirect()->route('inspections.index')->with('success', 'Inspection added successfully.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('vehicle','faults');
        return view('inspections.show', compact('inspection'));
    }

    public function updateStatus(Inspection $inspection)
    {
        $inspection->update(['status' => 'Sent to Garage']);
        return back()->with('success','Inspection sent to garage.');
    }

public function saveEmergencyFaults(Request $request, $rentalId)
    {
        $latestInspectionId = $request->latest_inspection_id;

        // Get the latest two inspection IDs for this vehicle
        $latestTwo = \App\Models\Inspection::where('rental_id', $rentalId)
            ->orderBy('id', 'desc')
            ->take(2)
            ->pluck('id');

        $hasSavedFault = false; // Track if any faults were actually processed

        if ($request->has('faults') && count($request->faults) > 0) {
            foreach ($request->faults as $tempId => $faultData) {
                if (isset($faultData['checked']) && $faultData['checked'] == 1) {
                    $temp = TempInspection::find($tempId);

                    if ($temp) {
                        // Save into inspection_faults pivot with latest inspection ID
                        DB::table('inspection_faults')->insert([
                            'inspection_id' => $latestInspectionId,
                            'fault_id'      => $temp->fault_id,
                            'status'        => $faultData['status'] ?? $temp->status,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);

                        $hasSavedFault = true;
                    }
                }
            }
        }

        // ⚠️ If no faults were checked or provided — still create one TempInspection record
        if (!$hasSavedFault) {
            TempInspection::create([
                'inspection_id' => $latestInspectionId,
                'fault_id'      => null,
                'status'        => null,
                'type'          => 'emergency',
                'job_status'    => 'not completed',
            ]);
        }

        // ✅ Mark ALL temp_inspections for the latest two inspection IDs as completed
        TempInspection::whereIn('inspection_id', $latestTwo)
            ->update(['job_status' => 'completed']);

        // ✅ Update latest inspection record
        Inspection::where('id', $latestInspectionId)->update([
            'status'      => 'repair',
            'repair_type' => 'routine',
            'updated_at'  => now(),
        ]);

        return redirect()->route('inspections.index')
            ->with('success', 'Emergency faults saved, temp inspections completed, and inspection updated.');
    }
}
