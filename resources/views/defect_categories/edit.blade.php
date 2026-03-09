@if(auth()->user()->hasPermission('manage_inspection-reports'))
    @extends('layouts.app')
    @section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4>Edit Category</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('defect_categories.update', $defectCategory->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Name Field -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                            value="{{ old('name', $defectCategory->name) }}" required>
                    </div>

                    <!-- Description Field -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $defectCategory->description) }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Suppliers</label>
                        <select name="suppliers[]" class="form-select" multiple>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" 
                                    {{ in_array($supplier->id, $selectedSuppliers ?? []) ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple suppliers</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('defect_categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-success">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    @endsection
@endif