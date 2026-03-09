@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row">
        @foreach($grnsBySupplier as $supplierId => $supplierGrns)
            <div class="col-lg-6 mb-4">
                <div id="grn-{{ $supplierId }}" class="p-4 h-100 grn-card" 
                     style="background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        @if(!empty($vehicle->company->logo))
                            <img src="{{ asset($vehicle->company->logo) }}" alt="Company Logo" class="img-fluid rounded" style="max-width: 80px; height:auto;">
                        @else 
                            <span class="text-muted">No Logo</span>
                        @endif
                        <h4 class="fw-bold text-center flex-grow-1">Goods Received Note (GRN)</h4>
                    </div>

                    <!-- Info -->
                    <div class="table-responsive">
                        <table class="table table-borderless mb-4 small">
                            <tr>
                                <td><strong>GRN No:</strong></td>
                                <td>INS-{{ $inspectionId }}-SUP-{{ $supplierId }}</td>
                                <td><strong>Date:</strong></td>
                                <td>{{ now()->format('F d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Supplier:</strong></td>
                                <td>{{ $supplierGrns->first()->procurement->supplier->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Received Date:</strong></td>
                                <td>{{ $supplierGrns->first()->created_at->format('F d, Y') ?? '' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Items -->
                    <div class="table-responsive mt-5">
                        <table class="table table-bordered text-center small">
                            <thead class="table-secondary">
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
                                @foreach($supplierGrns as $grn)
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
                    </div>

                    <!-- Signatures -->
                    <div class="row mt-4">
                        <div class="col text-center">
                            <p>_________________________</p>
                            <p><strong>Checked By</strong></p>
                        </div>
                        <div class="col text-center">
                            <p>_________________________</p>
                            <p><strong>Received By</strong></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('procurements.grn.download', [$inspectionId, $supplierId]) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-download me-1"></i> Download GRN
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
