@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm p-5" style="max-width: 900px; margin:auto; font-size:16px;">
        
        <!-- Header -->
        <div class="d-flex align-items-center">
            <div class="col-md-4">
                @if($approval->cashier->vehicle && $approval->cashier->vehicle->company->logo)
                    <img src="{{ asset($approval->cashier->vehicle->company->logo) }}" alt="Company Logo" height="100" width="100">
                @endif
            </div>
            <!-- Company Name -->
            <div class="text-center col-md-4">
                <h2 class="fw-bold mb-0">{{ $approval->cashier->vehicle->company->name ?? '' }}</h2>
            </div>
        </div>

        <!-- Vehicle Info + Date -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="col-3"></div>
            <div class="col-6 text-center">
                <p class="fw-bold mb-1">{{ $approval->cashier->vehicle->reg_no ?? '' }}</p>
            </div>
            <div class="col-3 text-end">
                <p class="mb-0">{{ now()->format('d F Y') }}</p>
            </div>
        </div>

        <!-- Vehicle & Period -->
        <div class="mb-4 mt-3">
            <p class="fw-bold mb-0 text-decoration-underline">
                Monthly Rental for the Period of 
                {{ \Carbon\Carbon::create(now()->year, now()->subMonth()->month, $approval->cashier->due_day)->format('jS F Y') }} 
                - 
                {{ \Carbon\Carbon::create(now()->year, now()->month, $approval->cashier->due_day)->format('jS F Y') }}
            </p>
        </div>

        <!-- Rental Info -->
        <div class="mb-2">
            @php
            use Carbon\Carbon;

            $startDate = Carbon::parse($approval->cashier->rental_agreement_start_date);
            // Current date
            $today = Carbon::today();

            $monthsDiff = ($today->year - $startDate->year) * 12 + ($today->month - $startDate->month);

            if ($today->day < $startDate->day) {
                $monthsDiff -= 1;
            }

            // Ensure minimum 0
            $monthsDiff = max(0, $monthsDiff);
            @endphp

            <p class="fw-bold text-decoration-underline mb-2">
                Rental for the {{ ordinal($monthsDiff + 1) }} Month
            </p>

            <div class="d-flex justify-content-between">
                <p>{{ \Carbon\Carbon::create(now()->year, now()->subMonth()->month, $approval->cashier->due_day)->format('jS F Y') }} 
                - 
                {{ \Carbon\Carbon::create(now()->year, now()->month, $approval->cashier->due_day)->format('jS F Y') }}</p>
                <p class="text-end"><strong>LKR {{ number_format($approval->cashier->amount,2) }}</strong></p>
            </div>
        </div>

        <!-- Deduction -->
        <div class="mb-4">
            <p class="fw-bold text-decoration-underline mb-1">Deduction</p>
            <div class="d-flex justify-content-between">
                <p>Expenses</p>
                <p>LKR {{ number_format($approval->cashier->amount - $approval->total_price,2) }}</p>
            </div>

            <div class="d-flex justify-content-between">
                <p>Balance Amount to be paid to {{ $approval->cashier->vehicle->owner_name ?? '-' }}</p>
                <p>LKR {{ number_format($approval->total_price ?? 0, 2) }}</p>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>PO ID</th>
                    <th>Inventory Name</th>
                    <th class="text-end">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $proc)
                    <tr>
                        <td>{{ $proc->po_id }}</td>
                        <td>{{ $proc->issueInventory->inventory->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($proc->price ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No outsourced procurements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($installments->count())
            <p class="fw-bold text-decoration-underline mt-4">Installments</p>
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th>Installment Id</th>
                        <th>Type</th>
                        <th class="text-end">Amounts (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installments as $installment)
                        @php
                            $amounts = is_array($installment->options) ? $installment->options : json_decode($installment->options, true);
                        @endphp
                        <tr>
                            <td>00{{ $installment->id }}</td>
                            <td>{{ ucfirst($installment->type) }}</td>
                            <td class="text-end">
                                @if($amounts)
                                    @if($installment->type === 'equal' && count(array_unique($amounts)) === 1)
                                        {{ number_format($amounts[0], 2) }} × {{ count($amounts) }}
                                        <br>
                                        <strong>Total:</strong> {{ number_format(array_sum($amounts), 2) }}
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($amounts as $i => $amt)
                                                <li>Installment {{ $i+1 }}: {{ number_format($amt, 2) }}</li>
                                            @endforeach
                                            <li><strong>Total:</strong> {{ number_format(array_sum($amounts), 2) }}</li>
                                        </ul>
                                    @endif
                                @else
                                    <span class="text-muted">No details</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Received Payment -->
        <div class="mb-4">
            <p class="mb-4">
                Received the {{ ordinal($approval->cashier->due_day) }} Payment for the month of {{ now()->subMonth()->format('F Y') }}
            </p>
            <p><strong>Bank:</strong> {{ $approval->cashier->bank_name ?? '-' }}</p>
            <p><strong>AC Name:</strong> {{ $approval->cashier->account_name ?? '-' }}</p>
            <p><strong>AC No:</strong> {{ $approval->cashier->account_number ?? '-' }}</p>
        </div>

        <!-- Signatures -->
        <div class="d-flex justify-content-between text-center mt-3">
            <div>
                <p>..........................</p>
                <p class="mb-0">Prepared By</p>
            </div>
            <div>
                <p>..........................</p>
                <p class="mb-0">Checked By</p>
            </div>
            <div>
                <p>..........................</p>
                <p class="mb-0">Authorized By</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4" style="font-size:13px;">
            <p class="mb-0">
                No.371-5, Negombo Road, Seeduwa, Sri Lanka
            </p>
            <p class="mb-0">
                Tel: 0094 11 4 941650 | Fax: 0094 11 2255 665 | Hotline: 0094 777 780729
            </p>
            <p class="mb-0">
                Email: info@srilankarentacar.com | Web: www.srilankarentacar.com
            </p>
        </div>
        <div class="text-end">
            <a href="{{ route('cashier.downloadBill', $approval->id) }}" class="btn btn-primary">
                <i class="bi bi-download"></i> Download PDF
            </a>
        </div>
    </div>
</div>
@endsection

@php
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
