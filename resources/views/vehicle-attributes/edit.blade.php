@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="fw-bold mb-4">Edit Vehicle Attribute</h2>

    <form action="{{ route('vehicle-attributes.update', $vehicle_attribute->id) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Make</label>
                <input type="text" name="make" value="{{ $vehicle_attribute->make }}" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Model</label>
                <input type="text" name="model" value="{{ $vehicle_attribute->model }}" class="form-control" required>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('vehicle-attributes.index') }}" class="btn btn-secondary me-2">Back</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>
@endsection
