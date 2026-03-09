@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Edit Vehicle</h2>
        <a href="{{ route('vehicles.index') }}" class="btn btn-primary">Back to List</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="reg_no" class="form-label fw-bold">Registration Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="reg_no" name="reg_no" value="{{ old('reg_no', $vehicle->reg_no) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="vehicle_type_id" class="form-label fw-bold">Vehicle Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vehicle_type_id" name="vehicle_type_id" required>
                                        <option value="">Select Type</option>
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="vehicle_category_id" class="form-label fw-bold">Vehicle Category <span class="text-danger">*</span></label>
                                    <select class="select2 form-select" id="vehicle_category_id" name="vehicle_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($vehicleCategories as $category)
                                            <option value="{{ $category->id }}" data-type="{{ $category->vehicle_type_id }}"
                                                {{ old('vehicle_category_id', $vehicle->vehicle_category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Make <span class="text-danger">*</span></label>
                                        <select name="make" id="make" class="form-control select2 @error('make') is-invalid @enderror" required>
                                            <option value="">-- Select Make --</option>
                                            @foreach($vehicleAttributes->pluck('make')->unique() as $make)
                                                <option value="{{ $make }}" 
                                                    {{ (isset($vehicle) && $vehicle->make == $make) || old('make') == $make ? 'selected' : '' }}>
                                                    {{ $make }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('make')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Model <span class="text-danger">*</span></label>
                                        <select name="model" id="model" class="form-control select2 @error('model') is-invalid @enderror" required>
                                            <option value="">-- Select Model --</option>
                                            @foreach($vehicleAttributes->pluck('model')->unique() as $model)
                                                <option value="{{ $model }}" 
                                                    {{ (isset($vehicle) && $vehicle->model == $model) || old('model') == $model ? 'selected' : '' }}>
                                                    {{ $model }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="year_of_manufacture" class="form-label fw-bold">Year of Manufacture</label>
                                        <input type="number" class="form-control" id="year_of_manufacture" name="year_of_manufacture" value="{{ old('year_of_manufacture', $vehicle->year_of_manufacture) }}" min="1900" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label fw-bold">Color</label>
                                        <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $vehicle->color) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Technical Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vin" class="form-label fw-bold">VIN</label>
                                        <input type="text" class="form-control" id="vin" name="vin" value="{{ old('vin', $vehicle->vin) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="engine_no" class="form-label fw-bold">Engine Number</label>
                                        <input type="text" class="form-control" id="engine_no" name="engine_no" value="{{ old('engine_no', $vehicle->engine_no) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fuel_type_id" class="form-label fw-bold">Fuel Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="fuel_type_id" name="fuel_type_id" required>
                                            <option value="">Select Fuel Type</option>
                                            @foreach($fuelTypes as $type)
                                                <option value="{{ $type->id }}" {{ old('fuel_type_id', $vehicle->fuel_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->fuel_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="transmission_id" class="form-label fw-bold">Transmission <span class="text-danger">*</span></label>
                                        <select class="form-select" id="transmission_id" name="transmission_id" required>
                                            <option value="">Select Transmission</option>
                                            @foreach($transmissions as $transmission)
                                                <option value="{{ $transmission->id }}" {{ old('transmission_id', $vehicle->transmission_id) == $transmission->id ? 'selected' : '' }}>
                                                    {{ $transmission->transmission_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="seating_capacity" class="form-label fw-bold">Seating Capacity</label>
                                        <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" min="1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="odometer_at_registration" class="form-label fw-bold">Odometer Reading</label>
                                        <input type="number" class="form-control" id="odometer_at_registration" name="odometer_at_registration" value="{{ old('odometer_at_registration', $vehicle->odometer_at_registration) }}" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ownership Information -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Ownership Information</h5>
                            </div>
                            <div class="card-body">
                                <!-- Ownership Information -->
                                <div class="col-md-12">
                                    <div class="card h-100">
                                        <div class="card-header bg-secondary text-white">
                                            <h5 class="card-title mb-0">Ownership Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="ownership_type_id" class="form-label fw-bold">Ownership Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="ownership_type_id" name="ownership_type_id" required>
                                                    <option value="">Select Ownership Type</option>
                                                    @foreach($ownershipTypes as $type)
                                                        <option value="{{ $type->id }}" {{ old('ownership_type_id', $vehicle->ownership_type_id) == $type->id ? 'selected' : '' }}>
                                                            {{ $type->ownership_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Company (conditional) -->
                                            <div class="col-md-6 mb-3 company-field">
                                                <label for="company_id" class="form-label fw-bold">Company</label>
                                                <select id="company_id" name="company_id" class="form-select">
                                                    <option value="">-- Select Company --</option>
                                                    @foreach($companies as $company)
                                                        <option value="{{ $company->id }}" {{ old('company_id', $vehicle->company_id) == $company->id ? 'selected' : '' }}>
                                                            {{ $company->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="owner_name" class="form-label fw-bold">Owner Name</label>
                                                <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ old('owner_name', $vehicle->owner_name) }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="owner_phone" class="form-label fw-bold">Owner Contact</label>
                                                <input type="text" class="form-control" id="owner_phone" name="owner_phone" value="{{ old('owner_phone', $vehicle->owner_phone) }}">
                                            </div>

                                            <!-- Lease fields (conditional) -->
                                            <div class="row lease-fields" style="display: none;">
                                                <div class="col-md-6 mb-3">
                                                    <label for="lease_start" class="form-label fw-bold">Lease Start Date</label>
                                                    <input type="date" class="form-control" id="lease_start" name="lease_start" value="{{ old('lease_start', $vehicle->lease_start) }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="lease_end" class="form-label fw-bold">Lease End Date</label>
                                                    <input type="date" class="form-control" id="lease_end" name="lease_end" value="{{ old('lease_end', $vehicle->lease_end) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Financial Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="purchase_price" class="form-label fw-bold">Purchase Price</label>
                                        <input type="number" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price) }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="purchase_date" class="form-label fw-bold">Purchase Date</label>
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $vehicle->purchase_date) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="depreciation_rate" class="form-label fw-bold">Depreciation Rate (%)</label>
                                        <input type="number" class="form-control" id="depreciation_rate" name="depreciation_rate" value="{{ old('depreciation_rate', $vehicle->depreciation_rate) }}" step="0.01" min="0" max="100">
                                    </div>
                                    {{-- <div class="col-md-6 mb-3">
                                        <label for="current_value" class="form-label fw-bold">Current Value</label>
                                        <input type="number" class="form-control" id="current_value" name="current_value" value="{{ old('current_value', $vehicle->current_value) }}" step="0.01" min="0">
                                    </div> --}}
                                </div>

                                {{-- <div class="mb-3">
                                    <label for="loan_emi_details" class="form-label fw-bold">Loan/EMI Details</label>
                                    <textarea class="form-control" id="loan_emi_details" name="loan_emi_details" rows="2">{{ old('loan_emi_details', $vehicle->loan_emi_details) }}</textarea>
                                </div> --}}
                            </div>
                        </div>
                    </div>

                    <!-- Document Information -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Document Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Insurance Provider <span class="text-danger">*</span></label>
                                        <select name="insurance_provider" class="form-select select2" required>
                                            <option value="">-- Select Provider --</option>
                                            @php
                                                $providers = ['Union Assurance PLC','Softlogic Life Insurance PLC','Sri Lanka Insurance Corporation','Ceylinco Insurance PLC','AIA Insurance Lanka Limited','Amãna Takaful PLC','Allianz Insurance Lanka Ltd','Fairfirst Insurance Limited','Janashakthi Insurance PLC','Co-operative Insurance PLC','Continental Insurance Lanka Ltd','Orient Insurance Ltd','HNB Assurance PLC','HNB General Insurance','Life Insurance Corporation (Lanka) Ltd','MBSL Insurance Co. Ltd',];
                                            @endphp

                                            @foreach($providers as $provider)
                                                <option value="{{ $provider }}"
                                                    {{ (old('insurance_provider', $vehicle->insurance_provider ?? '') === $provider) ? 'selected' : '' }}>
                                                    {{ $provider }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="insurance_policy_no" class="form-label fw-bold">Insurance Policy Number</label>
                                        <input type="text" class="form-control" id="insurance_policy_no" name="insurance_policy_no" value="{{ old('insurance_policy_no', $vehicle->insurance_policy_no) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="insurance_expiry" class="form-label fw-bold">Insurance Expiry Date</label>
                                        <input type="date" class="form-control" id="insurance_expiry" name="insurance_expiry" value="{{ old('insurance_expiry', $vehicle->insurance_expiry) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="emission_test_expiry" class="form-label fw-bold">Emission Test Expiry</label>
                                        <input type="date" class="form-control" id="emission_test_expiry" name="emission_test_expiry" value="{{ old('emission_test_expiry', $vehicle->emission_test_expiry) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="revenue_license_expiry" class="form-label fw-bold">Revenue License Expiry</label>
                                        <input type="date" class="form-control" id="revenue_license_expiry" name="revenue_license_expiry" value="{{ old('revenue_license_expiry', $vehicle->revenue_license_expiry) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Files -->
                    <div class="row mt-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Revenue License</label>
                            <input type="file" name="revenue_license_file" class="form-control">
                            @if($vehicle->revenue_license_file)
                                <a href="{{ $vehicle->revenue_license_file }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Insurance</label>
                            <input type="file" name="insurance_file" class="form-control">
                            @if($vehicle->insurance_file)
                                <a href="{{ $vehicle->insurance_file }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Emission Test</label>
                            <input type="file" name="emission_test_file" class="form-control">
                            @if($vehicle->emission_test_file)
                                <a href="{{ $vehicle->emission_test_file }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Other Document</label>
                            <input type="file" name="other_doc_file" class="form-control">
                            @if($vehicle->other_doc_file)
                                <a href="{{ $vehicle->other_doc_file }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Images -->
                    <div class="row mt-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Front Image</label>
                            <input type="file" name="vehicle_front" class="form-control">
                            @if($vehicle->vehicle_front)
                                <img src="{{ $vehicle->vehicle_front }}" class="img-fluid mt-1 rounded" alt="Front">
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Back Image</label>
                            <input type="file" name="vehicle_back" class="form-control">
                            @if($vehicle->vehicle_back)
                                <img src="{{ $vehicle->vehicle_back }}" class="img-fluid mt-1 rounded" alt="Back">
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Left Image</label>
                            <input type="file" name="vehicle_left" class="form-control">
                            @if($vehicle->vehicle_left)
                                <img src="{{ $vehicle->vehicle_left }}" class="img-fluid mt-1 rounded" alt="Left">
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Right Image</label>
                            <input type="file" name="vehicle_right" class="form-control">
                            @if($vehicle->vehicle_right)
                                <img src="{{ $vehicle->vehicle_right }}" class="img-fluid mt-1 rounded" alt="Right">
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="4" placeholder="Enter remarks...">{{ old('remarks', $vehicle->remarks) }}</textarea>
                    </div>
                </div>

                <div class="mt-5 mb-4 d-flex justify-content-end">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary ms-2">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ownershipSelect = document.querySelector("select[name='ownership_type_id']");
        const leaseFields = document.querySelectorAll(".lease-fields");
        const financialFields = document.querySelectorAll(
            "input[name='purchase_price'], input[name='purchase_date'], input[name='depreciation_rate']"
        );

        function toggleFields() {
            const selectedText = ownershipSelect.options[ownershipSelect.selectedIndex]?.text.toLowerCase();

            // Lease fields
            if (selectedText.includes("lease")) {
                leaseFields.forEach(el => el.style.display = "flex");
            } else {
                leaseFields.forEach(el => el.style.display = "none");
            }

            // Financial fields
            if (selectedText.includes("rented")) {
                financialFields.forEach(f => f.closest('.mb-3').style.display = "none");
            } else {
                financialFields.forEach(f => f.closest('.mb-3').style.display = "block");
            }
        }

        // Run on load and on change
        toggleFields();
        ownershipSelect.addEventListener("change", toggleFields);
    });
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Search...",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const vehicleType = document.getElementById('vehicle_type_id');
        const vehicleCategory = document.getElementById('vehicle_category_id');
        const allCategories = Array.from(vehicleCategory.options);
        const savedCategoryId = "{{ old('vehicle_category_id', $vehicle->vehicle_category_id ?? '') }}";

        function filterCategories() {
            const selectedType = vehicleType.value;
            vehicleCategory.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = 'Select Category';
            vehicleCategory.appendChild(defaultOption);

            allCategories.forEach(option => {
                if(option.dataset.type === selectedType) {
                    vehicleCategory.appendChild(option);
                }
            });

            const selectedOption = Array.from(vehicleCategory.options).find(opt => opt.value === savedCategoryId);
            if (selectedOption) {
                vehicleCategory.value = savedCategoryId;
            } else {
                vehicleCategory.value = ''; 
            }
        }

        filterCategories();

        vehicleType.addEventListener('change', function() {
            filterCategories();
        });
    });
</script>

@endpush

@endsection
