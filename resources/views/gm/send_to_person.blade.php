@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <h3 class="mb-4 fw-bold text-primary">Send Inspection to Staff</h3>

        @if(session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm p-4 bg-light border-0 rounded-4">
            <form action="{{ route('gm.sendToPerson.store') }}" method="POST">
                @csrf

                <!-- Inspection Select -->
                <div class="mb-4">
                    <label for="inspection_id" class="form-label fw-bold">Select Inspection</label>
                    <select name="inspection_id" id="inspection_id" class="form-select form-select-md shadow-sm" required>
                        <option value="">-- Select Inspection --</option>
                        @foreach($inspections as $inspection)
                            <option value="{{ $inspection->id }}" data-job-code="{{ $inspection->job_code }}">
                                Inspection #00{{ $inspection->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Job Code Display -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Job Code</label>
                    <input type="text" id="job_code" class="form-control shadow-sm bg-white border-1 rounded-3" readonly>
                </div>

                <!-- Staff Selection -->
                <div class="mb-4">
                    <label for="user_id" class="form-label fw-bold">Select Person</label>
                    <select name="user_id" id="user_id" class="form-select form-select-md shadow-sm" required>
                        <option value="">-- Select Person --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Message -->
                <div class="mb-4">
                    <label for="message" class="form-label fw-bold">Message</label>
                    <textarea name="message" id="message" rows="4" class="form-control shadow-sm bg-white border-1 rounded-3" placeholder="Enter message..."></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-md shadow-sm w-40">Send Inspection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('inspection_id').addEventListener('change', function() {
            const selectedOption = this.selectedOptions[0];
            document.getElementById('job_code').value = selectedOption.dataset.jobCode || '';
        });
    </script>

    <style>
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #0d6efd;
        }

        .card:hover {
            transform: translateY(-3px);
            transition: all 0.2s ease-in-out;
        }

        .btn-success:hover {
            background-color: #198754;
        }
    </style>
@endsection
