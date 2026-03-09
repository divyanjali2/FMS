@if(auth()->user()->hasPermission('manage_inspection-reports'))
@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h4>{{ isset($fault) ? 'Edit Fault' : 'Add Fault' }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ isset($fault) ? route('faults.update', $fault->id) : route('faults.store') }}" method="POST">
            @csrf
            @if(isset($fault)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $fault->name ?? '') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="interior" {{ old('type', $fault->type ?? '')=='interior' ? 'selected' : '' }}>Interior</option>
                        <option value="exterior" {{ old('type', $fault->type ?? '')=='exterior' ? 'selected' : '' }}>Exterior</option>
                        <option value="tires & wheels" {{ old('type', $fault->type ?? '')=='tires & wheels' ? 'selected' : '' }}>Tires & Wheels</option>
                        <option value="odometer & fuel" {{ old('type', $fault->type ?? '')=='odometer & fuel' ? 'selected' : '' }}>Odometer & Fuel</option>
                        <option value="glass & lights" {{ old('type', $fault->type ?? '')=='glass & lights' ? 'selected' : '' }}>Glass & Lights</option>
                        <option value="engine & fluid" {{ old('type', $fault->type ?? '')=='engine & fluid' ? 'selected' : '' }}>Engine & Fluid</option>
                        <option value="accessories & documents" {{ old('type', $fault->type ?? '')=='accessories & documents' ? 'selected' : '' }}>Accessories & Documents</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $fault->category_id ?? '')==$category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('faults.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success">{{ isset($fault) ? 'Update' : 'Save' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
@endif