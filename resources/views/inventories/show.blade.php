@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Inventory Item Details</h4>
                <a href="{{ route('inventories.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Item Code</h6>
                        <p class="fw-bold">{{ $inventory->item_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Item Name</h6>
                        <p class="fw-bold">{{ $inventory->name }}</p>
                    </div>
                    <div class="col-md-12">
                        <h6 class="text-muted">Description</h6>
                        <p class="fw-normal">{{ $inventory->description }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Quantity</h6>
                        <p class="fw-bold">
                            <span class="badge bg-info text-dark">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Purchase Date</h6>
                        <p class="fw-bold">{{ \Carbon\Carbon::parse($inventory->purchase_date)->format('d M, Y') }}</p>
                    </div>
                    <div class="col-md-12">
                        <h6 class="text-muted">Supplier</h6>
                        <p class="fw-bold">{{ $inventory->supplier->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@endif
