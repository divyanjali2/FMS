@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4 text-dark fw-bold">Post Check - {{ $inspection->job_code }} ({{ $inspection->vehicle->reg_no }})</h3>

    <form method="POST" action="{{ route('fleet_post_checks.store', $inspection->id) }}">
        @csrf
        <!-- Main container with bg -->
        <div class="card shadow-sm p-4 bg-light">
            <h5 class="fw-bold mb-3">Issues / Faults Verified by Garage</h5>

            <div class="row g-3">
                @foreach($inspection->gmWorkStatuses as $work)
                    @php
                        $issueName = $work->inbuildIssue->issue->name ?? null;
                        $faultName = $work->inbuildIssue->fault->name ?? null;
                    @endphp

                    <div class="col-md-6">
                        <div class="card h-100 p-3 border-start border-4 
                            {{ $issueName ? 'border-danger' : ($faultName ? 'border-warning' : 'border-secondary') }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @if($issueName)
                                        <span class="badge bg-danger me-1">Issue</span> {{ $issueName }}
                                    @elseif($faultName)
                                        <span class="badge bg-warning text-dark me-1">Fault</span> {{ $faultName }}
                                    @else
                                        <em class="text-muted">No issue/fault linked</em>
                                    @endif
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="items[{{ $work->id }}][verified]" value="1" id="check{{ $work->id }}">
                                    <label class="form-check-label fw-semibold" for="check{{ $work->id }}">Completed</label>
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label fw-semibold">Remarks</label>
                                <textarea name="items[{{ $work->id }}][remarks]" class="form-control" rows="2" placeholder="Add remarks if any"></textarea>
                            </div>

                            <input type="hidden" name="items[{{ $work->id }}][gm_work_status_id]" value="{{ $work->id }}">
                            <input type="hidden" name="items[{{ $work->id }}][issue_id]" value="{{ $work->inbuildIssue->issue_id ?? '' }}">
                            <input type="hidden" name="items[{{ $work->id }}][fault_id]" value="{{ $work->inbuildIssue->fault_id ?? '' }}">
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Action dropdown separated visually -->
            <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                <label class="form-label fw-bold">Action</label>
                <select id="statusDropdown" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="">-- Select Action --</option>
                    <option value="send_to_fm" {{ old('status') == 'send_to_fm' ? 'selected' : '' }}>Send to Fleet Manager</option>
                    <option value="send_back_to_garage" {{ old('status') == 'send_back_to_garage' ? 'selected' : '' }}>Send Back to Garage</option>
                </select>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-success btn-md rounded-pill">Save Post Check</button>
            </div>
        </div>
    </form>
</div>

<style>
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    input.form-check-input:checked ~ label {
        text-decoration: line-through;
        color: #198754;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="items"]');
        const dropdown = document.getElementById('statusDropdown');

        function updateDropdownOptions() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            // Clear existing options
            dropdown.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select Action --';
            dropdown.appendChild(defaultOption);

            if (allChecked) {
                const sendToFMOption = document.createElement('option');
                sendToFMOption.value = 'send_to_fm';
                sendToFMOption.textContent = 'Send to Fleet Manager';
                dropdown.appendChild(sendToFMOption);
            } else {
                const sendBackOption = document.createElement('option');
                sendBackOption.value = 'send_back_to_garage';
                sendBackOption.textContent = 'Send Back to Garage';
                dropdown.appendChild(sendBackOption);
            }
        }

        // Initial check on page load
        updateDropdownOptions();

        // Watch for checkbox changes
        checkboxes.forEach(cb => cb.addEventListener('change', updateDropdownOptions));
    });
</script>

@endsection
