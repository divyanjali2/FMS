@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            Fleet Decision Details – Job {{ $garageReport->inspection->job_code }}
        </div>
        <div>
            <span class="badge bg-success">Sent to Garage</span>
        </div>
    </div>
    <div class="card-body">
        <h5 class="fw-bold mb-4">Vehicle: {{ $garageReport->inspection->vehicle->reg_no }}</h5>

        {{-- Fleet Reported Issues --}}
        <h6 class="mt-3">Fleet Reported Issues</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Fault</th>
                    <th>Decision</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                @foreach($garageReport->inspection->faults as $fault)
                    @php
                        $decision = $fleetDecisions[$fault->id] ?? 'Not decided';
                        $supplier = \App\Models\Supplier::find(
                            \App\Models\FleetDecision::where('garage_report_id', $garageReport->id)
                                ->where('fault_id', $fault->id)
                                ->where('type', 'fleet')
                                ->value('supplier_id')
                        );
                    @endphp
                    <tr>
                        <td>{{ $fault->name }}</td>
                        <td>
                            <input type="text" class="form-control" value="{{ ucfirst($decision) }}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ $supplier->name ?? '-' }}" readonly>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Garage Identified Issues --}}
        <h6 class="mt-4">Garage Identified Issues</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Decision</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                @forelse($garageReport->inspection->garageReports as $report)
                    @if($report->issue)
                        @php
                            $decision = $garageDecisions[$report->issue->id] ?? 'Not decided';
                            $supplier = \App\Models\Supplier::find(
                                \App\Models\FleetDecision::where('garage_report_id', $garageReport->id)
                                    ->where('issue_id', $report->issue->id)
                                    ->where('type', 'garage')
                                    ->value('supplier_id')
                            );
                        @endphp
                        <tr>
                            <td>{{ $report->issue->name }}</td>
                            <td>
                                <input type="text" class="form-control" value="{{ ucfirst($decision) }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $supplier->name ?? '-' }}" readonly>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="3" class="text-muted">No garage issues reported.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            <a href="{{ route('fleet-decisions.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection
