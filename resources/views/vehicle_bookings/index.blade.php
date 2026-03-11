@extends('layouts.app')

@section('content')
<style>
    .type-item { cursor:pointer; transition:.2s; }
    .category-name { cursor:pointer; transition:.2s; }
    .type-item:hover { background:#f0f0f0; transform:scale(1.03); }
    .active-type { background:#0d6efd!important; color:white!important; }

    .booking-table-container { overflow-x:auto; }
    .booking-table { border-collapse:collapse; width:100%; min-width:max-content; }
    .booking-table th, .booking-table td {
        border:1px solid #0000008c; padding:5px; text-align:center; white-space:nowrap; font-size:12px;
    }
    .booking-table th { background:#f2f2f2; }
    .booked { background:#08c1f9ff; color:#fff; border : 2px solid black !important; }    
        
    .current-booking {
        background: #f0e373;
        color: #000;
        border : 2px solid black !important;
    }

    .booking-cell[data-can-interact="0"] {
        cursor: not-allowed;
        opacity: 0.9;
    }

    .vehicle-frozen-date {
        background: #000 !important; 
        color: #fff !important;      
        text-align: center;
        vertical-align: middle;
    }

    .vehicle-frozen-date div {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-weight: 600;
    }

    td.past-booking {
        background: #08c1f9ff; 
        color: #fff; 
        border: 2px solid black !important;
    }

    .future-booking {
        background: #ed8a82 !important;
        color: #fff;
        border : 2px solid black !important;
    }

    .month-nav { display:flex; align-items:center; gap:10px; }
        body.hide-sidebar .sidebar {
        display: none !important;
    }

    body.hide-sidebar .content-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
    }

    .past-date {
        background: repeating-linear-gradient(
            45deg,
            #f8f9fa,
            #f8f9fa 6px,
            #e0e0e0 6px,
            #e0e0e0 12px
        );
        color: #6c757d;
        border: 1px dashed #6c757d !important;
    }

    .vehicle-frozen {
        background: #f1f1f1 !important;
        opacity: 0.35;
    }

    .vehicle-frozen .btn {
        display: none;
    }
    
    .legend-box {
        display:inline-block;
        width:15px;
        height:15px;
        border:1px solid #000;
        margin-right:5px;
        vertical-align:middle;
        font-weight: 600;
    }

    .booking-number {
        color: black;
        font-weight: 600;
    }

    .booked.legend-box { background:#08c1f9ff; }
    .current-booking.legend-box { background:#f0e373; border: 2px solid transparent !important;  }
    .past-booking.legend-box { background:#08c1f9ff; border: 2px solid transparent; }
    .future-booking.legend-box { background:#ed8a82; border: 2px solid transparent !important;  }

    .alternative-range {
        background: repeating-linear-gradient(
            310deg,
            #f8f9fa,
            #f8f9fa 6px,
            #e0e0e0 6px,
            #e0e0e0 12px
        );
        color: #07111a;
        border: 2px dashed #2a5179; 
        border-radius: 6px;          
        padding: 6px;               
    }
    .arrived-booking {
        background-color: #b2f2bb; 
        color: #000;
        font-weight: bold;
    }
    .arrived-with-inspection {
        background-color: #dac6dc; 
        color: #000;
        font-weight: bold;
    }

    /* scroll container */
    .booking-table-container{
        max-height: 70vh;
        overflow: auto;
        position: relative;
    }

    .booking-table{
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: max-content;
    }

    /* sticky header */
    .booking-table thead th{
        position: sticky;
        top: 0;
        z-index: 20;
        background: #f2f2f2;
    }

    .booking-table thead tr:nth-child(2) th{
        top: 29px; 
        z-index: 21;
        background: #f2f2f2;
    }

    .booking-table thead tr:first-child th:nth-child(1),
    .booking-table tbody td:nth-child(1){
        width:120px; min-width:120px; max-width:120px;
    }

    .booking-table thead tr:first-child th:nth-child(2),
    .booking-table tbody td:nth-child(2){
        width:220px; min-width:220px; max-width:220px;
    }

    .booking-table thead tr:first-child th:nth-child(3),
    .booking-table tbody td:nth-child(3){
        width:110px; min-width:110px; max-width:110px;
    }

    .booking-table thead tr:nth-child(2) th{
        width:34px; min-width:34px; max-width:34px;
    }

    .booking-table thead tr:first-child th:nth-child(1),
    .booking-table tbody td:nth-child(1){
        position: sticky;
        left: 0;
        z-index: 30;
        background: #fff;
    }

    .booking-table thead tr:first-child th:nth-child(2),
    .booking-table tbody td:nth-child(2){
        position: sticky;
        left: 120px; 
        z-index: 30;
        background: #fff;
    }

    .booking-table thead tr:first-child th:nth-child(3),
    .booking-table tbody td:nth-child(3){
        position: sticky;
        left: 340px; 
        z-index: 30;
        background: #fff;
    }

    .booking-table thead tr:first-child th:nth-child(1),
    .booking-table thead tr:first-child th:nth-child(2),
    .booking-table thead tr:first-child th:nth-child(3){
        background: #f2f2f2;
        z-index: 40;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h2 class="fw-bold text-center mb-0 flex-grow-1">Vehicle Bookings Calendar</h2>
    </div>
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div class="card shadow-sm w-50">
            <div class="card-header fw-bold">Vehicle Types</div>
            <div class="card-body d-flex gap-2 flex-wrap" id="vehicle-types-container">
                @foreach($types as $type)
                    <div class="me-3 type-wrapper" data-id="{{ $type->id }}">
                        <span class="type-item badge bg-secondary p-2 px-3">
                            {{ $type->type_name }}
                        </span>
                        <div class="mt-1 categories-container" style="display:none;">
                            @foreach($type->vehicleCategories as $category)
                                <span class="category-name badge bg-info text-dark p-1 px-2 category-item" data-category-id="{{ $category->id }}">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="btn-group ms-2" id="company-filter">
            <button class="btn btn-sm btn-outline-secondary active" data-company="">
                All
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-company="Elite Rent A Car">
                Elite Rent A Car
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-company="SR Rent A Car">
                SR Rent A Car
            </button>
        </div>

        <div class="d-flex gap-2 w-35">
            <input type="text" id="reg-filter" class="form-control"
                placeholder="Search by Reg No, Make , Model">

            <input type="text" id="booking-filter" class="form-control"
                placeholder="Search by Booking No">
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            Booking Calender

            <div class="d-flex gap-4">
                @php
                    $user = auth()->user();
                    $canManage = $user->hasRole(['admin', 'rent_a_car']);
                @endphp

                <button class="btn btn-sm btn-danger"  data-bs-toggle="modal" data-bs-target="#freezeVehicleModal" {{ $canManage ? '' : 'disabled' }}>
                    ❄ Freeze Vehicle
                </button>

                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#dailyRecordModal">
                    📝 Daily Records
                </button>

                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#transportServiceModal" data-type="shuttle" {{ $canManage ? '' : 'disabled' }} >
                    🚐 Shuttle
                </button>
            </div>

            <div class="month-nav">
                <button id="prev-month" class="btn btn-sm btn-outline-primary">&lt; Prev</button>
                <strong id="current-month"></strong>
                <button id="next-month" class="btn btn-sm btn-outline-primary">Next &gt;</button>
            </div>
        </div>
        <div class="card-body">
            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3" id="bookingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-tab-pane" type="button" role="tab">
                        📅 Booking Calendar
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="freeze-tab" data-bs-toggle="tab" data-bs-target="#freeze-tab-pane" type="button" role="tab">
                        ❄ Freezed Vehicles
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency-tab-pane" type="button" role="tab">
                        🚨 Emergency Inspections
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shuttle-tab" data-bs-toggle="tab" data-bs-target="#shuttle-tab-pane" type="button" role="tab">
                        � Shuttle Services
                    </button>
                </li>
            </ul>
            
            {{-- Tab Content --}}
            <div class="tab-content">

                {{-- Booking Calendar Tab --}}
                <div class="tab-pane fade show active" id="calendar-tab-pane" role="tabpanel">
                    <div id="booking-grid"></div>
                </div>

                {{-- Frozen Vehicles Tab --}}
                <div class="tab-pane fade" id="freeze-tab-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Extended End Date</th>
                                    <th>Extended Reason</th>
                                    <th>Reason</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($freezes as $freeze)
                                    <tr id="freeze-row-{{ $freeze->id }}">
                                        <td>{{ $freeze->vehicle->reg_no ?? '-' }}</td>
                                        <td>{{ $freeze->start_date }}</td>
                                        <td>{{ $freeze->old_end_date ? $freeze->old_end_date : $freeze->end_date }}</td>
                                        <td>{{ !empty($freeze->extend_reason) ? $freeze->end_date : '-' }}</td>
                                        <td>{{ !empty($freeze->extend_reason) ? $freeze->extend_reason : '-' }}</td>
                                        <td>{{ !empty($freeze->reason) ? $freeze->reason : '-' }}</td>
                                        <td>{{ !empty($freeze->remarks) ? $freeze->remarks : '-' }}</td>
                                        @php
                                            $user = auth()->user();
                                            $canManage = $user->hasRole(['admin', 'rent_a_car']);
                                        @endphp

                                        <td>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="unfreezeVehicle({{ $freeze->id }})"
                                                    {{ $canManage ? '' : 'disabled' }}>
                                                Unfreeze
                                            </button>

                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#extendFreezeModal" 
                                                    data-freeze-id="{{ $freeze->id }}" 
                                                    data-end-date="{{ $freeze->end_date }}"
                                                    {{ $canManage ? '' : 'disabled' }}>
                                                Extend
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No frozen vehicles found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Emergency Inspections Tab --}}
                <div class="tab-pane fade" id="emergency-tab-pane" role="tabpanel">
                    <div class="table-responsive mt-3">
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
                                        if($rental && $rental->repair_type === 'routine') continue;

                                        if($rental && $rental->emer_arrival_date && $rental->emer_departure_date) continue;
                                        $hasPendingTempInspections = \App\Models\TempInspection::where('inspection_id', $inspection->id)
                                            ->where('job_status', 'not completed')
                                            ->exists();
                                        if (!$hasPendingTempInspections) continue;
                                    @endphp
                                    <tr>
                                        <td>{{ $inspection->id }}</td>
                                        <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                                        <td>{{ $inspection->vehicle->vehicleType->type_name ?? '-' }}</td>
                                        <td><span class="badge bg-danger">Emergency</span></td>
                                        <td>{{ $inspection->user->name ?? 'N/A' }}</td>
                                        <td>-</td>
                                        @php
                                            $user = auth()->user();
                                            $canManage = $user->hasRole(['admin', 'rent_a_car']);
                                        @endphp

                                        <td>
                                            <button class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editEmerDates{{ $inspection->id }}"
                                                    {{ $canManage ? '' : 'disabled' }}>
                                                Edit Dates
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No emergency inspections available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Emergency Modals --}}
                    @foreach($emergencyInspections as $inspection)
                        @php $rental = $inspection->vehicle->rentals()->latest()->first(); @endphp
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

                {{-- Shuttle Services Tab --}}
               <div
    class="tab-pane fade"
    id="shuttle-tab-pane"
    role="tabpanel"
    aria-labelledby="shuttle-tab"
    tabindex="0"
>
    @php
        $dept = strtolower(trim(auth()->user()->department ?? ''));
        $isAdmin = strtolower(trim(auth()->user()->name ?? '')) === 'admin';
        $canManageShuttle = $isAdmin || $dept === 'rent a car department';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Shuttle Services</h5>

        @if($canManageShuttle)
            <button
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#transportServiceModal"
                data-type="shuttle"
            >
                🚐 Add Shuttle
            </button>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped align-middle" id="shuttleServicesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th>Passengers</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp

                    @foreach($transportServices as $ts)
                        @if(strtolower(trim($ts->type)) === 'shuttle')
                            @php
                                $canEditRow = $isAdmin || $canManageShuttle;
                            @endphp

                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $ts->vehicle->reg_no ?? '-' }}</td>
                                <td>{{ optional($ts->assigned_start_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $ts->assigned_end_at ? $ts->assigned_end_at->format('Y-m-d H:i') : '-' }}</td>
                                <td>{{ $ts->pickup_location }}</td>
                                <td>{{ $ts->dropoff_location }}</td>
                                <td>{{ $ts->passenger_count }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning {{ $canEditRow ? '' : 'disabled' }}"
                                        @if($canEditRow)
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTransportServiceModal"
                                            data-id="{{ $ts->id }}"
                                            data-type="{{ $ts->type }}"
                                            data-vehicle_id="{{ $ts->vehicle_id }}"
                                            data-chauffer_id="{{ $ts->chauffer_id }}"
                                            data-start="{{ $ts->assigned_start_at ? $ts->assigned_start_at->format('Y-m-d\TH:i') : '' }}"
                                            data-end="{{ $ts->assigned_end_at ? $ts->assigned_end_at->format('Y-m-d\TH:i') : '' }}"
                                            data-pickup="{{ $ts->pickup_location }}"
                                            data-dropoff="{{ $ts->dropoff_location }}"
                                            data-passengers="{{ $ts->passenger_count }}"
                                            data-trip_code="{{ $ts->trip_code }}"
                                            data-note="{{ $ts->note }}"
                                        @else
                                            type="button"
                                            disabled
                                            title="No permission for this type"
                                        @endif
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button
                                        class="btn btn-sm btn-danger {{ $canEditRow ? '' : 'disabled' }}"
                                        @if($canEditRow)
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteTransportServiceModal"
                                            data-id="{{ $ts->id }}"
                                        @else
                                            type="button"
                                            disabled
                                            title="No permission for this type"
                                        @endif
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

            <div class="modal fade" id="extendFreezeModal" tabindex="-1" aria-labelledby="extendFreezeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="extendFreezeForm" method="POST" action="{{ route('vehicle-freeze.extend') }}">
                        @csrf
                        <input type="hidden" name="freeze_id" id="modal_freeze_id">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="extendFreezeModalLabel">Extend Freeze</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label for="modal_end_date" class="form-label">New End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="modal_end_date" class="form-control" required>
                            </div>
                            <div class="modal-body">
                                <label for="reasonSelect" class="form-label">Extend Reason <span class="text-danger">*</span></label>
                                <!-- Dropdown -->
                                <select name="extend_reason" id="reasonSelect" class="form-control" required>
                                    <option value="">-- Select Reason --</option>
                                    <option value="need more time for vehicle repair" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for vehicle repair' ? 'selected' : '' }}>
                                        Need more time for vehicle repair
                                    </option>
                                    <option value="need more time for vehicle maintenance" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for vehicle maintenance' ? 'selected' : '' }}>
                                        Need more time for vehicle maintenance
                                    </option>
                                    <option value="need more time for police clearance" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for police clearance' ? 'selected' : '' }}>
                                        Need more time for police clearance
                                    </option>
                                    <option value="need more time for the insurance DR approval" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for the insurance DR approval' ? 'selected' : '' }}>
                                        Need more time for the insurance DR approval
                                    </option>
                                    <option value="owner requested more time for the repair" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'owner requested more time for the repair' ? 'selected' : '' }}>
                                        Owner requested more time for the repair
                                    </option>
                                    <option value="owner requested the vehicle for his personal use" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'owner requested the vehicle for his personal use' ? 'selected' : '' }}>
                                        Owner requested the vehicle for his personal use
                                    </option>
                                    <option value="personal use for the company staff" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'personal use for the company staff' ? 'selected' : '' }}>
                                        Personal use for the company staff
                                    </option>
                                    <option value="Other" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>

                                <!-- Text input for "Other" reason -->
                                <input type="text" name="other_reason" id="otherReasonInput" class="form-control mt-2" placeholder="Type reason" value="{{ old('other_reason', $freeze->other_reason ?? '') }}" style="{{ old('extend_reason', $freeze->extend_reason ?? '') == 'Other' ? '' : 'display: none;' }}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modals --}}
            @include('vehicle_freeze.partials.freeze-vehicle-modal')
            @include('vehicle_bookings.partials.booking-actions-modal')
            @include('vehicle_bookings.partials.daily-record-modal')
            @include('vehicle_bookings.partials.booking-actions-script')
            @include('transport-services._create_modal')
            @include('transport-services._create_modal_script')
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initShuttleTable() {
            if (!$.fn.DataTable.isDataTable('#shuttleServicesTable')) {
                $('#shuttleServicesTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[2, 'desc']],
                    columnDefs: [
                        {
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }
                    ]
                });
            } else {
                $('#shuttleServicesTable').DataTable().columns.adjust().responsive.recalc();
            }
        }

        $('button[data-bs-target="#shuttle-tab-pane"]').on('shown.bs.tab', function () {
            initShuttleTable();
        });

        if ($('#shuttle-tab-pane').hasClass('show') || $('#shuttle-tab-pane').hasClass('active')) {
            initShuttleTable();
        }
    });
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            document.body.classList.add('hide-sidebar');
            let currentDate = new Date();

            let selectedTypeId = null;
            let selectedCategoryId = null;
            let selectedCompany = '';

            function renderBookingGrid(typeId, categoryId = null){
                selectedTypeId = typeId;
                selectedCategoryId = categoryId;

                const year = currentDate.getFullYear();
                const month = currentDate.getMonth() + 1;
                
                function initTooltips() {
                    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                    existingTooltips.forEach(el => {
                        const instance = bootstrap.Tooltip.getInstance(el);
                        if (instance) {
                            instance.dispose();
                        }
                    });

                    // Initialize new tooltips
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.forEach(el => {
                        new bootstrap.Tooltip(el, {
                            delay: { show: 100, hide: 100 },
                            trigger: 'hover',  
                            fallbackPlacement: 'auto',
                            boundary: 'viewport'
                        });
                    });
                }


                const url =
                    `/vehicles/${typeId}/booking-grid?year=${year}&month=${month}`
                    + (categoryId ? `&category=${categoryId}` : '')
                    + (selectedCompany ? `&company=${encodeURIComponent(selectedCompany)}` : '');

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        const bookingGrid = document.getElementById('booking-grid');
                        bookingGrid.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            const t = bootstrap.Tooltip.getInstance(el);
                            if(t) t.dispose();
                        });

                        bookingGrid.innerHTML = data.html;

                        initTooltips();
                        document.getElementById('current-month').innerText = data.monthName || '';
                        filterVehicles();
                    });
            }

            document.querySelectorAll('#company-filter button').forEach(btn => {
                btn.addEventListener('click', function () {

                    document.querySelectorAll('#company-filter button')
                        .forEach(b => b.classList.remove('active'));

                    this.classList.add('active');
                    selectedCompany = this.dataset.company;

                    if (selectedTypeId) {
                        renderBookingGrid(selectedTypeId, selectedCategoryId);
                    }
                });

                var extendFreezeModal = document.getElementById('extendFreezeModal');
                    extendFreezeModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        var freezeId = button.getAttribute('data-freeze-id');
                        var endDate = button.getAttribute('data-end-date');

                        document.getElementById('modal_freeze_id').value = freezeId;
                        document.getElementById('modal_end_date').value = endDate ?? '';
                    });

            });

            const typeWrappers = document.querySelectorAll('.type-wrapper');

            typeWrappers.forEach((wrapper, i) => {
                const typeItem = wrapper.querySelector('.type-item');
                const categoriesContainer = wrapper.querySelector('.categories-container');
                const categoryItems = wrapper.querySelectorAll('.category-item');

                // Type click
                typeItem.addEventListener('click', function(){
                    // hide all categories
                    document.querySelectorAll('.categories-container').forEach(c => c.style.display = 'none');
                    document.querySelectorAll('.type-item').forEach(t => t.classList.remove('active-type'));
                    document.querySelectorAll('.category-item').forEach(c => c.classList.remove('active-type'));

                    categoriesContainer.style.display = 'block';
                    typeItem.classList.add('active-type');

                    selectedCategoryId = null; 
                    renderBookingGrid(wrapper.dataset.id);
                });

                // Category click
                categoryItems.forEach(cat => {
                    cat.addEventListener('click', function(){
                        categoryItems.forEach(c => c.classList.remove('active-type'));
                        this.classList.add('active-type');

                        const categoryId = this.dataset.categoryId;
                        renderBookingGrid(wrapper.dataset.id, categoryId);
                    });
                });

                // Auto-select first type
                if(i === 0){
                    categoriesContainer.style.display = 'block';
                    typeItem.classList.add('active-type');
                    renderBookingGrid(wrapper.dataset.id);
                }
            });

            // Month navigation
            document.getElementById('prev-month').onclick = function () {
                currentDate = new Date(
                    currentDate.getFullYear(),
                    currentDate.getMonth() - 1,
                    1
                );

                if (selectedTypeId) {
                    renderBookingGrid(selectedTypeId, selectedCategoryId);
                }
            };

            document.getElementById('next-month').onclick = function () {
                currentDate = new Date(
                    currentDate.getFullYear(),
                    currentDate.getMonth() + 1,
                    1
                );

                if (selectedTypeId) {
                    renderBookingGrid(selectedTypeId, selectedCategoryId);
                }
            };

            function filterVehicles() {
                const query = document.getElementById('reg-filter').value.toLowerCase().trim();
                const bookingTxt = document.getElementById('booking-filter').value.toLowerCase().trim();

                let matchedArrivalDate = null;

                document.querySelectorAll('.booking-table tbody tr').forEach(row => {
                    const reg = row.querySelector('.vehicle-reg')?.textContent.toLowerCase() || '';
                    const make = row.querySelector('.vehicle-make')?.textContent.toLowerCase() || '';
                    const model = row.querySelector('.vehicle-model')?.textContent.toLowerCase() || '';
                    const bookingCells = row.querySelectorAll('.booking-cell');

                    let bookingMatch = false;
                    bookingCells.forEach(cell => {
                        const cellText = cell.textContent.toLowerCase();
                        if (bookingTxt && cellText.includes(bookingTxt)) {
                            bookingMatch = true;
                            if (!matchedArrivalDate) {
                                matchedArrivalDate =
                                    cell.dataset.arrival ||
                                    cell.dataset.start ||
                                    cell.getAttribute('data-arrival') ||
                                    null;
                            }
                        }
                    });

                    let show = true;
                    if (query && !(reg.includes(query) || make.includes(query) || model.includes(query))) show = false;
                    if (bookingTxt && !bookingMatch) show = false;

                    row.style.display = show ? '' : 'none';
                });

                // Auto-jump to month if booking number found
                if (bookingTxt && matchedArrivalDate) {
                    const d = new Date(matchedArrivalDate);
                    if (!isNaN(d)) {
                        const newMonth = new Date(d.getFullYear(), d.getMonth(), 1);
                        if (currentDate.getFullYear() !== newMonth.getFullYear() || currentDate.getMonth() !== newMonth.getMonth()) {
                            currentDate = newMonth;
                            if (selectedTypeId) renderBookingGrid(selectedTypeId, selectedCategoryId);
                        }
                    }
                }
            }

            document.getElementById('reg-filter').addEventListener('input', filterVehicles);
            document.getElementById('booking-filter').addEventListener('input', filterVehicles);

            function searchByReg() {
                const query = document.getElementById('reg-filter').value.trim().toLowerCase();
                if (!query) return;

                fetch(`/vehicles/search?reg_no=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(vehicles => {
                        if (!vehicles.length) {
                            alert('No vehicle found');
                            return;
                        }

                        const v = vehicles.find(v =>
                            v.reg_no?.toLowerCase().includes(query) ||
                            v.make?.toLowerCase().includes(query) ||
                            v.model?.toLowerCase().includes(query)
                        ) || vehicles[0];

                        selectedTypeId = v.vehicle_type_id;
                        selectedCategoryId = null;

                        // Reset all type/category UI
                        document.querySelectorAll('.type-item').forEach(t => t.classList.remove('active-type'));
                        document.querySelectorAll('.categories-container').forEach(c => c.style.display = 'none');
                        document.querySelectorAll('.category-item').forEach(c => c.classList.remove('active-type'));

                        // Activate correct type
                        const typeWrapper = document.querySelector(`.type-wrapper[data-id="${v.vehicle_type_id}"]`);
                        if (typeWrapper) {
                            typeWrapper.querySelector('.categories-container').style.display = 'block';
                            typeWrapper.querySelector('.type-item').classList.add('active-type');
                        }

                        // Render booking grid **for the current month only**
                        renderBookingGrid(selectedTypeId, selectedCategoryId);
                    });
            }

            // Debounce for typing
            document.getElementById('reg-filter')
                .addEventListener('input', debounce(searchByReg, 400));


            function searchByBooking() {
                const bookingTxt = document.getElementById('booking-filter').value.trim();
                if (!bookingTxt) return;

                fetch(`/vehicles/search?booking_no=${encodeURIComponent(bookingTxt)}`)
                    .then(res => res.json())
                    .then(vehicles => {
                        if (!vehicles.length) {
                            alert('No booking found');
                            return;
                        }

                        const v = vehicles[0]; // first match
                        const rental = v.rentals[0];

                        // Auto-jump to the month of the first booking (if exists)
                        if (rental?.arrival_date) {
                            const d = new Date(rental.arrival_date);
                            currentDate = new Date(d.getFullYear(), d.getMonth(), 1);
                        }

                        // Auto-select correct type only (skip category)
                        selectedTypeId = v.vehicle_type_id;
                        selectedCategoryId = null; // no category selected

                        // Activate correct type in UI only
                        document.querySelectorAll('.type-item').forEach(t => t.classList.remove('active-type'));
                        document.querySelectorAll('.categories-container').forEach(c => c.style.display = 'none');
                        document.querySelectorAll('.category-item').forEach(c => c.classList.remove('active-type'));

                        const typeWrapper = document.querySelector(`.type-wrapper[data-id="${v.vehicle_type_id}"]`);
                        if (typeWrapper) {
                            typeWrapper.querySelector('.categories-container').style.display = 'block';
                            typeWrapper.querySelector('.type-item').classList.add('active-type');
                        }

                        renderBookingGrid(selectedTypeId, selectedCategoryId);
                    });
            }

            // Debounce typing for booking search
            document.getElementById('booking-filter')
                .addEventListener('input', debounce(searchByBooking, 400));



            document.getElementById('booking-filter')
                .addEventListener('input', debounce(searchByBooking, 400));

            function debounce(fn, delay) {
                let t;
                return () => {
                    clearTimeout(t);
                    t = setTimeout(fn, delay);
                };
            }

        

            // Booking cell click
            document.addEventListener('click', function(e) {
                const cell = e.target.closest('.booking-cell');
                if (cell) {
                    const row = cell.closest('td');

                    if (!row.classList.contains('future-booking')) {
                        return; 
                    }

                    const canInteract = cell.dataset.canInteract === "1";
                    if (cell.dataset.canInteract === "0") {
                        return;
                    }

                    const bookingId = cell.dataset.bookingId;
                    const arrival = cell.dataset.arrival;
                    const departure = cell.dataset.departure;

                    if (bookingId) {
                        loadAvailableVehicles(bookingId, arrival, departure);
                    }
                }
            });
        });

        window.loadAvailableVehicles = function (bookingId, arrival, departure) {
            window.activeBookingId = bookingId;

            fetch(`/rentals/${bookingId}/available-vehicles?arrival=${encodeURIComponent(arrival)}&departure=${encodeURIComponent(departure)}`)
                .then(res => res.json())
                .then(data => {

                    const select = document.getElementById("vehicle-select");

                    // Destroy old Choices instance
                    if (select.choicesInstance) {
                        select.choicesInstance.destroy();
                    }

                    // Reset select options
                    select.innerHTML = '<option value="">Select Vehicle...</option>';

                    data.vehicles.forEach(v => {
                        const option = document.createElement("option");
                        option.value = v.id;
                        option.textContent = `${v.reg_no}   ${v.make} ${v.model} | ${v.vehicle_category_name}`;
                            option.dataset.type = v.type_id; // store type for later filtering
                        select.appendChild(option);
                    });

                    // Re-create Choices
                    select.choicesInstance = new Choices(select, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                    });

                    new bootstrap.Modal(document.getElementById('changeVehicleModal')).show();
                });

                function showInitialVehicles() {
                    const options = select.querySelectorAll('option');
                    options.forEach(opt => {
                        if (opt.value === '') return; // keep "Select Vehicle..." option
                        opt.style.display = opt.dataset.type == 1 ? '' : 'none';
                    });
                }

                // Call this right after populating the dropdown
                showInitialVehicles();
        };
    </script>

    <script>
        function unfreezeVehicle(freezeId) {
            if (!confirm('Are you sure you want to unfreeze this vehicle?')) return;

            fetch(`/vehicle-freezes/${freezeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if(res.ok) return res.json();
                throw new Error('Failed to unfreeze');
            })
            .then(data => {
                location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to unfreeze vehicle');
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reasonSelect = document.getElementById('reasonSelect');
            const otherInput = document.getElementById('otherReasonInput');

            // Show/hide the other input based on selection
            function toggleOtherReason() {
                if (reasonSelect.value === 'Other') {
                    otherInput.style.display = 'block';
                    otherInput.required = true;
                } else {
                    otherInput.style.display = 'none';
                    otherInput.required = false;
                }
            }

            reasonSelect.addEventListener('change', toggleOtherReason);
            toggleOtherReason();
        });
    </script>

@endpush
@endsection
