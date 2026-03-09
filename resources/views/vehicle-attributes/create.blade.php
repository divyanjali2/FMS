@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="fw-bold mb-4">Add Vehicle Attribute</h2>

    <form action="{{ route('vehicle-attributes.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Make</label>
                <input type="text" name="make" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Model</label>
                <input type="text" name="model" class="form-control" required>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('vehicle-attributes.index') }}" class="btn btn-secondary me-2">Back</a>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </form>
</div>
@endsection
