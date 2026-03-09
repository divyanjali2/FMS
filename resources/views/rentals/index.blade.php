@extends('layouts.app')

@section('content')
<div class="container py-3" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); min-height: 100vh;">
    <h2 class="mb-4 text-center text-primary fw-bold">🚗 Rent a Car Dashboard</h2>

    <div class="mb-4 d-flex justify-content-end align-items-center gap-3 flex-wrap">
        <!-- Filter by Type -->
        <div class="d-flex align-items-center">
            <label for="commonFilter" class="fw-bold me-2">Filter by Type:</label>
            <select id="commonFilter" class="form-select w-auto">
                <option value="">All</option>
                @foreach($vehicleTypes as $type)
                    <option value="{{ $type->type_name }}">{{ $type->type_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter by Status -->
        <div class="d-flex align-items-center">
            <label for="statusFilter" class="fw-bold me-2">Filter by Status:</label>
            <select id="statusFilter" class="form-select w-auto">
                <option value="">All</option>
                <option value="Available">Available</option>
                <option value="On Tour">On Tour</option>
                <option value="Under Maintenance">Under Maintenance</option>
            </select>
        </div>
    </div>

    @if(auth()->user()->hasPermission('manage_rent_a_car'))
    <!-- Available Vehicles -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white fw-bold">Available Vehicles</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover rounded" id="vehiclesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Registration</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            @php
                                // Get all future bookings for this vehicle
                                $bookings = $futureBookings->where('vehicle_id', $vehicle->id);

                                // Get the most recent departure date (if any)
                                $latestDeparture = $bookings->max('departure_date');
                                $latestDepartureDate = $latestDeparture ? \Carbon\Carbon::parse($latestDeparture)->startOfDay() : null;

                                // Check if the latest departure date is in the past (date-only)
                                $today = \Carbon\Carbon::today();
                                $isPast = $latestDepartureDate && $latestDepartureDate->lt($today);
                                $underMaintenanceCount = \App\Models\Rental::where('vehicle_id', $vehicle->id)
                                    ->whereIn('status', ['arrived', 'emergency_completed'])
                                    ->whereDoesntHave('inspections', function ($q) {
                                        $q->whereNotNull('vehicle_condition')
                                        ->where('vehicle_condition', 'available', '');
                                    })
                                    ->whereHas('inspections.garageReports', function ($q) {
                                        $q->whereNotNull('issue_id');   
                                    })
                                    ->count();
                            @endphp

                            <tr>
                                <td>{{ $vehicle->id ?? '-' }}</td>
                                <td>{{ $vehicle->reg_no ?? '-' }}</td>
                                <td>{{ $vehicle->vehicleType->type_name ?? '-' }}</td>
                                <td>
                                    {{-- Maintenance status --}}
                                    @if($underMaintenanceCount > 0)
                                        <div>
                                            <span class="badge bg-secondary text-white">
                                                Under Maintenance ({{ $underMaintenanceCount }})
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Active bookings --}}
                                    @if($bookings->count())
                                        <div class="mt-1">
                                            @foreach($bookings as $booking)
                                                @php
                                                    $isToday = \Carbon\Carbon::parse($booking->arrival_date)->isToday();
                                                @endphp
                                                @if($isToday)
                                                    <span class="badge bg-warning text-dark">On Tour (Today)</span><br>
                                                @else
                                                    <span class="badge bg-danger">On Tour</span><br>
                                                @endif
                                                <small>
                                                    ({{ \Carbon\Carbon::parse($booking->arrival_date)->format('Y-m-d - H:i') }}
                                                    → {{ \Carbon\Carbon::parse($booking->departure_date)->format('Y-m-d - H:i') }} )
                                                    (<strong>BN : {{ $booking->booking_number ?? '-' }} </strong> - {{ $booking->salutation ?? '-' }} {{ $booking->driver_name ?? '-' }}) - {{ $booking->company->name ?? '-' }}
                                                </small><br>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Available status --}}
                                    @if($underMaintenanceCount === 0 && $bookings->count() === 0)
                                        <span class="badge bg-success">Available</span>
                                    @endif

                                    {{-- Past departure alert --}}
                                    @if($isPast)
                                        <div class="alert alert-warning mt-2 mb-0 p-1 text-center" style="font-size: 0.85rem;">
                                            ⚠️ The vehicle will be disabled until the first inspection is done!!
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{-- Rent button --}}
                                    <a href="{{ route('rentals.create', $vehicle->id) }}" class="btn btn-primary btn-sm {{ $isPast ? 'disabled' : '' }}" data-bs-toggle="tooltip" title="{{ $isPast ? 'Cannot rent: last departure already passed' : 'Rent this vehicle' }}">
                                        Rent
                                    </a>

                                    {{-- Cancel Booking --}}
                                    @if($bookings->count())
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#allBookingsModal{{ $vehicle->id }}">
                                            Cancel a Booking
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No vehicles available for rental.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
            @foreach($vehicles as $vehicle)
                @php
                    $bookings = $futureBookings->where('vehicle_id', $vehicle->id);
                @endphp
                @if($bookings->count())
                <div class="modal fade" id="allBookingsModal{{ $vehicle->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Cancel a Booking ({{ $vehicle->reg_no }})</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Arrival → Departure</th>
                                            <th>Passengers</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookings as $booking)
                                            <tr>
                                                <td>{{ $booking->driver_name }}</td>
                                                <td>{{ $booking->arrival_date }} → {{ $booking->departure_date }}</td>
                                                <td>{{ $booking->passengers }}</td>
                                                <td>
                                                    <form method="POST" action="{{ route('rentals.cancel', $booking->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    
    <hr class="my-5">

    <!-- Frozen Vehicles -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white fw-bold">Freezed Vehicles</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover rounded" id="frozenVehiclesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Registration</th>
                            <th>Type</th>
                            <th>Model</th>
                            <th>Make</th>
                            <th>Inspection Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($frozenVehicles as $vehicle)
                            <tr>
                                <td>{{ $vehicle->id ?? '-' }}</td>
                                <td>{{ $vehicle->reg_no ?? '-' }}</td>
                                <td>{{ $vehicle->vehicleType->type_name ?? '-' }}</td>
                                <td>{{ $vehicle->model ?? '-' }}</td>
                                <td>{{ $vehicle->make ?? '-' }}</td>
                                <td>
                                    @php
                                        $latestInspection = $vehicle->inspections()->latest()->first();
                                    @endphp
                                    @if($latestInspection)
                                        <span class="badge bg-danger text-white">
                                            {{ ucfirst($latestInspection->vehicle_status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestInspection)
                                        <div class="d-flex gap-2">
                                            <!-- View Inspection -->
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal{{ $latestInspection->id }}"> View Inspection
                                            </button>

                                            <!-- Unfreeze Button -->
                                            <form method="POST"
                                                action="{{ route('vehicles.unfreeze', $vehicle->id) }}"
                                                onsubmit="return confirm('Are you sure you want to unfreeze this vehicle?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Unfreeze
                                                </button>
                                            </form>
                                        </div>

                                        @include('inspections.show', ['inspection' => $latestInspection])
                                    @else
                                        <span class="badge bg-secondary">No Inspection</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No frozen vehicles at the moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- Emergency Inspections -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white fw-bold">🚨 Emergency Inspections</div>
        <div class="card-body">
            <table class="table table-striped table-hover rounded" id="emergencyInspectionsTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Repair Type</th>
                        <th>Created By</th>
                        <th>Days</th> 
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emergencyInspections as $inspection)
                        @php
                            $rental = $inspection->vehicle->rentals()->latest()->first();

                            // Skip if rental is routine
                            if($rental && $rental->repair_type === 'routine') {
                                continue;
                            }

                            // Skip if emergency dates are already set
                            if ($rental && $rental->emer_arrival_date && $rental->emer_departure_date) {
                                continue;
                            }

                            // Skip if all related temp inspections are completed
                            $hasPendingTempInspections = \App\Models\TempInspection::where('inspection_id', $inspection->id)
                                ->where('job_status', 'not completed')
                                ->exists();
                            if (!$hasPendingTempInspections) {
                                continue;
                            }
                    @endphp
                    <tr>
                        <td>{{ $inspection->id }}</td>
                        <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                        <td>{{ $inspection->vehicle->vehicleType->type_name ?? '-' }}</td>
                        <td><span class="badge bg-danger">Emergency</span></td>
                        <td>{{ $inspection->user->name ?? 'N/A' }}</td>
                        <td>-</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editEmerDates{{ $inspection->id }}">
                                Edit Dates
                            </button>
                        </td>
                    </tr>
                    @empty
                    <!-- <tr>
                        <td colspan="7" class="text-center">No emergency inspections available.</td>
                    </tr> -->
                    @endforelse
                </tbody>
            </table>
            @foreach($emergencyInspections as $inspection)
                @php
                    $rental = $inspection->vehicle->rentals()->latest()->first();
                @endphp
                <div class="modal fade" id="editEmerDates{{ $inspection->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('rentals.saveEmerDates', $rental->id ?? 0) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title fw-bold">Set Emergency Ride Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <label>Booking Number<span class="text-danger">*</span></label>
                                            <input type="text" name="emer_booking_number" class="form-control" required>
                                        </div>
                                        <div class="col-6">
                                            <label>Customer Name<span class="text-danger">*</span></label>
                                            <input type="text" name="emer_customer_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <label>No of Passengers<span class="text-danger">*</span></label>
                                            <input type="number" name="emer_no_of_passengers" class="form-control" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label>Emergency Arrival Date<span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="emer_arrival_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6 mb-3">
                                            <label>Emergency Departure Date<span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="emer_departure_date" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <hr class="my-5">

    <!-- Scheduled Departures -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
            <span>Scheduled Departures</span>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('rentals.index') }}" class="d-flex align-items-center gap-2">
                <span class="d-flex align-items-center" style="white-space: nowrap;">
                    Departure Date
                    <input type="date" name="departure_date" id="departure_date" class="form-control form-control-sm" value="{{ request('departure_date') }}" style="margin-left: 12px">
                </span>
                <a href="{{ route('rentals.index') }}" class="btn btn-danger btn-sm">Clear</a>
            </form>
        </div>        
        <div class="table-responsive card-body">
            @php
                $today = \Carbon\Carbon::today()->toDateString();
                $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
            @endphp

            <table class="table table-bordered table-hover rounded" id="ongoingRentalsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Vehicle</th>
                        <th>Booking Number</th>
                        <th>Customer Name</th>
                        <th>Arrival Date</th>
                        <th>Departure Date</th>
                        <th>Passengers</th>
                        <th>Repair Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ongoingRentals->sortBy(function($rental) {
                        return \Carbon\Carbon::parse($rental->emer_departure_date ?? $rental->departure_date)->startOfDay();
                    }) as $rental)
                        @php
                            $arrDate = $rental->emer_arrival_date ?? $rental->arrival_date;
                            $depDate = $rental->emer_departure_date ?? $rental->departure_date;

                            // Badge logic
                            $depDateStr = \Carbon\Carbon::parse($depDate)->toDateString();
                            if ($rental->repair_type === 'emergency2') {
                                $badge = '<span class="badge bg-danger ms-2">🚨 Emergency</span>';
                            } elseif ($depDateStr < $today) {
                                $badge = '<span class="badge bg-secondary ms-2">⏮️ Past</span>';
                            } elseif ($depDateStr === $today) {
                                $badge = '<span class="badge bg-danger ms-2">🚨 Today</span>';
                            } elseif ($depDateStr === $tomorrow) {
                                $badge = '<span class="badge bg-warning text-dark ms-2">⚠️ Tomorrow</span>';
                            } else {
                                $badge = '<span class="badge bg-info text-dark ms-2">😇 Upcoming</span>';
                            }

                            $bookingNumber = $rental->repair_type === 'emergency' ? $rental->emer_booking_number : $rental->booking_number;
                            $customerName = $rental->repair_type === 'emergency' ? $rental->emer_customer_name : $rental->driver_name;
                            $passengers = $rental->repair_type === 'emergency' ? $rental->emer_no_of_passengers : $rental->passengers;
                        @endphp

                        <tr>
                            <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                            <td>{{ $bookingNumber ?? '-' }}</td>
                            <td>{{ $customerName ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($arrDate)->format('Y-m-d - H:i') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($depDate)->format('Y-m-d - H:i') }}
                                {!! $badge !!}
                            </td>
                            <td>{{ $passengers }}</td>
                            <td>{{ ucfirst($rental->repair_type ?? '-') }}</td>
                            <td>
                                @if($rental->status === 'rented' || $rental->repair_type === 'emergency')
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#arriveModal{{ $rental->id }}">
                                        Mark Arrived
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#extendDepartureModal{{ $rental->id }}">
                                        Extend Departure
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#altVehicleModal{{ $rental->id }}">
                                        Add Alternative Vehicle
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @foreach($ongoingRentals as $rental)
                <div class="modal fade" id="extendDepartureModal{{ $rental->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold">Extend Departure Date</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Current Departure Date: <strong>{{ \Carbon\Carbon::parse($rental->departure_date)->format('Y-m-d - H:i') }}</strong></p>
                                <form action="{{ route('rentals.extendDeparture', $rental->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                   <div class="mb-3">
                                        <label class="form-label">New Departure Date</label>
                                        <input type="datetime-local" name="new_departure_date" class="form-control" requiredmin="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-info">Extend</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach($rentals->where('status', '!=', 'arriveds')->where('status', '!=', 'emergency_completed') as $rental)
                <div class="modal fade" id="arriveModal{{ $rental->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title fw-bold">Mark Vehicle as Arrived</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <p>Please choose the type of arrival for <strong>{{ $rental->vehicle->reg_no }}</strong></p>
                                <div class="d-flex justify-content-around mt-3">

                                    <!-- Routine Arrival -->
                                    <form action="{{ route('rentals.markArrived', $rental->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="arrival_type" value="routine">
                                        <button type="submit" class="btn btn-primary">Routine</button>
                                    </form>

                                    <!-- Emergency Arrival -->
                                    <form action="{{ route('rentals.markArrived', $rental->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="arrival_type" value="emergency">
                                        <button type="submit" class="btn btn-danger">Emergency Inspection</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach($ongoingRentals as $rental)
                <div class="modal fade" id="altVehicleModal{{ $rental->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold">Assign Alternative Vehicle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Current Vehicle:</strong> {{ $rental->vehicle->reg_no }} ({{ $rental->vehicle->vehicleType->type_name }})</p>
                                <p><strong>Customer:</strong> {{ $rental->driver_name }}</p>
                                <p><strong>Booking Dates:</strong> {{ $rental->arrival_date }} → {{ $rental->departure_date }}</p>

                                <div class="alert alert-info" role="alert">
                                    If the preferred vehicle is not listed, it may be unavailable due to overlapping bookings. 
                                    Consider <strong>cancelling or rescheduling a future booking</strong> to free up the vehicle.
                                </div>
                                <form action="{{ route('rentals.assignAlternativeVehicle', $rental->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label class="form-label">Select New Vehicle</label>
                                        <select class="form-select select2" name="new_vehicle_id" required>
                                            @foreach($vehicles as $vehicle)
                                                @php
                                                    $isBooked = $rentals->where('vehicle_id', $vehicle->id)
                                                        ->where('arrival_date', '<=', $rental->departure_date)
                                                        ->where('departure_date', '>=', $rental->arrival_date)
                                                        ->where('status', 'rented')
                                                        ->isNotEmpty();
                                                @endphp

                                                @if(!$isBooked && $vehicle->id != $rental->vehicle_id)
                                                    <option value="{{ $vehicle->id }}">
                                                        {{ $vehicle->reg_no }} - {{ $vehicle->vehicleType->type_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="row mt-4">
                                        {{-- Alternative Start Date --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Alternative Vehicle Start Date</label>
                                            <input type="date" name="alternative_start_date" class="form-control"min="{{ $rental->arrival_date }}"max="{{ $rental->departure_date }}"required >
                                            <small class="text-muted">
                                                Date from which the alternative vehicle will be used.
                                            </small>
                                        </div>
    
                                        {{-- Reason for Change --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Reason for Vehicle Change</label>
                                            <textarea name="change_reason" class="form-control" rows="3" placeholder="e.g. Vehicle breakdown, customer request, maintenance issue"required ></textarea>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Assign Vehicle</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach        
        </div>
    </div>

    <hr class="my-5">
    @endif

    <!-- Ongoing Repairs -->
    @if(auth()->user()->hasPermission('manage_rent_a_car'))
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white fw-bold">Ongoing Repairs</div>
        <div class="table-responsive card-body">
            <table class="table table-bordered table-hover rounded" id="ongoingRepairsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Issue / Fault</th>
                        <th>Inventory</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ongoingRepairs as $repair)
                        @php
                            $gi = $repair->issueInventory->garageInbuildIssue ?? null;
                            $issueName = $gi && $gi->issue ? $gi->issue->name : null;
                            $faultName = $gi && $gi->fault ? $gi->fault->name : null;
                            $issueFault = collect([$issueName, $faultName])->filter()->implode(' / ');
                            $vehicle = $repair->issueInventory->garageReport->inspection->vehicle ?? null;
                        @endphp
                        <tr>
                            <td>{{ $vehicle->reg_no ?? '-' }}</td>
                            <td>{{ $vehicle->vehicleType->type_name ?? '-' }}</td>
                            <td>{{ $issueFault ?: '-' }}</td>
                            <td>{{ $repair->issueInventory->inventory->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $repair->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="5" class="text-center">No ongoing repairs.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')

<script>
    $(document).ready(function() {
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                placeholder: "Search...",
                allowClear: true,
                width: '100%',
                dropdownParent: $(this) 
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        var defaultOptions = {
            pageLength: 10,
            lengthChange: true,
            ordering: true,  
            info: true,
            autoWidth: false
        };

        var otherTables = [
            '#vehiclesTable',
            '#arrivedRentalsTable',
            '#emergencyInspectionsTable',
            '#ongoingRepairsTable'
        ];
        otherTables.forEach(function(id) {
            $(id).DataTable(defaultOptions);
        });

        $('#ongoingRentalsTable').DataTable({
            pageLength: 10,
            lengthChange: true,
            ordering: false,  
            info: true,
            autoWidth: false
        });

        // Common filter
        $('#commonFilter').on('change', function() {
            var value = $(this).val();
            // Apply search to all tables
            otherTables.concat('#ongoingRentalsTable').forEach(function(id) {
                $(id).DataTable().search(value).draw();
            });
        });

        // Initialize tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });
</script>

<style>
    table.table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.08);
        transition: background-color 0.2s;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    .btn:hover {
        transform: translateY(-2px);
        transition: transform 0.2s;
    }

    table {
        border-radius: 8px;
        overflow: hidden;
    }

    hr {
        border-top: 2px solid #dee2e6;
    }
</style>

<script>
    $(document).ready(function () {
        let actionCallback = null;
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        const messageEl = document.getElementById('confirmationMessage');

        $('.ownerRepairBtn').on('click', function () {
            let inspectionId = $(this).data('id');

            messageEl.textContent = 'Are you sure you want to mark the vehicle received?';

            actionCallback = function () {
                $.ajax({
                    url: '{{ route("owner-repairs.updateStatus") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        inspection_id: inspectionId
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast('Status updated successfully.', 'success');
                            location.reload();
                        } else {
                            showToast('Failed to update status.', 'danger');
                        }
                    },
                    error: function () {
                        showToast('An error occurred.', 'danger');
                    }
                });
            };
            confirmationModal.show();
        });

        $('#confirmActionBtn').on('click', function () {
            if (actionCallback) actionCallback();
            confirmationModal.hide();
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#arrival_date, #departure_date').on('change', function() {
            let arrival_date = $('#arrival_date').val();
            let departure_date = $('#departure_date').val();

            $.ajax({
                url: "{{ route('rentals.index') }}", 
                type: "GET",
                data: { arrival_date: arrival_date, departure_date: departure_date },
                success: function(response) {
                    let newTableBody = $(response).find('#ongoingRentalsTable tbody').html();
                    $('#ongoingRentalsTable tbody').html(newTableBody);

                    if(arrival_date || departure_date) {
                        $('.btn-success').show();
                    } else {
                        $('.btn-success').hide();
                    }
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filters = ['statusFilter', 'commonFilter'].map(id => document.getElementById(id));
        const rows = document.querySelectorAll('#vehiclesTable tbody tr');

        const filterRows = () => {
            const [status, type] = filters.map(f => f.value.toLowerCase());
            rows.forEach(row => {
            const [typeText, statusText] = [
                row.children[2]?.innerText.toLowerCase() || '',
                row.children[3]?.innerText.toLowerCase() || ''
            ];
            row.style.display = (
                (!status || statusText.includes(status)) &&
                (!type || typeText.includes(type))
            ) ? '' : 'none';
            });
        };

        filters.forEach(f => f.addEventListener('change', filterRows));
    });
</script>

@endpush
@endsection
