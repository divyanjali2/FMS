@extends('layouts.app')

@section('content')
@php
    $positions = ['GM', 'MD', 'HOD', 'Staff'];
    $departments = [
        'Senior Management',
        'HR Department',
        'IT Department',
        'Finance Department',
        'Marketing Department',
        'Rent a Car Department',
        'Digital Marketing Department',
        'Transfers Department',
        'Airport Parking',
        'Fleet Management Department',
        'Procurement Department'
    ];

@endphp
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit User</h4>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" 
                           id="name" value="{{ old('name', $user->name) }}" required>
                    <label for="name">Full Name</label>
                </div>

                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" 
                           id="email" value="{{ old('email', $user->email) }}" required>
                    <label for="email">Email Address</label>
                </div>

                <!-- Password -->
                <div class="mb-3 position-relative">
                    <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" id="password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3 position-relative">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Position -->
                <div class="form-floating mb-3">
                    <select name="position" class="form-select" id="position" required>
                        <option value="">Select Position</option>
                        @foreach($positions as $position)
                           <option value="{{ $position }}" {{ old('position', $user->position) == $position ? 'selected' : '' }}>
                                {{ ucfirst($position) }}
                            </option>
                        @endforeach
                    </select>
                    <label for="position">Position</label>
                </div>

                <!-- Department -->
               <div class="form-floating mb-3">
                    <select name="department" class="form-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" 
                                {{ (old('department', $user->department ?? '') == $department) ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                    <label for="department">Department</label>
                </div>

            
                <!-- Roles -->
                <div class="mb-3">
                    <label class="form-label">Roles</label>
                    <div class="d-flex flex-wrap gap-2" data-admin-id="{{ $adminRoleId }}">
                        @foreach($roles as $role)
                            <input type="checkbox" class="btn-check role-checkbox" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                                {{ in_array($role->id, $user->roles->pluck('id')->toArray()) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary rounded-pill px-3" for="role-{{ $role->id }}">
                                <i class="bi bi-shield-lock"></i> {{ $role->label ?? $role->name }}
                            </label>
                        @endforeach
                    </div>
                    <small class="text-muted">Click to select roles (you can pick multiple).</small>
                </div>

                <!-- Affiliation -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Affiliation</label>

                    <div class="form-check">
                       <input class="form-check-input" type="checkbox" name="is_sr" id="is_sr" value="1"
                            {{ old('is_sr', $user->is_sr) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_sr">SR Rent A Car</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_elite" id="is_elite" value="1"
                            {{ old('is_elite', $user->is_elite) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_elite">Elite Rent A Car</label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        if (field.type === "password") {
            field.type = "text";
        } else {
            field.type = "password";
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const srCheckbox = document.getElementById('is_sr');
    const eliteCheckbox = document.getElementById('is_elite');
    const roleContainer = document.querySelector('[data-admin-id]');
    const adminRoleId = roleContainer.dataset.adminId;

    function handleRoleChange() {
        const adminRoleChecked = Array.from(document.querySelectorAll('.role-checkbox'))
            .some(cb => cb.checked && cb.value == adminRoleId);

        if (adminRoleChecked) {
            srCheckbox.checked = true;
            eliteCheckbox.checked = true;
        }
        // else do nothing, preserve existing SR/Elite state
    }

    // Watch all role checkboxes
    document.querySelectorAll('.role-checkbox').forEach(cb => {
        cb.addEventListener('change', handleRoleChange);
    });

    // Initial check on page load
    handleRoleChange();

    // Allow manual checking of SR/Elite if admin not selected
    srCheckbox.addEventListener('change', function () {
        const adminSelected = Array.from(document.querySelectorAll('.role-checkbox'))
            .some(cb => cb.checked && cb.value == adminRoleId);

        if (!adminSelected && this.checked) {
            eliteCheckbox.checked = false;
        }
    });

    eliteCheckbox.addEventListener('change', function () {
        const adminSelected = Array.from(document.querySelectorAll('.role-checkbox'))
            .some(cb => cb.checked && cb.value == adminRoleId);

        if (!adminSelected && this.checked) {
            srCheckbox.checked = false;
        }
    });
});
</script>
@endpush

@endsection
