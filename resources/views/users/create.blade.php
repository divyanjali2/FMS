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
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Create User</h4>
        </div>
        <div class="card-body">
            <form id="create-user-form" action="{{ route('users.store') }}" method="POST" novalidate>
                @csrf

                <!-- Name -->
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="name"
                           value="{{ old('name') }}" placeholder="Full Name" required>
                    <label for="name">Full Name <span class="text-danger">*</span></label>
                    <div class="invalid-feedback">Name is required.</div>
                </div>

                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="email"
                           placeholder="Email" required>
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>

                <!-- Password -->
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="password"
                           placeholder="Password" required>
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <div class="invalid-feedback">Password is required.</div>
                    <small id="password-strength" class="text-muted"></small>
                </div>

                <!-- Confirm Password -->
                <div class="form-floating mb-3">
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                           placeholder="Confirm Password" required>
                    <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <div class="invalid-feedback">Passwords must match.</div>
                    <small id="password-match" class="text-muted"></small>
                </div>

                <!-- Position -->
            <div class="form-floating mb-3">
                <select name="position" class="form-select" id="position" required>
                    <option value="">Select Position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position }}" {{ old('position') === $position ? 'selected' : '' }}>
                            {{ ucfirst($position) }}
                        </option>
                    @endforeach
                </select>
                <label for="position">Position <span class="text-danger">*</span></label>
            </div>

            <!-- Department -->
            <div class="form-floating mb-3">
                <select name="department" class="form-select" id="department" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                            {{ ucfirst($department) }}
                        </option>
                    @endforeach
                </select>
                <label for="department">Department <span class="text-danger">*</span></label>
            </div>


                <!-- Roles -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Assign Role</label>
                    <div class="row g-2">
                        @foreach($roles as $role)
                            <div class="col-md-4">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="roles[]" 
                                        id="role_{{ $role->id }}" value="{{ $role->id }}" required>
                                    <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">
                                        {{ $role->label ?? $role->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Please select one role.</div>
                    <div class="invalid-feedback d-block">Role is required.</div>
                </div>

                <!-- Affiliation -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Affiliation</label>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_sr" id="is_sr" value="1">
                        <label class="form-check-label" for="is_sr">SR Rent A Car</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_elite" id="is_elite" value="1">
                        <label class="form-check-label" for="is_elite">Elite Rent A Car</label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-plus me-1"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
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
        const form = document.getElementById('create-user-form');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const strengthText = document.getElementById('password-strength');
        const matchText = document.getElementById('password-match');

        // Bootstrap validation
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');

            // password match check
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity("Passwords do not match");
            } else {
                confirmPasswordInput.setCustomValidity("");
            }
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function () {
            const value = this.value;
            let strength = "Weak";
            let color = "text-danger";

            if (value.length > 8 && /[A-Z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value)) {
                strength = "Strong";
                color = "text-success";
            } else if (value.length >= 6) {
                strength = "Medium";
                color = "text-warning";
            }

            strengthText.textContent = "Strength: " + strength;
            strengthText.className = color;
        });

        // Confirm password match check
        confirmPasswordInput.addEventListener('input', function () {
            if (passwordInput.value !== confirmPasswordInput.value) {
                matchText.textContent = "Passwords do not match ❌";
                matchText.className = "text-danger";
            } else {
                matchText.textContent = "Passwords match ✅";
                matchText.className = "text-success";
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const roleRadios = document.querySelectorAll('input[name="roles[]"]');
        const srCheckbox = document.getElementById('is_sr');
        const eliteCheckbox = document.getElementById('is_elite');
        const adminRoleId = '{{ $adminRoleId }}';

        roleRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === adminRoleId) {
                    srCheckbox.checked = true;
                    eliteCheckbox.checked = true;
                } else {
                    srCheckbox.checked = false;
                    eliteCheckbox.checked = false;
                }
            });
        });
    });
</script>
@endpush
