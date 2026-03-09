@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white"><h4>Add Inspection</h4></div>
        <div class="card-body">
            <form id="inspection-form" action="{{ route('inspections.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id ?? request('vehicle') }}">
            <input type="hidden" name="rental_id" value="{{ $rental_id ?? request('rental_id') }}">
            <input type="hidden" name="repair_type" value="{{ $repair_type ?? request('repair_type', 'routine') }}">

            @if($pendingTempInspections->count())
                <div class="card mb-3">
                    <div class="card-header bg-warning fw-bold">Faults Reported from Rent Team</div>
                    <div class="card-body">
                        <ul class="list-group">
                            @forelse($pendingTempInspections as $temp)
                                @if($temp->fault_id)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $temp->fault->name ?? 'Fault #'.$temp->fault_id }} - 
                                        <span class="badge bg-danger">{{ ucfirst($temp->status) }}</span>
                                    </li>
                                @else
                                    <li class="list-group-item text-muted">No faults reported</li>
                                @endif
                            @empty
                                <li class="list-group-item text-muted">No faults reported</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Vehicle -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle <span class="text-danger">*</span></label>
                    @if($vehicle)
                        <input type="text" class="form-control" value="{{ $vehicle->reg_no }} ({{ $vehicle->model }})" disabled>
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    @else
                        <select name="vehicle_id" class="form-select" required>
                            <option value="">-- Select Vehicle --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->reg_no }} ({{ $v->model }})</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Date -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Inspection Date <span class="text-danger">*</span></label>
                    <input type="date" name="inspection_date" class="form-control" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required >
                </div>

                <!-- Odometer -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Odometer Reading <span class="text-danger">*</span></label>
                    <input type="number" name="odometer_reading" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="service_type" class="form-label fw-bold">Service Type </label>
                    <select id="service_type" name="service_type" class="form-select">
                        <option value="">-- Select Service Type --</option>
                        <option value="wash_vehicle">Wash Vehicle</option>
                        <option value="full_service">Full Service</option>
                    </select>
                </div>

                <!-- Faults -->
                <div class="row">
                    @foreach($faults as $type => $faultGroup)
                        <div class="col-md-6 col-lg-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">{{ ucfirst($type) }} Faults</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($faultGroup as $fault)
                                            <div class="col-md-12 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="fault_{{ $fault->id }}" name="faults[{{ $fault->id }}][checked]" value="1" class="form-check-input me-2 fault-checkbox">

                                                    <label for="fault_{{ $fault->id }}" class="me-2 flex-grow-1">
                                                        {{ $fault->name }}
                                                    </label>

                                                    @if($fault->name === 'FUEL')
                                                        <div class="d-flex align-items-center fuel-input-wrapper">
                                                            <input type="number" name="faults[{{ $fault->id }}][percentage]" class="form-control form-control-sm d-none fault-percentage" placeholder="Fuel %" min="0" max="100">
                                                            <span class="ms-1 d-none fuel-percent-label">%</span>
                                                        </div>                                                    
                                                    @else
                                                        <select name="faults[{{ $fault->id }}][status]" class="form-select form-select-sm fault-status d-none" style="width: 120px;">
                                                            <option value="">-- Select --</option>
                                                            <option value="scratch">Scratch</option>
                                                            <option value="dent">Dent</option>
                                                            <option value="tear">Tear</option>
                                                            <option value="crack">Crack</option>
                                                            <option value="broken">Broken</option>
                                                            <option value="missing">Missing</option>
                                                            <option value="not_working">Not Working</option>
                                                            <option value="less_fuel">Less Fuel</option>
                                                            <option value="exceed">Exceed</option>
                                                            <option value="dirty">Dirty</option>
                                                        </select>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Remarks -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Remarks <span class="text-danger">*</span></label>
                    <textarea name="remarks" class="form-control" rows="3" required>{{ old('remarks') }}</textarea>
                </div>
                <input type="hidden" name="repair_type" value="{{ $repair_type }}">

                <!-- Images -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Upload Images</label>
                    <div id="image-upload-container">
                        <div class="input-group mb-2">
                            <input class="form-control" type="file" name="images[]" accept="images/*" capture="environment">
                        <button type="button" class="btn btn-danger remove-image d-none">Remove</button>
                    </div>
                </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2" id="add-image">+ Add Another Image</button>
                </div>
            </div>

            <!-- Vehicle Status -->
            <div class="d-md-flex gap-2">
                <div class="col-md-6 mb-3 ">
                    <label class="form-label fw-bold">Vehicle Status <span class="text-danger">*</span></label>
                    <select name="vehicle_status" class="form-select" required>
                        <option value="arrived" selected>Allow Booking</option>
                        <option value="freeze">Freeze Vehicle</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Vehicle Condition <span class="text-danger">*</span></label>
                    <select name="vehicle_condition" class="form-select" required>
                        <option value="under_maintenance" selected>Send to Maintenance</option>
                        <option value="available">Make Available</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('inspections.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success" id="submit-btn">
                    Save Inspection
                </button>            
            </div>
        </form>
    </div>
</div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('inspection-form');
            const btn  = document.getElementById('submit-btn');
            if (!form || !btn) return;

            form.addEventListener('submit', function () {
                if (!form.checkValidity()) return;
                btn.disabled = true;
                btn.textContent = 'Saving...';
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Fault checkbox toggle
            document.querySelectorAll(".fault-checkbox").forEach(function (checkbox) {
                checkbox.addEventListener("change", function () {
                    let dropdown = this.closest("div").querySelector(".fault-status");
                    if (this.checked) {
                        dropdown.classList.remove("d-none");
                    } else {
                        dropdown.classList.add("d-none");
                        dropdown.value = "";
                    }
                });
            });

            // Dynamic image upload
            const container = document.getElementById("image-upload-container");
            const addBtn = document.getElementById("add-image");

            addBtn.addEventListener("click", function () {
                const inputGroup = document.createElement("div");
                inputGroup.classList.add("input-group", "mb-2");

                inputGroup.innerHTML = `
                    <input type="file" name="images[]" class="form-control">
                    <button type="button" class="btn btn-danger remove-image"> 
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                        </svg>
                    </button>
                `;

                container.appendChild(inputGroup);
            });

            container.addEventListener("click", function (e) {
                if (e.target.classList.contains("remove-image")) {
                    e.target.closest(".input-group").remove();
                }
            });
        });
    </script>

    <script>
        document.querySelectorAll(".fault-percentage").forEach(function(input) {
            input.addEventListener("input", function () {
                const label = this.closest(".fuel-input-wrapper").querySelector(".fuel-percent-label");
                if (this.value !== '') {
                    label.classList.remove("d-none");
                } else {
                    label.classList.add("d-none");
                }
            });
        });

        document.querySelectorAll(".fault-checkbox").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                const container = this.closest("div");
                const dropdown = container.querySelector(".fault-status");
                const percentageInput = container.querySelector(".fault-percentage");
                const percentLabel = container.querySelector(".fuel-percent-label");

                if (this.checked) {
                    if (percentageInput) {
                        percentageInput.classList.remove("d-none");
                        percentLabel?.classList.toggle('d-none', !percentageInput.value);
                    } else if (dropdown) {
                        dropdown.classList.remove("d-none");
                    }
                } else {
                    if (percentageInput) {
                        percentageInput.classList.add("d-none");
                        percentageInput.value = '';
                        percentLabel?.classList.add("d-none");
                    }
                    if (dropdown) {
                        dropdown.classList.add("d-none");
                        dropdown.value = '';
                    }
                }
            });
        });
    </script>
@endpush
