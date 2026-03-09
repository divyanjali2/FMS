<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PO - {{ $supplier->name }}</title>
    <style>
        @page {
            size: A4 landscape; 
            margin: 10mm;
        }

        body {
            font-family: 'Cambria', serif;
            font-size: 14px; 
            margin: 0;
            line-height: 1.6;
            color: #333;
        }

        h2, h4, h5 { margin: 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-end { text-align: right; }

        .header, .company-info, .footer { margin-bottom: 15px; }

        .header-company {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .header-company div {
            width: 48%;
        }

        .company-info {
            margin-top: 10px;
        }

        .footer .signature {
            margin-top: 40px;
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .footer .signature div {
            border-top: 1px solid #000;
            margin-top: 25px;
        }

        p.generated {
            text-align: center;
            font-size: 12px;
            color: gray;
            margin-top: 20px;
        }

        .logo-container {
            position: absolute;
            top: 8mm;
            right: 8mm;
        }

        .logo-container img {
            max-width: 100px;
            height: auto;
            transition: all 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.1);
        }

        tbody tr:hover {
            background-color: #f9f9f9;
            cursor: pointer;
        }

        .header, .footer {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .footer p {
            font-weight: bold;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .header-company {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-company div {
                width: 100%;
                margin-bottom: 10px;
            }

            .logo-container {
                position: static;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <div class="header-company">
        <div class="header">
            <h2>Purchase Order</h2>
            <p><strong>Supplier:</strong> {{ $supplier->name }}</p>
            <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
            <p><strong>PO #:</strong> {{ sprintf('%05d', $inspection->id) }}</p>
        </div>

        <div class="company-info">
            <p><strong>Company:</strong> Rent A Car</p>
            <p><strong>Address:</strong> 371/5 Negombo Rd, Seeduwa</p>
            <p><strong>Phone:</strong> +94 114 941 650</p>
            <p><strong>Email:</strong> account@explorevacations.lk</p>
        </div>
    </div>

    <!-- Logo positioned at the top right -->
    <div class="logo-container">
        @if(!empty($vehicle->company->logo))
            <img src="{{ public_path($vehicle->company->logo) }}" alt="Company Logo">
        @else 
            <span class="text-muted">No Logo</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Inventory</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Payment Type</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPrice = 0; @endphp
            @foreach($procurements as $p)
                @php
                    $review = $p->accountantReview ?? null;
                    $totalPrice += $p->price ?? 0;
                @endphp
                <tr>
                    <td>{{ optional($p->issueInventory->inventory)->name ?? '-' }}</td>
                    <td>{{ $p->fulfilled_qty ?? '-' }}</td>
                    <td class="text-end">{{ number_format($p->price ?? 0, 2) }}</td>
                    <td>{{ ucfirst($review->types ?? '-') }}</td>
                    <td>{{ $p->remark ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-end" colspan="2">Total:</th>
                <th class="text-end">{{ number_format($totalPrice, 2) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Prepared By</p>
            <div></div>
        </div>
        <div class="signature" style="float: right;">
            <p>Authorized By</p>
            <div></div>
        </div>
    </div>

    <p class="generated">
        Generated on {{ now()->format('d M Y, H:i') }}
    </p>

</body>
</html>
