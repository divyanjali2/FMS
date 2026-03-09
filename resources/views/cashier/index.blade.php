@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold text-primary">
        💼 Rental Payments
    </h2>

    <div class="mb-3 d-flex justify-content-end">
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addRentalModal">
            <i class="bi bi-plus-circle me-1"></i> Add New Rental
        </button>
    </div>

    @include('cashier.create')
    @php
        use Carbon\Carbon;

        $today = Carbon::today();

        $cashiers = $cashiers->map(function($c) use ($today) {
            $dueDay = $c->due_day;

            // Create a date in the current month
            $dueDate = $today->copy()->day($dueDay);

            // If that date is in the past, push to next month
            if ($dueDate->lt($today)) {
                $dueDate = $dueDate->addMonth();
            }

            $c->due_date_obj = $dueDate;
            return $c;
        });

        $upcoming = $cashiers->filter(fn($c) => 
            $c->due_date_obj->between($today, $today->copy()->addDays(3))
        );

        $others = $cashiers->diff($upcoming);

        function ordinal($number) {
            if (!in_array(($number % 100), [11,12,13])) {
                switch ($number % 10) {
                    case 1: return $number.'st';
                    case 2: return $number.'nd';
                    case 3: return $number.'rd';
                }
            }
            return $number.'th';
        }
    @endphp

    {{-- Upcoming Rentals --}}
    <div class="card mb-4 shadow-sm p-4 pt-0">
        <h4 class="my-4 text-danger fw-bold">📅 Rentals Due Within 3 Days</h4>
        <div class="table-responsive mb-5 shadow-sm rounded">
            <table class="table table-bordered table-hover align-middle" id="upcomingRentalsTable">
                <thead class="table-danger text-center">
                    <tr>
                        <th>🚗 Vehicle</th>
                        <th>📅 Due Day</th>
                        <th>💰 Amount</th>
                        <th>⚡ Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcoming as $cashier)
                        <tr>
                            <td class="fw-semibold">{{ $cashier->vehicle->reg_no }}</td>
                            <td>
                                <span class="badge 
                                    {{ $cashier->due_day == now()->day ? 'bg-danger' : 'bg-info text-dark' }} fs-6">
                                    {{ ordinal($cashier->due_day) }} on every month
                                </span>
                            </td>
                            <td class="fw-bold text-success">Rs. {{ number_format($cashier->amount, 2) }}</td>
                            <td>
                                <form action="{{ route('cashier.sendToFM', $cashier->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                        class="btn btn-warning btn-sm shadow-sm" 
                                        {{ $cashier->status === 'send_to_fm' ? 'disabled' : '' }}>
                                        <i class="bi bi-send-fill me-1"></i>
                                        {{ $cashier->status === 'send_to_fm' ? 'Sent to FM' : 'Send to FM' }}
                                    </button>
                                </form>
                                @if($cashier->status !== 'send_to_fm')
                                    <a href="{{ route('cashier.edit', $cashier->id) }}" class="btn btn-info btn-sm shadow-sm ms-1">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="4" class="text-center text-muted">✅ No rentals due this week.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Other Rentals --}}
    <div class="mt-5 card mb-4 shadow-sm p-4 pt-0">
        <h4 class="my-4 text-secondary fw-bold">📂 Other Rentals</h4>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-striped table-hover align-middle" id="otherRentalsTable">
                <thead class="table-secondary text-center">
                    <tr>
                        <th>🚗 Vehicle</th>
                        <th>📅 Due Day</th>
                        <th>💰 Amount</th>
                        <th>⚡ Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($others as $cashier)
                        <tr>
                            <td class="fw-semibold">{{ $cashier->vehicle->reg_no }}</td>
                            <td>
                                <span class="badge 
                                    {{ $cashier->due_day == now()->day ? 'bg-danger' : 'bg-info text-dark' }} fs-6">
                                    {{ ordinal($cashier->due_day) }} on every month
                                </span>
                            </td>
                            <td class="fw-bold text-primary">Rs. {{ number_format($cashier->amount, 2) }}</td>
                            <td>
                                <form action="{{ route('cashier.sendToFM', $cashier->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                        class="btn btn-warning btn-sm shadow-sm" 
                                        {{ $cashier->status === 'send_to_fm' ? 'disabled' : '' }}>
                                        <i class="bi bi-send-fill me-1"></i>
                                        {{ $cashier->status === 'send_to_fm' ? 'Sent to FM' : 'Send to FM' }}
                                    </button>
                                </form>
                                @if($cashier->status !== 'send_to_fm')
                                    <a href="{{ route('cashier.edit', $cashier->id) }}" class="btn btn-info btn-sm shadow-sm ms-1">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="4" class="text-center text-muted">No other rentals found.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- <div class="mt-3 d-flex justify-content-center">
        {{ $cashiers->links('pagination::bootstrap-5') }}
    </div> --}}

    {{-- Approvals from FM --}}
    <div class="mt-5 card mb-4 shadow-sm p-4 pt-0">
        <h4 class="my-4 text-success fw-bold">
            ✅ Approvals from FM
        </h4>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-hover" id="approvalsTable">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Cashier Amount</th>
                        <th>FM Amount</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvals as $approval)
                        @php
                            $vehicle = $approval->cashier->vehicle ?? null;
                            $cashierAmount = $approval->cashier->amount;
                            $totalPayment = $approval->total_price; 
                            $sum = $cashierAmount - $totalPayment;
                            $isApproved = $approval->status === 'approved';
                        @endphp
                        <tr>
                            <td>{{ $vehicle->reg_no ?? '-' }}</td>
                            <td>Rs. {{ number_format($cashierAmount, 2) }}</td>
                            <td>Rs. {{ number_format($sum, 2) }}</td>
                            <td>Rs. {{ number_format($totalPayment, 2) }}</td>
                            <td>
                                <form action="{{ route('cashier.payment.approve', $approval->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"
                                        {{ $isApproved ? 'disabled' : '' }}>
                                        {{ $isApproved ? 'Approved' : 'Approve' }}
                                    </button>
                                </form>

                                @if($approval->status === 'approved')
                                    <a href="{{ route('cashier.viewBill', $approval->id) }}" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-eye"></i> View Bill
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- <div class="mt-3 d-flex justify-content-center">
        {{ $approvals->links('pagination::bootstrap-5') }}
    </div> --}}
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#upcomingRentalsTable').DataTable({
            "order": [[1, "asc"]] // default sort by Due Day column
        });

        $('#otherRentalsTable').DataTable({
            "order": [[1, "asc"]]
        });

        $('#approvalsTable').DataTable({
            "order": [[0, "asc"]] // default sort by Vehicle
        });
    });
</script>
@endpush
@endsection


