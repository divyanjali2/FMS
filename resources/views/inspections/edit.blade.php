@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white"><h4>Edit Inspection</h4></div>
    <div class="card-body">
        <form action="{{ route('inspections.update', $inspection->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Vehicle -->
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold">Vehicle <span class="text-danger">*</span></label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ $inspection->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->reg_no }} ({{ $vehicle->model }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Inspection Date <span class="text-danger">*</span></label>
                    <input type="date" name="inspection_date" class="form-control" required value="{{ $inspection->inspection_date }}">
                </div>
                <!-- Odometer -->
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold">Odometer Reading <span class="text-danger">*</span></label>
                    <input type="number" name="odometer_reading" class="form-control" required value="{{ $inspection->odometer_reading }}">
                </div>
                <hr>
                <!-- Faults -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Interior Faults</label>
                    <select name="interior_faults[]" class="form-select" multiple size="4">
                        @foreach($faults['interior'] ?? [] as $fault)
                            <option value="{{ $fault->id }}" 
                                {{ $inspection->faults->contains($fault->id) ? 'selected' : '' }}>
                                {{ $fault->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple faults</div>
                </div>

                <div class="col-md-12 my-4">
                    <label class="form-label fw-bold">Exterior Faults</label>
                    <select name="exterior_faults[]" class="form-select" multiple size="4">
                        @foreach($faults['exterior'] ?? [] as $fault)
                            <option value="{{ $fault->id }}"
                                {{ $inspection->faults->contains($fault->id) ? 'selected' : '' }}>
                                {{ $fault->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple faults</div>
                </div>

                <!-- Remarks -->
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3">{{ $inspection->remarks }}</textarea>
                </div>
                <hr>
                <!-- Existing Images -->
                @if($inspection->images && count($inspection->images) > 0)
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Current Images</label>
                    <div class="row">
                        @foreach($inspection->images as $image)
                        <div class="col-md-3 mb-2">
                            <img src="{{ $image }}" class="img-thumbnail" alt="Inspection Image">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- New Images -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Add New Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('inspections.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success">Update Inspection</button>
            </div>
        </form>
    </div>
</div>

<style>
    select[multiple] option:checked {
        background: linear-gradient(0deg, #4CAF50 0%, #4CAF50 100%);
        color: white;
    }
</style>
@endsection
