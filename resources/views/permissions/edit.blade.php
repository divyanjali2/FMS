@if(auth()->user()->hasPermission('manage_users'))

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Edit Permission</h4>
        </div>
        <div class="card-body">
            <form id="edit-permission-form" action="{{ route('permissions.update', $permission) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <!-- Permission Name -->
                <div class="form-floating mb-3">
                    <input type="text" name="name" id="name" class="form-control" 
                           value="{{ old('name', $permission->name) }}" placeholder="Permission Name" required>
                    <label for="name">Permission Name <span class="text-danger">*</span></label>
                    <div class="invalid-feedback">
                        Please provide a valid permission name (letters, numbers, dashes, underscores).
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success" id="update-btn">
                        <i class="bi bi-check-circle me-1"></i> Update
                    </button>
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('edit-permission-form');

    // Bootstrap custom validation
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Optional: Live validation feedback for permission name
    const nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function () {
        const pattern = /^[A-Za-z0-9_-]+$/;
        if (!pattern.test(this.value)) {
            this.setCustomValidity("Invalid format");
        } else {
            this.setCustomValidity("");
        }
    });
});
</script>
@endpush

@endif
