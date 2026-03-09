<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GRN - {{ $supplierName ?? 'Supplier' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: 'Cambria', serif;
            font-size: 18px; 
            margin: 0;
            padding: 20px;
        }

        h3 { text-align: center; margin-bottom: 20px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }

        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }

        /* Signatures table */
        .signatures-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
            border: none;
        }

        .signatures-table td {
            width: 45%;
            text-align: center;
            border: none;
        }

        .signatures-table td p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($vehicle->company->logo))
            <img src="{{ public_path($vehicle->company->logo) }}" alt="Company Logo" style="max-width: 80px; height:auto;">
        @else 
            <span class="text-muted">No Logo</span>
        @endif
        <h3>Goods Received Note (GRN)</h3>
    </div>

    <table>
        <tr>
            <td><strong>GRN No:</strong></td>
            <td>{{ $grnNo }}</td>
            <td><strong>Date:</strong></td>
            <td>{{ $date }}</td>
        </tr>
        <tr>
            <td><strong>Supplier:</strong></td>
            <td colspan="3">{{ $supplierName }}</td>
        </tr>
        <tr>
            <td><strong>Received Date:</strong></td>
            <td colspan="3">{{ $receivedDate }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item Code</th>
                <th>Description</th>
                <th>Qty Ordered</th>
                <th>Qty Received</th>
                <th>Total (LKR)</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grns as $grn)
                <tr>
                    <td>00{{ $grn->procurement->issueInventory->inventory->id ?? '-' }}</td>
                    <td>{{ $grn->procurement->issueInventory->inventory->name ?? '-' }}</td>
                    <td>{{ $grn->procurement->fulfilled_qty ?? 0 }}</td>
                    <td>{{ $grn->received_qty ?? 0 }}</td>
                    <td>{{ $grn->procurement->price ?? 0 }}</td>
                    <td>{{ $grn->remark ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures-table">
        <tr>
            <td>
                <p>_________________________</p>
                <p><strong>Checked By</strong></p>
            </td>
            <td>
                <p>_________________________</p>
                <p><strong>Received By</strong></p>
            </td>
        </tr>
    </table>
    
</body>
</html>
