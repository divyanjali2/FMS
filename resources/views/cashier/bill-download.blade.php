<!DOCTYPE html>
<html>
<head>
    <title>Rental Bill</title>
    <style>
        @page { size: A4 portrait; margin: 6mm; }
        body { font-family: 'Cambria', serif; font-size: 12px; margin:0; padding:0; }
        .container { padding: 5px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 2px; }
        .mb-2 { margin-bottom: 4px; }
        .mb-3 { margin-bottom: 6px; }
        .mb-4 { margin-bottom: 10px; }
        .mt-2 { margin-top: 5px; }
        .mt-3 { margin-top: 8px; }
        .d-flex { display: flex; justify-content: space-between; align-items: center; }
        .table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 11px; }
        .table, th, td { border: 1px solid black; }
        th, td { padding: 4px; text-align: left; }
        .text-decoration-underline { text-decoration: underline; }
        .signatures { display: flex; justify-content: space-between; margin-top: 20px; text-align: center; }
        .signatures div { width: 30%; }
        .footer { text-align: center; font-size: 10px; margin-top: 15px; }
        img { max-height: 100px; max-width: 100px; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="d-flex">
        <div style="position: relative; display: flex; align-items: center; height: 80px;">
            @if($approval->cashier->vehicle && $approval->cashier->vehicle->company->logo)
                <img src="{{$approval->cashier->vehicle->company->logo}}" alt="Logo" style="height:80px; width:80px; margin-right:10px;">
            @endif

            <!-- Company Name centered absolutely -->
            <h3 class="fw-bold mb-0" style="position: absolute; left: 50%; transform: translateX(-50%); line-height:1; font-size:26px;">
                {{ $approval->cashier->vehicle->company->name ?? '' }}
            </h3>
        </div>
    </div>

    <!-- Vehicle Info + Date -->
    <div class="d-flex justify-content-between mb-2">
        <div style="flex:1"></div>
        <div style="flex:1; text-align:center;">
            <p class="fw-bold mb-0">{{ $approval->cashier->vehicle->reg_no ?? '' }}</p>
        </div>
        <div style="flex:1; text-align:right;">
            <p class="mb-0">{{ now()->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Rental Period -->
    <p class="fw-bold mb-1 text-decoration-underline">
        Monthly Rental: {{ \Carbon\Carbon::create(now()->year, now()->subMonth()->month, $approval->cashier->due_day)->format('jS F Y') }} 
        - {{ \Carbon\Carbon::create(now()->year, now()->month, $approval->cashier->due_day)->format('jS F Y') }}
    </p>

    @php
        use Carbon\Carbon;
        $startDate = Carbon::parse($approval->cashier->rental_agreement_start_date);
        $diff = $startDate->diff(Carbon::today());
        $months = $diff->m + ($diff->y * 12);
        $days = $diff->d;
        $ordinalMonth = $months . ['th','st','nd','rd','th','th','th','th','th','th'][$months % 10] ?? 'th';
    @endphp

    <p class="fw-bold mb-2 text-decoration-underline">
        Rental for {{ $ordinalMonth }} Month ({{ $months }} months {{ $days }} days)
    </p>

    <table style="width:100%; margin-top:10px; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="font-weight:bold; width:50%; border: none;">Total Rental</td>
            <td style="text-align:right; width:50%; border: none;">LKR {{ number_format($approval->cashier->amount,2) }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; border: none;">Expenses Deduction</td>
            <td style="text-align:right; border: none;">LKR {{ number_format($approval->cashier->amount - $approval->total_price,2) }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; border: none;">Balance</td>
            <td style="text-align:right; border: none;">LKR {{ number_format($approval->total_price ?? 0,2) }}</td>
        </tr>
    </table>

    <!-- Expenses Table -->
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

    <!-- Payment Info -->
    <div class="d-flex justify-content-between mb-2">
        <p>Bank: {{ $approval->cashier->bank_name ?? '-' }}</p>
        <p>AC Name: {{ $approval->cashier->account_name ?? '-' }}</p>
        <p>AC No: {{ $approval->cashier->account_number ?? '-' }}</p>
    </div>

    <!-- Signatures -->
    <table style="width: 100%; margin-top: 18px; border-collapse: collapse; text-align: center;" class="table-borderless">
        <tr>
            <td style="border: none;">....................<br>Prepared By</td>
            <td style="border: none;">....................<br>Checked By</td>
            <td style="border: none;">....................<br>Authorized By</td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>No.371-5, Negombo Road, Seeduwa, Sri Lanka</p>
        <p>Tel: 0094 11 4 941650 | Fax: 0094 11 2255 665 | Hotline: 0094 777 780729</p>
        <p>Email: info@srilankarentacar.com | Web: www.srilankarentacar.com</p>
    </div>
</div>
</body>
</html>
