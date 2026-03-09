@extends('layouts.app')

@section('content')
<div class="container">
    <div style="background-color: #820000" class="p-3 text-white rounded">
        <h3 class="fw-bold">Vehicle Status Dashboard</h3>
    </div>

    <!-- Vehicles in repair -->
    <h5 class="mb-3 mt-4">🚗 Vehicles in Garage Repair</h5>
    <div class="card mb-4">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Inspection ID</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Updated At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inProgress as $inspectionId => $records)
                        @php $inspection = $records->first()->inspection; @endphp
                        <tr>
                            <td>00{{ $inspection->id }}</td>
                            <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                            <td><span class="badge bg-warning">In Progress</span></td>
                            <td>{{ $records->max('updated_at')->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal{{ $inspection->id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No vehicles in repair.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Modals for Vehicles in Repair -->
            @foreach($inProgress as $inspectionId => $records)
                @php $inspection = $records->first()->inspection; @endphp
                <div class="modal fade" id="modal{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title">Work Details (Inspection #00{{ $inspection->id }})</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6 class="fw-bold">Inspection Info</h6>
                                <p><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>
                                <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>

                                <h6 class="fw-bold mt-3">Work Records</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Inventory</th>
                                                <th>Issue / Fault</th>
                                                <th>Status</th>
                                                <th>Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                @php
                                                    $issueName = $record->issueInventory->garageInbuildIssue->issue->name ?? null;
                                                    $faultName = $record->issueInventory->garageInbuildIssue->fault->name ?? null;

                                                    // Set badge color based on status
                                                    $status = $record->status;
                                                    $badgeClass = match($status) {
                                                        'in_progress' => 'bg-warning',
                                                        'work_done'   => 'bg-success',
                                                        default       => 'bg-secondary',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>{{ $record->id }}</td>
                                                    <td>{{ $record->issueInventory->inventory->name ?? '-' }}</td>
                                                    <td>
                                                        @if($issueName || $faultName)
                                                        {{ $issueName }}
                                                        {{ $faultName }}
                                                        @endif
                                                    </td>
                                                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                                                    <td>{{ $record->updated_at->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Vehicles Released -->
    <h5 class="mb-3">✅ Vehicles Released</h5>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Inspection ID</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Updated At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Group releases by inspection ID
                        $releasedByInspection = $released->groupBy(fn($r) => $r->fleetPostCheck->inspection->id);
                    @endphp

                    @forelse($releasedByInspection as $inspectionId => $releases)
                        @php $inspection = $releases->first()->fleetPostCheck->inspection; @endphp
                        <tr>
                            <td>00{{ $inspection->id ?? '-' }}</td>
                            <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                            <td><span class="badge bg-success">Released</span></td>
                            <td>{{ $releases->max('updated_at')->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#releasedModal{{ $inspection->id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No released vehicles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Modals for Released Vehicles --}}
            @foreach($releasedByInspection as $inspectionId => $releases)
                @php $inspection = $releases->first()->fleetPostCheck->inspection; @endphp
                <div class="modal fade" id="releasedModal{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Vehicle History (Inspection #00{{ $inspection->id }})</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6 class="fw-bold">Inspection Info</h6>
                                <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>
                                <p><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>

                                <h6 class="fw-bold mt-4">All Work Records & Summary</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Inventory</th>
                                                <th>Issue / Fault</th>
                                                <th>Times Occurred</th>
                                                <th>Status</th>
                                                <th>Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($inspection->gmWorkStatuses ?? [] as $record)
                                                @php
                                                    $issue = $record->issueInventory->garageInbuildIssue->issue->name ?? null;
                                                    $fault = $record->issueInventory->garageInbuildIssue->fault->name ?? null;

                                                    $issueCount = $issue ? ($issueFaultCounts[$inspection->vehicle->id]['issues'][$issue] ?? 0) : null;
                                                    $faultCount = $fault ? ($issueFaultCounts[$inspection->vehicle->id]['faults'][$fault] ?? 0) : null;
                                                @endphp

                                                <tr>
                                                    <td>00{{ $record->id }}</td>
                                                    <td>{{ $record->issueInventory->inventory->name ?? '-' }}</td>
                                                    <td>
                                                        {!! $issue ? $issue . '<br>' : '' !!}
                                                        {!! $fault ? $fault : '' !!}
                                                    </td>
                                                    <td>
                                                        {!! $issueCount ? $issueCount . '<br>' : '' !!}
                                                        {!! $faultCount ? $faultCount : '' !!}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                                                    </td>
                                                    <td>{{ $record->updated_at->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Vehicle Selection for Issue/Fault History -->
    <h5 class="mt-5 mb-3 fw-bold">🔎 Vehicle Fault History</h5>
    <div class="card mb-4 p-3">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Select Vehicle</label>
                <select id="vehicleSelect" class="form-select">
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->reg_no }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="vehicleHistoryTable">
                <thead class="table-dark">
                    <tr>
                        <th>Faults</th>
                        <th>Times Occurred</th>
                        <th>Date Occured</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Select a vehicle to view history.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('vehicleSelect').addEventListener('change', function() {
        let vehicleId = this.value;
        let tbody = document.querySelector('#vehicleHistoryTable tbody');

        if (!vehicleId) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Select a vehicle to view history.</td></tr>';
            return;
        }

        fetch(`/vehicle-status/history/${vehicleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No records found for this vehicle.</td></tr>';
                    return;
                }

                let html = '';
                data.forEach(record => {
                    html += `<tr>
                        <td>${record.issue_fault}</td>
                        <td>${record.count}</td>
                        <td>${record.dates.join('<br>')}</td>
                    </tr>`;
                });

                tbody.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error fetching data.</td></tr>';
            });
    });
</script>
@endsection
