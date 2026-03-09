@if(auth()->user()->hasPermission('manage_accountant'))
@extends('layouts.app')
@section('content')
<div class="container my-5">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Procurement Review</h2>
            <span class="text-muted">Inspection #{{ $inspection->id ?? '-' }}</span>
        </div>

        <div class="mb-4">
            <h5 class="mb-3 fw-bold">Vehicle & Job Details</h5>
            <p class="mb-0"><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>
            <p class="mb-0"><strong>Vehicle:</strong> {{ $vehicle->reg_no ?? '-' }}</p>
        </div>

        <form method="POST" action="{{ route('accountant.sendToProcurement', $inspection->id) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Inventory</th>
                            <th>Supplier</th>
                            <th class="text-end">Price</th>
                            <th>Remarks</th>
                            <th>Payment Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($procurements as $procurement)
                            <tr>
                                <td>{{ $procurement->issueInventory->inventory->name ?? '-' }}</td>
                                <td>{{ $procurement->supplier->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($procurement->price, 2) }}</td>
                                <td>{{ $procurement->remark }}</td>
                                <td>
                                    <select name="types[{{ $procurement->id }}]" class="form-select" required>
                                        <option value="" disabled selected>Select</option>
                                        <option value="cash">Cash</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total:</th>
                            <th class="text-end">{{ number_format($totalPrice, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('accountant.index') }}" class="btn btn-secondary btn-lg">Back</a>
                <button type="submit" class="btn btn-success btn-lg">Send to Procurement Office</button>
            </div>
        </form>

        <div class="text-center mt-4 text-muted">
            <small>Generated on {{ now()->format('d M Y, H:i') }}</small>
        </div>
    </div>
</div>
@endsection
@endif
