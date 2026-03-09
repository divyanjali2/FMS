@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold text-primary text-center">Edit Rental Payment</h2>

    <form action="{{ route('cashier.update', $cashier) }}" method="POST" class="bg-light p-4 rounded shadow-sm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Vehicle --}}
            <div class="col-md-6 mb-3">
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ $cashier->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->reg_no }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Due Date --}}
            <div class="col-md-6 mb-3">
                <label for="due_day" class="form-label">Due Day of Month</label>
                <select name="due_day" id="due_day" class="form-select" required>
                    @for($i=1; $i<=30; $i++)
                        <option value="{{ $i }}" {{ $cashier->due_day == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="d-flex row">
            <div class="col-md-6 mb-3">
                <label for="rental_agreement_start_date" class="form-label">Rental Agreement Start Date</label>
                <input type="date" name="rental_agreement_start_date" class="form-control" value="{{ $cashier->rental_agreement_start_date }}" required>
            </div>
    
            <div class="col-md-6 mb-3">
                <label for="rental_agreement_end_date" class="form-label">Rental Agreement End Date</label>
                <input type="date" name="rental_agreement_end_date" class="form-control" value="{{ $cashier->rental_agreement_end_date }}" required>
            </div>
        </div>

        <div class="row">
            {{-- Amount --}}
            <div class="col-md-6 mb-3">
                <label for="amount" class="form-label">Payment Amount</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0" value="{{ $cashier->amount }}" required>
            </div>

            {{-- Bank Name --}}
            <div class="col-md-6 mb-3">
                <label for="bank_name" class="form-label">Bank</label>
                <select name="bank_name" id="bank_name" class="form-select select2" required>
                    <option value="">Select Bank</option>
                    @php
                        $banks = [
                            "Bank of Ceylon (BOC)",
                            "Cargills Bank Ltd",
                            "Citibank N.A.",
                            "Commercial Bank of Ceylon PLC",
                            "Commercial Credit and Finance",
                            "DFCC Bank PLC",
                            "HDFC Bank",
                            "Hatton National Bank PLC (HNB)",
                            "Hongkong and Shanghai Banking Corporation (HSBC)",
                            "National Development Bank (NDB)",
                            "National Savings Bank (NSB)",
                            "Nations Trust Bank (NTB)",
                            "Pan Asia Bank PLC",
                            "People’s Bank",
                            "Sanasa Development Bank",
                            "Sampath Bank PLC",
                            "Seylan Bank PLC",
                            "Singer Finance",
                            "Softlogic Finance",
                            "Standard Chartered Bank",
                            "State Mortgage and Investment Bank (SMIB)",
                            "Union Bank of Colombo PLC",
                            "Vallibel Finance",
                            "Regional Development Bank (Pradeshiya Sanwardhana Bank)"
                        ];
                    @endphp
                    @foreach($banks as $bank)
                        <option value="{{ $bank }}" {{ $cashier->bank_name == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            {{-- Account Number --}}
            <div class="col-md-6 mb-3">
                <label for="account_number" class="form-label">Account Number</label>
                <input type="text" name="account_number" id="account_number" class="form-control" value="{{ $cashier->account_number }}" required>
            </div>

            {{-- Account Name --}}
            <div class="col-md-6 mb-3">
                <label for="account_name" class="form-label">Account Name</label>
                <input type="text" name="account_name" id="account_name" class="form-control" value="{{ $cashier->account_name }}" required>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('cashier.index') }}" class="btn btn-secondary me-3">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Rental</button>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({ width: '100%' });
    });
</script>

<style>
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .rounded {
        border-radius: 0.375rem !important;
    }

    .form-label {
        font-weight: 500;
    }
</style>

@endsection
