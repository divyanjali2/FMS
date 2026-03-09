@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            {{ $garageReport->status === 'sent_to_fleet' ? 'Fleet Review' : 'Fleet Decision Details' }} - Job {{ $garageReport->inspection->job_code }}
        </div>
        <div>
            @php
                $badgeClass = match($garageReport->status) {
                    'sent_to_fleet' => 'bg-warning',
                    'sent_to_garage' => 'bg-success',
                    default => 'bg-secondary'
                };
                $statusText = match($garageReport->status) {
                    'sent_to_fleet' => 'Pending Review',
                    'sent_to_garage' => 'Sent to Garage',
                    default => $garageReport->status
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
        </div>
    </div>
    <div class="card-body">
        <h5>Vehicle: {{ $garageReport->inspection->vehicle->reg_no }}</h5>

        <form action="{{ route('fleet-decisions.store', $garageReport->id) }}" method="POST">
            @csrf

            {{-- Fleet Issues --}}
            <h6 class="mt-3">Fleet Reported Issues</h6>
            <table class="table table-bordered fleet-table">
                <thead>
                    <tr>
                        <th>Fault</th>
                        <th>Decision</th>
                        <th class="supplier-col d-none">Suppliers</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($garageReport->inspection->faults as $fault)
                        <tr>
                            <td>
                                {{ $fault->name }}

                                @php
                                    $status = $fault->pivot->status ?? null;
                                    $isFuel = strtoupper($fault->name) === 'FUEL';
                                @endphp

                                @if($status !== null && $status !== '')
                                    <small class="text-muted">
                                        (
                                        @if($isFuel && is_numeric($status))
                                            {{ $status }}%
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        @endif
                                        )
                                    </small>
                                @endif
                            </td>                       
                            <td>
                                <select name="fleet_decisions[{{ $fault->id }}][decision]" class="form-select decision-select">
                                    <option value="inbuild">Inbuild</option>
                                    <option value="outsource">Outsource</option>
                                </select>
                            </td>
                            <td class="supplier-cell d-none">
                                <select name="fleet_decisions[{{ $fault->id }}][supplier_id]" class="form-select">
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($fault->category->suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Garage Issues --}}
            <h6 class="mt-4">Garage Identified Issues</h6>
            <table class="table table-bordered garage-table">
                <thead>
                    <tr>
                        <th>Issue</th>
                        <th>Decision</th>
                        <th class="supplier-col d-none">Suppliers</th>
                        <th>Images</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($garageReport->inspection->garageReports as $report)
                        <tr>
                            <td>
                                @if($report->issue)
                                    {{ $report->issue->name }}
                                    @if($report->notes)
                                        <span class="text-muted ms-2">
                                            ({{ ucfirst(str_replace('_',' ',$report->notes)) }})
                                        </span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>                            
                            <td>
                                @if($report->issue)
                                    <select name="garage_decisions[{{ $report->issue->id }}][decision]" class="form-select decision-select">
                                        <option value="inbuild">Inbuild</option>
                                        <option value="outsource">Outsource</option>
                                    </select>
                                @else
                                    <span class="text-muted">No issue assigned</span>
                                @endif
                            </td>
                            <td class="supplier-cell d-none">
                                @if($report->issue && $report->issue->category)
                                    <select name="garage_decisions[{{ $report->issue->id }}][supplier_id]" class="form-select">
                                        <option value="">-- Select Supplier --</option>
                                        @foreach($report->issue->category->suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="text-muted">No suppliers available</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $imgs = is_array($report->images) ? $report->images : json_decode($report->images, true);
                                    $imgs = $imgs ?: [];
                                @endphp

                                @if(count($imgs))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($imgs as $img)
                                            <a href="{{ $img }}" target="_blank">
                                                <img src="{{ $img }}"
                                                    class="rounded border"
                                                    style="width:60px;height:60px;object-fit:cover;">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('fleet-decisions.index') }}" class="btn btn-secondary">
                    Back
                </a>
                <button type="submit" class="btn btn-success">
                    Save Decisions
                </button>

            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fleet-table, .garage-table').forEach(table => {
            const update = () => {
                let anyVisible = false;
                table.querySelectorAll('tbody tr').forEach(row => {
                    const select = row.querySelector('.decision-select');
                    const supplierCell = row.querySelector('.supplier-cell');
                    const show = select.value === 'outsource';
                    supplierCell.classList.toggle('d-none', !show);
                    if (show) anyVisible = true;
                });
                table.querySelector('.supplier-col').classList.toggle('d-none', !anyVisible);
            };
            table.addEventListener('change', e => {
                if (e.target.classList.contains('decision-select')) update();
            });
            update();
        });
    });
</script>
@endsection
