@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h3 class="mb-3 fw-bold text-center">Vehicles Inspection Dashboard</h3>

    <!-- Arrived Vehicles -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
            <span>Vehicle Inspections</span>

            <form method="GET" action="{{ route('inspections.index') }}" id="filterForm" class="d-flex align-items-center gap-2">
                <span class="d-flex align-items-center" style="white-space: nowrap;">
                    Departure Date <input type="date" id="departure_date" name="departure_date" class="form-control form-control-sm" value="{{ request('departure_date') }}" style="margin-left: 12px">
                </span>

                <a href="{{ route('inspections.index') }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i> Clear
                </a>
            </form>
        </div>
        <div class="table-responsive card-body">
            <table class="table table-bordered table-hover rounded" id="inspectionRentalsTable">
                <thead class="table-dark">
                    <tr>
                        <th>RENT ID</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Customer Name</th>
                        <th>Arrival Date / Time</th>
                        <th>Departure Date / Time</th>
                        <th>Passengers</th>
                        <th>Repair Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $today = \Carbon\Carbon::today()->toDateString();
                        $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
                    @endphp
                    @foreach($inspectionRentals as $rental)
                        @php
                            $depDate = \Carbon\Carbon::parse($rental->departure_date)->toDateString();
                           if ($depDate < $today) {
                                $badge = '<span class="badge bg-secondary ms-2">⏮️ Past</span>';
                            } elseif ($depDate === $today) {
                                $badge = '<span class="badge bg-danger ms-2">🚨 Today</span>';
                            } elseif ($depDate === $tomorrow) {
                                $badge = '<span class="badge bg-warning text-dark ms-2">⚠️ Tomorrow</span>';
                            } else {
                                $badge = '<span class="badge bg-info text-dark ms-2">🔜 upcoming</span>';
                            }

                            $showInspectionButton = false;
                            $showSentBadge = false;
                            $upcomingBadge = false;

                            if ($rental->status === 'rented') {
                                $upcomingBadge = true;
                            }

                            // Normal arrived rental (routine)
                            if ($rental->status === 'arrived' && $rental->repair_type !== 'emergency') {
                                $inspectionSentToGarage = $rental->vehicle->inspections()
                                    ->where('rental_id', $rental->id)
                                    ->where('status', 'Sent to Garage')
                                    ->exists();

                                $showInspectionButton = !$inspectionSentToGarage;
                                $showSentBadge = $inspectionSentToGarage;
                            }

                            if (
                                ($rental->status === 'emergency_completed' && $rental->repair_type === 'emergency') ||
                                ($rental->status === 'arrived' && $rental->repair_type === 'emergency' && is_null($rental->emer_arrival_date))
                            ) {

                            $inspectionSent = $rental->vehicle->inspections()
                                ->where('rental_id', $rental->id)
                                ->whereIn('status', ['Sent to Garage', 'sent_to_garage'])
                                ->exists();

                            $hasPendingTempInspections = \App\Models\TempInspection::whereHas('inspection', function($q) use ($rental) {
                                    $q->where('rental_id', $rental->id);
                                })
                                ->where('job_status', 'not completed')
                                ->exists();

                                if ($inspectionSent) {
                                    $showInspectionButton = $hasPendingTempInspections;
                                    $showSentBadge = !$hasPendingTempInspections;
                                } else {
                                    $showInspectionButton = true;
                                    $showSentBadge = false;
                            }
                            }
                            // {
                            //     $hasPendingTempInspections = \App\Models\TempInspection::whereHas('inspection', function($q) use ($rental) {
                            //             $q->where('rental_id', $rental->id);
                            //         })
                            //         ->where('job_status', 'not completed')
                            //         ->exists();

                            //     if ($hasPendingTempInspections) {
                            //         $showInspectionButton = true;
                            //         $showSentBadge = false;
                            //     } else {
                            //         $showInspectionButton = false;
                            //         $showSentBadge = true;
                            //     }
                            // }
                        @endphp

                        @if($showSentBadge)
                            @continue
                        @endif
                        <tr
                        class="{{ $showInspectionButton ? 'table-success' : ($rental->status === 'emergency_completed' ? 'table-danger' : '') }}"
                        data-upcoming="{{ $upcomingBadge ? 1 : 0 }}"
                        data-start="{{ $showInspectionButton ? 1 : 0 }}"
                        >
                            <td>{{ $rental->id ?? '-' }}</td>
                            <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                            <td>{{ $rental->vehicle->vehicleType->type_name ?? '-' }}</td>
                            <td>{{ $rental->salutation ?? '-' }} {{ $rental->driver_name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($rental->arrival_date)->format('Y-m-d - H:i') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($rental->departure_date)->format('Y-m-d - H:i') }}
                                {!! $badge !!}
                            </td>
                            <td>{{ $rental->passengers }}</td>
                            <td class="{{ $rental->repair_type === 'emergency' ? 'text-danger fw-bold' : '' }}">
                                {{ ucfirst($rental->repair_type) }}

                                @if($rental->repair_type === 'emergency' && $rental->emer_arrival_date && $rental->emer_departure_date)
                                    <span class="text-dark">(On Tour)</span>
                                @endif
                            </td>
                            <td>
                                @if($showInspectionButton)
                                    <a href="{{ route('inspection.create', [
                                            'vehicle' => $rental->vehicle->id,
                                            'rental_id' => $rental->id,
                                            'repair_type' => $rental->repair_type
                                        ]) }}"
                                    class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Start vehicle inspection">
                                    Start Inspection
                                    </a>
                                @elseif($upcomingBadge)
                                    <span class="badge bg-info text-dark">upcoming Inspection</span>
                                @else
                                    <span class="badge bg-secondary">Vehicle Sent</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Routine Inspections -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">Routine Inspections</div>
        <div class="table-responsive card-body">
            <table class="table table-bordered table-striped" id="routine">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Job Code</th>
                        <th>Vehicle No</th>
                        <th>Model</th>
                        <th>Date</th>
                        <th>Odometer</th>
                        <th>Service Type</th>
                        <th>Created By</th>
                        {{-- <th width="180">Actions</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($routineInspections as $inspection)
                        <tr>
                            <td>{{ $inspection->id }}</td>
                            <td>{{ $inspection->job_code }}</td>
                            <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                            <td>{{ $inspection->vehicle->model ?? '-' }}</td>
                            <td>{{ $inspection->inspection_date }}</td>
                            <td>{{ $inspection->odometer_reading }}</td>
                            <td>{{ $inspection->service_type ? ucwords(str_replace('_', ' ', $inspection->service_type)) : '-' }}</td>
                            <td>{{ $inspection->user->name ?? 'N/A' }}</td>
                            {{-- <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#inspectionModal{{ $inspection->id }}">
                                    View
                                </button>
                            </td> --}}
                        </tr>
                    @empty
                        <!-- <tr>
                            <td colspan="8" class="text-center">No routine inspections found.</td>
                        </tr> -->
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Emergency Inspections -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white fw-bold">Emergency Inspections</div>
        <div class="table-responsive card-body">
            <table class="table table-bordered table-striped" id="emergencyInspection">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Job Code</th>
                        <th>Vehicle No</th>
                        <th>Model</th>
                        <th>Date</th>
                        <th>Odometer</th>
                        <th>Created By</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emergencyInspections as $group)
                        @php
$rentalId = $group->rental_id;
                            $latestTwo = $group->inspections;
                            $latestInspection = $latestTwo->first();
                            // Fetch pending temp faults (job_status = not completed)
                            $tempFaults = \App\Models\TempInspection::whereIn('inspection_id', $latestTwo->pluck('id'))
                                ->where('job_status', 'not completed')
                                ->with('fault')
                                ->get();
                        @endphp
                        <tr>
                            <td>{{ $latestInspection->id }}</td>
                            <td>{{ $latestInspection->job_code }}</td>
                            <td>{{ $latestInspection->vehicle->reg_no ?? '-' }}</td>
                            <td>{{ $latestInspection->vehicle->model ?? '-' }}</td>
                            <td>{{ $latestInspection->inspection_date }}</td>
                            <td>{{ $latestInspection->odometer_reading }}</td>
                            <td>{{ $latestInspection->user->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($latestTwo->count() >= 2)
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#emergencyModal{{ $rentalId }}">
                                        Edit
                                    </button>
                                @else
                                    <span class="badge bg-secondary">Only 1 record</span>
                                @endif

                                {{-- Always show View Faults button --}}
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewFaultsModal{{ $latestInspection->id }}">
                                    View Faults
                                </button>
                            </td>
                        </tr>

                        {{-- View Faults Modal --}}
                        <div class="modal fade" id="viewFaultsModal{{ $latestInspection->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-dark">
                                        <h5 class="modal-title fw-bold">Pending Faults Reported by Rent Team for Inspection #{{ $latestInspection->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($tempFaults->isEmpty())
                                            <p class="text-muted">No faults reported for this inspection.</p>
                                        @else
                                            @foreach($tempFaults as $temp)
                                                <div class="row mb-2">
                                                    <div class="col-md-6">
                                                        <strong>
                                                            {{ $temp->fault_id ? optional($temp->fault)->name : 'No faults reported' }}
                                                        </strong>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $temp->status ?? 'pending')) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Emergency Edit Modal --}}
                        <div class="modal fade" id="emergencyModal{{ $rentalId  }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                {{-- <form method="POST" action="{{ route('emergency.saveFaults', $vehicleId) }}"> --}}
                                <form method="POST" action="{{ route('emergency.saveFaults', ['rentalId' => $rentalId]) }}">
                                    @csrf
                                    <input type="hidden" name="latest_inspection_id" value="{{ $latestInspection->id }}">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title fw-bold">Resolve Emergency Faults ({{ $latestInspection->vehicle->reg_no }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php
                                                $validFaults = $tempFaults->whereNotNull('fault_id');
                                            @endphp

                                            @if($validFaults->isEmpty())
                                                <p class="text-muted">No faults reported.</p>
                                            @else
                                                @foreach($validFaults as $temp)
                                                    <div class="row mb-2">
                                                        <div class="col-md-6 d-flex align-items-center">
                                                            <input type="checkbox" name="faults[{{ $temp->id }}][checked]" value="1" class="me-2">
                                                            <strong>{{ optional($temp->fault)->name }}</strong>
                                                        </div>

                                                        <div class="col-md-6">
                                                            @if($temp->fault && strtoupper($temp->fault->name) === 'FUEL')
                                                                <input 
                                                                    type="number"
                                                                    name="faults[{{ $temp->id }}][percentage]"
                                                                    class="form-control"
                                                                    min="0"
                                                                    max="100"
                                                                    placeholder="Enter fuel %"
                                                                    value="{{ is_numeric($temp->status) ? $temp->status : '' }}"
                                                                >
                                                            @else
                                                                <select name="faults[{{ $temp->id }}][status]" class="form-select">
                                                                    <option value="" {{ empty($temp->status) ? 'selected' : '' }}>Select status</option>
                                                                    <option value="scratch" {{ $temp->status === 'scratch' ? 'selected' : '' }}>Scratch</option>
                                                                    <option value="dent" {{ $temp->status === 'dent' ? 'selected' : '' }}>Dent</option>
                                                                    <option value="tear" {{ $temp->status === 'tear' ? 'selected' : '' }}>Tear</option>
                                                                    <option value="crack" {{ $temp->status === 'crack' ? 'selected' : '' }}>Crack</option>
                                                                    <option value="broken" {{ $temp->status === 'broken' ? 'selected' : '' }}>Broken</option>
                                                                    <option value="missing" {{ $temp->status === 'missing' ? 'selected' : '' }}>Missing</option>
                                                                    <option value="not_working" {{ $temp->status === 'not_working' ? 'selected' : '' }}>Not Working</option>
                                                                    <option value="less_fuel" {{ $temp->status === 'less_fuel' ? 'selected' : '' }}>Less Fuel</option>
                                                                    <option value="exceed" {{ $temp->status === 'exceed' ? 'selected' : '' }}>Exceed</option>
                                                                    <option value="dirty" {{ $temp->status === 'dirty' ? 'selected' : '' }}>Dirty</option>
                                                                </select>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <!-- <tr>
                            <td colspan="8" class="text-center">No emergency inspections found.</td>
                        </tr> -->
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')

<script>
$(function () {
  let mode = 'all';

  // Filter only the inspections table
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable.id !== 'inspectionRentalsTable') return true;
    if (mode === 'all') return true;

    const tr = settings.aoData[dataIndex].nTr;
    const upcoming = $(tr).data('upcoming') == 1;
    const start    = $(tr).data('start') == 1;

    return mode === 'upcoming' ? upcoming : mode === 'start' ? start : true;
  });

  const inspectionTable = $('#inspectionRentalsTable').DataTable({
    pageLength: 10,
    autoWidth: false,
    dom:
      '<"dt-top d-flex justify-content-between align-items-center mb-2"' +
        'l' +
        '<"dt-right d-flex align-items-center gap-2"f<"inspectionFilter">>' +
      '>' +
      'rtip'
  });

  $('.inspectionFilter').html(`
    <select id="inspectionMode" class="form-select form-select-sm" style="width:170px">
      <option value="all">All</option>
      <option value="upcoming">Upcoming</option>
      <option value="start">Start inspection</option>
    </select>
  `);

  $('#inspectionMode').on('change', function () {
    mode = this.value;
    inspectionTable.draw();
  });

  $('#routine, #emergencyInspection').DataTable({ pageLength: 10, autoWidth: false });

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>


<script>
    $(document).ready(function() {
        $('#arrival_date, #departure_date').on('change', function() {
            let arrival_date = $('#arrival_date').val();
            let departure_date = $('#departure_date').val();

            $.ajax({
                url: "{{ route('inspections.index') }}", 
                type: "GET",
                data: { arrival_date: arrival_date, departure_date: departure_date },
                success: function(response) {
                    let newTableBody = $(response).find('#inspectionRentalsTable tbody').html();
                    $('#inspectionRentalsTable tbody').html(newTableBody);

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
@endpush
@endsection
