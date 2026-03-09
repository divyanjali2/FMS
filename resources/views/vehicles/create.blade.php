@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header text-white" style="background-color: #820000">
        <h4 class="mb-0 fw-bold">Add Vehicle</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($vehicle))
                @method('PUT')
            @endif

            <div class="row">
                <!-- Basic Info -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Registration Number <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center">
                        <input type="text" name="reg_no_part1" class="form-control me-2 @error('reg_no_part1') is-invalid @enderror" value="{{ old('reg_no_part1', isset($vehicle) ? explode('-', $vehicle->reg_no)[0] : '') }}" required style="max-width: 120px;">
                        <span class="fw-bold">-</span>
                        <input type="text" name="reg_no_part2" class="form-control ms-2 @error('reg_no_part2') is-invalid @enderror" value="{{ old('reg_no_part2', isset($vehicle) ? (explode('-', $vehicle->reg_no)[1] ?? '') : '') }}" required style="max-width: 150px;">
                    </div>
                    @error('reg_no_part1')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('reg_no_part2')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Type <span class="text-danger">*</span></label>
                    <select name="vehicle_type_id" id="vehicle-type" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                        <option value="">-- Select Type --</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}" 
                                {{ (old('vehicle_type_id', isset($vehicle) ? $vehicle->vehicle_type_id : '') == $type->id) ? 'selected':'' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Category <span class="text-danger">*</span></label>
                    <select name="vehicle_category_id" id="vehicle-category" class="form-select select2" required>
                        <option value="">-- Select Category --</option>
                        @foreach($vehicleCategories as $category)
                            <option value="{{ $category->id }}" data-type="{{ $category->vehicle_type_id }}"
                                {{ (old('vehicle_category_id', isset($vehicle) ? $vehicle->vehicle_category_id : '') == $category->id) ? 'selected':'' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

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

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Year of Manufacture <span class="text-danger">*</span></label>
                    <input type="number" name="year_of_manufacture" class="form-control" 
                           value="{{ $vehicle->year_of_manufacture ?? old('year_of_manufacture') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Color <span class="text-danger">*</span></label>
                    <input type="text" name="color" class="form-control" value="{{ $vehicle->color ?? old('color') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">VIN / Chassis No <span class="text-danger">*</span></label>
                    <input type="text" name="vin" class="form-control" value="{{ $vehicle->vin ?? old('vin') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Engine No <span class="text-danger">*</span></label>
                    <input type="text" name="engine_no" class="form-control" value="{{ $vehicle->engine_no ?? old('engine_no') }}" required>
                </div>

                <!-- Fuel / Transmission -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fuel Type <span class="text-danger">*</span></label>
                    <select name="fuel_type_id" class="form-select @error('fuel_type_id') is-invalid @enderror" required>
                        <option value="">-- Select Fuel --</option>
                        @foreach($fuelTypes as $fuel)
                            <option value="{{ $fuel->id }}" 
                                {{ (old('fuel_type_id', isset($vehicle) ? $vehicle->fuel_type_id : '') == $fuel->id) ? 'selected':'' }}>
                                {{ $fuel->fuel_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('fuel_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Transmission <span class="text-danger">*</span></label>
                    <select name="transmission_id" class="form-select @error('transmission_id') is-invalid @enderror" required>
                        <option value="">-- Select Transmission --</option>
                        @foreach($transmissions as $t)
                            <option value="{{ $t->id }}" 
                                {{ (old('transmission_id', isset($vehicle) ? $vehicle->transmission_id : '') == $t->id) ? 'selected':'' }}>
                                {{ $t->transmission_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('transmission_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Seating / Odometer -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Seating Capacity <span class="text-danger">*</span></label>
                    <input type="number" name="seating_capacity" class="form-control" value="{{ $vehicle->seating_capacity ?? old('seating_capacity') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Odometer (at registration) <span class="text-danger">*</span></label>
                    <input type="number" name="odometer_at_registration" class="form-control" value="{{ $vehicle->odometer_at_registration ?? old('odometer_at_registration') }}" required>
                </div>

                <!-- Ownership & Legal -->
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Ownership Type <span class="text-danger">*</span></label>
                    <select name="ownership_type_id" class="form-select @error('ownership_type_id') is-invalid @enderror" required>
                        <option value="">-- Select Ownership --</option>
                        @foreach($ownershipTypes as $o)
                            <option value="{{ $o->id }}" 
                                {{ (old('ownership_type_id', isset($vehicle) ? $vehicle->ownership_type_id : '') == $o->id) ? 'selected':'' }}>
                                {{ $o->ownership_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ownership_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Owner Name <span class="text-danger">*</span></label>
                    <input type="text" name="owner_name" class="form-control" value="{{ $vehicle->owner_name ?? old('owner_name') }}"  placeholder="Enter Owner Name" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Owner Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="owner_phone" class="form-control" value="{{ $vehicle->owner_phone ?? old('owner_phone') }}" placeholder="Enter Owner Contact Number" required>
                </div>
              <div class="col-md-6 mb-3 company-fields">
                    <label for="company_id" class="form-label fw-bold">Company</label>
                    <select id="company_id" name="company_id" class="form-select">
                        <option value="">-- Select Company --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" 
                                {{ old('company_id', $vehicle->company_id ?? '') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3 lease-fields d-none">
                    <label class="form-label fw-bold">Lease Start</label>
                    <input type="date" name="lease_start" class="form-control" 
                        value="{{ $vehicle->lease_start ?? old('lease_start') }}">
                </div>
                <div class="col-md-6 mb-3 lease-fields d-none">
                    <label class="form-label fw-bold">Lease End</label>
                    <input type="date" name="lease_end" class="form-control" 
                        value="{{ $vehicle->lease_end ?? old('lease_end') }}">
                </div>

                <!-- Insurance -->
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

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Insurance Policy No <span class="text-danger">*</span></label>
                    <input type="text" name="insurance_policy_no" class="form-control" 
                           value="{{ $vehicle->insurance_policy_no ?? old('insurance_policy_no') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Insurance Expiry <span class="text-danger">*</span></label>
                    <input type="date" name="insurance_expiry" class="form-control" value="{{ $vehicle->insurance_expiry ?? old('insurance_expiry') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Emission Test Expiry </label>
                    <input type="date" name="emission_test_expiry" class="form-control" value="{{ $vehicle->emission_test_expiry ?? old('emission_test_expiry') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Revenue License Expiry <span class="text-danger">*</span></label>
                    <input type="date" name="revenue_license_expiry" class="form-control" 
                           value="{{ $vehicle->revenue_license_expiry ?? old('revenue_license_expiry') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>

                <!-- Financial -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Purchase Price <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" 
                        value="{{ $vehicle->purchase_price ?? old('purchase_price') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="form-control" 
                        value="{{ $vehicle->purchase_date ?? old('purchase_date') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Depreciation Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="depreciation_rate" class="form-control" 
                        value="{{ $vehicle->depreciation_rate ?? old('depreciation_rate') }}">
                </div>

                <hr>

                <h4 class="mt-4 mb-4 fw-bold">Documents</h3>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Revenue License (jpg, png, pdf) </label>
                    <input type="file" name="revenue_license_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Insurance Document (jpg, png, pdf) </label>
                    <input type="file" name="insurance_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Emission Test Document (jpg, png, pdf) </label>
                    <input type="file" name="emission_test_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Other Document (jpg, png, pdf) </label>
                    <input type="file" name="other_doc_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <hr class="mb-0">
                <h4 class="mt-4 mb-4 fw-bold">Images</h4>
                <!-- Vehicle Images -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Front Image </label>
                    <input type="file" name="vehicle_front" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Back Image </label>
                    <input type="file" name="vehicle_back" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Left Side Image </label>
                    <input type="file" name="vehicle_left" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Right Side Image </label>
                    <input type="file" name="vehicle_right" class="form-control" accept=".jpg,.jpeg,.png">
                </div>

                <hr>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Enter any remarks..."></textarea>
                </div>

            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success">
                    Save Vehicle
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ownershipSelect = document.querySelector("select[name='ownership_type_id']");
        const leaseFields = document.querySelectorAll(".lease-fields");
        const financialFields = document.querySelectorAll(
            "input[name='purchase_price'], input[name='purchase_date'], input[name='depreciation_rate']"
        );
        const financialLabels = Array.from(financialFields).map(f => f.closest('.mb-3').querySelector('label'));

        function toggleFields() {
            const selectedText = ownershipSelect.options[ownershipSelect.selectedIndex]?.text.toLowerCase();

            // Lease fields
            if (selectedText.includes("lease")) {
                leaseFields.forEach(el => el.classList.remove("d-none"));
            } else {
                leaseFields.forEach(el => el.classList.add("d-none"));
            }

            // Financial fields
            if (selectedText.includes("rented")) {
                financialFields.forEach(f => f.closest('.mb-3').classList.add("d-none"));
            } else {
                financialFields.forEach(f => f.closest('.mb-3').classList.remove("d-none"));
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
    const vehicleType = document.getElementById('vehicle-type');
    const vehicleCategory = document.getElementById('vehicle-category');
    const allCategories = Array.from(vehicleCategory.options);

    function filterCategories() {
        const selectedType = vehicleType.value;
        vehicleCategory.innerHTML = '';

        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = '-- Select Category --';
        vehicleCategory.appendChild(defaultOption);

        // Append only matching categories
        allCategories.forEach(option => {
            if(option.dataset.type === selectedType) {
                vehicleCategory.appendChild(option);
            }
        });

        // Do NOT select any category automatically
        vehicleCategory.value = '';
    }

    // Initial filter (no category selected)
    filterCategories();

    // Filter categories when vehicle type changes
    vehicleType.addEventListener('change', filterCategories);
});
</script>



@endpush

