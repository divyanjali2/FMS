@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold">Vehicles</h2>
    <div>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary me-2">Add Vehicle</a>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#freezeVehicleModal">
            ❄ Freeze Vehicle
        </button>
    </div>
</div>


<div class="card shadow-sm border-0">
    <div class="table-responsive card-body p-3">
        <ul class="nav nav-tabs mb-3" id="vehicleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab"
                        data-bs-target="#active" type="button" role="tab">
                    Active Vehicles
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="disabled-tab" data-bs-toggle="tab"
                        data-bs-target="#disabled" type="button" role="tab">
                    Disabled Vehicles
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="freeze-tab" data-bs-toggle="tab"
                        data-bs-target="#freeze" type="button" role="tab">
                    ❄ Freeze Vehicle
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Active Vehicles Tab -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">

                @include('vehicles.table', [
                    'vehicles' => $vehicles,
                    'showDisableButton' => true
                ])

            </div>

            <!-- Disabled Vehicles Tab -->
            <div class="tab-pane fade" id="disabled" role="tabpanel">

                @include('vehicles.table', [
                    'vehicles' => $disabledVehicles,
                    'showDisableButton' => false
                ])

            </div>

            <div class="tab-pane fade" id="freeze" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Extended End Date</th>
                                <th>Extended Reason</th>
                                <th>Reason</th>
                                <th>Remarks</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($freezes as $freeze)
                                <tr id="freeze-row-{{ $freeze->id }}">
                                    <td>{{ $freeze->vehicle->reg_no ?? '-' }}</td>
                                    <td>{{ $freeze->start_date }}</td>
                                    <td>{{ $freeze->old_end_date ?? $freeze->end_date }}</td>
                                    <td>{{ !empty($freeze->extend_reason) ? $freeze->end_date : '-' }}</td>
                                    <td>{{ $freeze->extend_reason ?? '-' }}</td>
                                    <td>{{ $freeze->reason ?? '-' }}</td>
                                    <td>{{ $freeze->remarks }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="unfreezeVehicle({{ $freeze->id }})">
                                            Unfreeze
                                        </button>

                                        <button class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#extendFreezeModal" 
                                                data-freeze-id="{{ $freeze->id }}" 
                                                data-end-date="{{ $freeze->end_date }}">
                                            Extend
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No frozen vehicles found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('vehicle_freeze.partials.freeze-vehicle-modal')

            <div class="modal fade" id="extendFreezeModal" tabindex="-1" aria-labelledby="extendFreezeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="extendFreezeForm" method="POST" action="{{ route('vehicle-freeze.extend') }}">
                        @csrf
                        <input type="hidden" name="freeze_id" id="modal_freeze_id">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="extendFreezeModalLabel">Extend Freeze</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label for="modal_end_date" class="form-label">New End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="modal_end_date" class="form-control" required>
                            </div>
                            <div class="modal-body">
                                <label for="reasonSelect" class="form-label">Extend Reason <span class="text-danger">*</span></label>

                                <!-- Dropdown -->
                                    <select name="extend_reason" id="reasonSelect" class="form-control" required>
                                    <option value="">-- Select Reason --</option>
                                    <option value="need more time for vehicle repair" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for vehicle repair' ? 'selected' : '' }}>
                                        Need more time for vehicle repair
                                    </option>
                                    <option value="need more time for vehicle maintenance" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for vehicle maintenance' ? 'selected' : '' }}>
                                        Need more time for vehicle maintenance
                                    </option>
                                    <option value="need more time for police clearance" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for police clearance' ? 'selected' : '' }}>
                                        Need more time for police clearance
                                    </option>
                                    <option value="need more time for the insurance DR approval" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'need more time for the insurance DR approval' ? 'selected' : '' }}>
                                        Need more time for the insurance DR approval
                                    </option>
                                    <option value="owner requested more time for the repair" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'owner requested more time for the repair' ? 'selected' : '' }}>
                                        Owner requested more time for the repair
                                    </option>
                                    <option value="owner requested the vehicle for his personal use" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'owner requested the vehicle for his personal use' ? 'selected' : '' }}>
                                        Owner requested the vehicle for his personal use
                                    </option>
                                    <option value="personal use for the company staff" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'personal use for the company staff' ? 'selected' : '' }}>
                                        Personal use for the company staff
                                    </option>
                                    <option value="Other" {{ old('extend_reason', $freeze->extend_reason ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>

                                <!-- Text input for "Other" reason -->
                                <input type="text" name="other_reason" id="otherReasonInput" class="form-control mt-2" placeholder="Type reason" value="{{ old('other_reason') }}" style="display: none;">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable Confirmation Modal -->
<div class="modal fade" id="disableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Disable Vehicle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to disable this vehicle?</p>
                <p class="fw-bold mb-0" id="vehicleName"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmDisableBtn" class="btn btn-danger">Yes, Disable</button>
            </div>
        </div>
    </div>
</div>

@foreach($vehicles as $vehicle)
    @include('vehicles.show', ['vehicle' => $vehicle])
@endforeach

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        $(document).ready(function() {
            $('#vehiclesTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 25,
                columnDefs: [
                    { orderable: false, targets: -1 } 
                ]
            });
        });
    </script>

    <script>
        let disableVehicleId = null;

        $(document).on("click", ".disable-btn", function () {
            disableVehicleId = $(this).data("id");
            let name = $(this).data("name");

            $("#vehicleName").text("Vehicle: " + name);

            let modal = new bootstrap.Modal(document.getElementById('disableModal'));
            modal.show();
        });

        $("#confirmDisableBtn").click(function () {
            $.ajax({
                url: "/vehicles/" + disableVehicleId + "/disable",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    location.reload();
                },
                error: function () {
                    alert("Failed to disable vehicle");
                }
            });
        });

        function unfreezeVehicle(freezeId) {
            if (!confirm('Are you sure you want to unfreeze this vehicle?')) return;

            fetch(`/vehicle-freezes/${freezeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(() => {
                location.reload();
            })
            .catch(() => alert('Failed to unfreeze vehicle'));
        }
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Search...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        document.getElementById('extendFreezeModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const freezeId = button.getAttribute('data-freeze-id');
            const endDate  = button.getAttribute('data-end-date');

            // Set hidden input
            document.getElementById('modal_freeze_id').value = freezeId;

            // Optionally prefill end date
            document.getElementById('modal_end_date').value = endDate;
        });
    </script>

    <script>
        document.getElementById('extendFreezeModal')
        .addEventListener('shown.bs.modal', function () {

            const modal = this;
            const reasonSelect = modal.querySelector('[name="extend_reason"]');
            const otherInput   = modal.querySelector('[name="other_reason"]');

            function toggleOtherReason() {
                if (reasonSelect.value === 'Other') {
                    otherInput.style.display = 'block';
                    otherInput.required = true;
                } else {
                    otherInput.style.display = 'none';
                    otherInput.required = false;
                    otherInput.value = '';
                }
            }

            reasonSelect.addEventListener('change', toggleOtherReason);
            toggleOtherReason(); 
        });
    </script>
@endpush
@endsection
