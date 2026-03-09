@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h3 class="mb-4 fw-bold">General Manager - Inspections</h3>

    <div class="card shadow-sm mb-5">
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

    <h3 class="my-4 fw-bold">Installment Requests from FM</h6>
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installments as $installment)
                        <tr>
                            <td>#00{{ $installment->id }}</td>
                            <td>{{ ucfirst($installment->type) }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#installmentModal{{ $installment->id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No installment requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($installments as $installment)
        <div class="modal fade" id="installmentModal{{ $installment->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title fw-bold">
                            Installment Request #00{{ $installment->id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Type:</strong> {{ ucfirst($installment->type) }}</p>
                        @php
                            // Always ensure we get an array
                            $rawOptions = $installment->options;
                            $amounts = [];

                            if (is_string($rawOptions)) {
                                $decoded = json_decode($rawOptions, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $amounts = $decoded;
                                }
                            } elseif (is_array($rawOptions)) {
                                $amounts = $rawOptions;
                            }
                        @endphp

                        @if(count($amounts))
                            <ul>
                                <li><strong>Installments:</strong> {{ count($amounts) }} times</li>

                                @if($installment->type === 'equal' && count(array_unique($amounts)) === 1)
                                    <li><strong>Amount per Installment:</strong> {{ number_format($amounts[0], 2) }}</li>
                                @else
                                    <li><strong>Amounts:</strong></li>
                                    <ul>
                                        @foreach($amounts as $index => $amount)
                                            <li>Payment {{ $index+1 }}: {{ number_format($amount, 2) }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <hr>
                                <li><strong>Total Payable:</strong> {{ number_format(array_sum($amounts), 2) }}</li>
                            </ul>
                        @else
                            <span class="text-muted">No installment details available.</span>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        @if($installment->status !== 'paid')
                            <form method="POST" action="{{ route('installments.approve', $installment->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                        @else
                            <button class="btn btn-secondary" disabled>Approved</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

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



