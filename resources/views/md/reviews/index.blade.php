@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h3 class="mb-4 fw-bold">Managing Director - Reviews</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Inspection ID</th>
                        <th>Vehicle</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspections as $inspection)
                        <tr>
                            <td>#00{{ $inspection->id }}</td>
                            <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#gmModal{{ $inspection->id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No inspections found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- All modals should be rendered OUTSIDE the table --}}
@foreach($inspections as $inspection)
    <div class="modal fade" id="gmModal{{ $inspection->id }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Inspection #00{{ $inspection->id }} - Vehicle {{ $inspection->vehicle->reg_no ?? '-' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <h6 class="fw-bold">Work Status</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Issue / Fault</th>
                                <th>Inventory</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inspection->gmWorkStatuses as $status)
                                @php
                                    $gi = $status->issueInventory->garageInbuildIssue ?? null;
                                    $issueName = $gi && $gi->issue ? $gi->issue->name : null;
                                    $faultName = $gi && $gi->fault ? $gi->fault->name : null;
                                    $issueFault = collect([$issueName, $faultName])->filter()->implode(' / ');

                                    // Determine badge color
                                    $badgeClass = match(strtolower($status->status)) {
                                        'work_done' => 'bg-success',
                                        'in_progress' => 'bg-secondary',
                                        default => 'bg-info'
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $issueFault ?: '-' }}</td>
                                    <td>{{ $status->issueInventory->inventory->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $status->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
