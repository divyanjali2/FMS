@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row">
        @foreach($procurementsBySupplier as $supplierId => $procurements)
            @php
                $supplier = $procurements->first()->supplier;
                $totalPrice = $procurements->sum('price');
            @endphp

            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm p-3 h-100" id="po-{{ $supplierId }}">
                   <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        @if(!empty($vehicle->company->logo))
                            <img src="{{ asset($vehicle->company->logo) }}" alt="Company Logo" class="img-fluid rounded" style="max-width: 80px; height:auto;">
                        @else 
                            N/A
                        @endif                        
                        <div class="text-end">
                            <h5 class="fw-bold text-primary">Purchase Order</h5>
                            <small>PO #{{ sprintf('%05d', $inspection->id) }}</small><br>
                            <small>Date: {{ now()->format('d M Y') }}</small><br>
                        </div>
                    </div>
                    <hr>

                    <!-- Company Info / Supplier Info -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="mb-1">371/5 Negombo Rd, <br> Seeduwa </p>
                            <p class="mb-1">+94 114 941 650</p>
                            <p class="mb-0">account@explorevacations.lk</p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="fw-bold">Supplier</h6>
                            <p class="mb-1">{{ $supplier->name ?? '-' }}</p>
                            <p class="mb-1">{{ $supplier->address ?? '-' }}</p>
                            <p class="mb-0">{{ $supplier->phone ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Item Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Inventory</th>
                                    <th>Req. Quantity</th>
                                    <th>Price</th>
                                    <th>Payment Type</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procurements as $p)
                                    @php $review = $p->accountantReview ?? null; @endphp
                                    <tr>
                                        <td>{{ $p->issueInventory->inventory->name ?? '-' }}</td>
                                        <td>{{ $p->fulfilled_qty ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($p->price, 2) }}</td>
                                        <td>{{ ucfirst($review->types ?? '-') }}</td>
                                        <td>{{ $p->remark ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <th class="text-end" colspan="1">Total:</th>
                                    <th class="text-end">{{ number_format($totalPrice, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Footer Signatories -->
                    <div class="row mt-3">
                        <div class="col-6">
                            <p class="fw-bold">Prepared By</p>
                            <div style="border-top:1px solid #000; width: 80%; margin-top: 8%;"></div>
                        </div>
                        <div class="col-6 text-end">
                            <p class="fw-bold">Authorized By</p>
                            <div style="border-top:1px solid #000; width: 80%; margin-left:auto; margin-top: 8%;"></div>
                        </div>
                    </div>

                    <div class="text-center mt-2 text-muted">
                        <small>Generated on {{ now()->format('d M Y, H:i') }}</small>
                    </div>

                    <!-- Individual Print Button -->
                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('accountant.downloadPO', $supplierId) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-download me-1"></i> Download PO
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
