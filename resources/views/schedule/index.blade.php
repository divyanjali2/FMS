@extends('layouts.app')
@section('content')

<div class="container py-4">
    @php
        $dept = strtolower(trim(auth()->user()->department ?? ''));
        $isAdmin = strtolower(trim(auth()->user()->name ?? '')) === 'admin';

        $canManageShuttle   = $isAdmin || $dept === 'rent a car department';
        $canManageTransfers = $isAdmin || $dept === 'transfers department';
    @endphp

    <div class="d-flex gap-2 my-3">
        @if($canManageShuttle)
            {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transportServiceModal" data-type="shuttle">
                Shuttle
            </button> --}}
        @endif

        @if($canManageTransfers)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transportServiceModal" data-type="transfers">
                Add Transfers
            </button>
        @endif
    </div>

    <hr class="my-4">
    <h5 class="mb-2 fw-bold">Transport Services</h5>

    <div class="card">
        <div class="card-body">
            <table class="table table-responsive table-striped align-middle" id="transportServicesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Vehicle</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transportServices as $i => $ts)
                        @php
                            $type = strtolower(trim($ts->type)); 

                            $canEditRow = $isAdmin
                                || ($type === 'shuttle' && $canManageShuttle)
                                || ($type === 'transfers' && $canManageTransfers);
                        @endphp

                        @if($type === 'shuttle')
                            @continue
                        @endif
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-capitalize">{{ $ts->type }}</td>
                            <td>{{ $ts->vehicle->reg_no ?? '-' }}</td>
                            <td>{{ optional($ts->assigned_start_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $ts->assigned_end_at ? $ts->assigned_end_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $ts->pickup_location }}</td>
                            <td>{{ $ts->dropoff_location }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning {{ $canEditRow ? '' : 'disabled' }}"
                                    @if($canEditRow)
                                        data-bs-toggle="modal"
                                        data-bs-target="#editTransportServiceModal"
                                        data-id="{{ $ts->id }}"
                                        data-type="{{ $ts->type }}"
                                        data-vehicle_id="{{ $ts->vehicle_id }}"
                                        data-chauffer_id="{{ $ts->chauffer_id }}"
                                        data-start="{{ $ts->assigned_start_at ? $ts->assigned_start_at->format('Y-m-d\TH:i') : '' }}"
                                        data-end="{{ $ts->assigned_end_at ? $ts->assigned_end_at->format('Y-m-d\TH:i') : '' }}"
                                        data-pickup="{{ $ts->pickup_location }}"
                                        data-dropoff="{{ $ts->dropoff_location }}"
                                        data-passengers="{{ $ts->passenger_count }}"
                                        data-trip_code="{{ $ts->trip_code }}"
                                        data-note="{{ $ts->note }}"
                                    @else
                                        type="button"
                                        disabled
                                        title="No permission for this type"
                                    @endif
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button
                                    class="btn btn-sm btn-danger {{ $canEditRow ? '' : 'disabled' }}"
                                    @if($canEditRow)
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteTransportServiceModal"
                                        data-id="{{ $ts->id }}"
                                    @else
                                        type="button"
                                        disabled
                                        title="No permission for this type"
                                    @endif
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .pac-container {
        z-index: 9999 !important;
    }
</style>

{{-- CREATE MODAL --}}
<div class="modal fade" id="transportServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" action="{{ route('transport-services.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="transportServiceTitle">Add Transport Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="type" id="ts_type">

                <div class="row g-3">
                    @php
                        $now = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
                    @endphp

                    <div class="col-md-6">
                        <label class="form-label">
                            Assigned Start<span class="text-danger">*</span>
                        </label>
                        <input class="form-control" type="datetime-local" name="assigned_start_at" id="create_start" min="{{ $now }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Assigned End (optional)
                        </label>
                        <input class="form-control" type="datetime-local" name="assigned_end_at" id="create_end" min="{{ $now }}">
                    </div>

                    <div class="col-12">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="is_vehicle_assigned" name="is_vehicle_assigned" value="1">
                            <label class="form-check-label" for="is_vehicle_assigned">
                                Assign without vehicle
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6" id="create_vehicle_wrapper">
                        <label class="form-label">Vehicle<span class="text-danger">*</span></label>
                        <select class="form-select vehicle-select-create" name="vehicle_id" id="create_vehicle_id">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->reg_no }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 d-none" id="create_vehicle_type_wrapper">
                        <label class="form-label">Vehicle Type<span class="text-danger">*</span></label>
                        <select class="form-select" name="vehicle_type_id" id="create_vehicle_type_id">
                            <option value="">Select vehicle type</option>
                            @foreach($vehicleTypes as $vehicleType)
                                <option value="{{ $vehicleType->id }}">{{ $vehicleType->type_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chauffer<span class="text-danger">*</span></label>
                        <select class="form-select chauffer-select-create" name="employee_id" required>
                            <option value="">Select chauffer</option>
                            @foreach($chauffers as $c)
                                <option
                                    value="{{ $c['employee_id'] }}"
                                    data-employee="{{ $c['employee_id'] }}"
                                >
                                    {{ $c['preferred_name'] }} ({{ $c['whatsapp_number'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pickup<span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="pickup_location" name="pickup_location" value="Seeduwa Office" required >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dropoff<span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="dropoff_location" name="dropoff_location" required >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Passengers<span class="text-danger">*</span></label>
                        <input class="form-control" type="number" name="passenger_count" min="1" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editTransportServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" id="editTransportServiceForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Transport Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="type" id="edit_type">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle<span class="text-danger">*</span></label>
                        <select class="form-select vehicle-select-edit" name="vehicle_id" id="edit_vehicle_id" required>                            
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->reg_no }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chauffer<span class="text-danger">*</span></label>
                        <select class="form-select chauffer-select-edit" name="employee_id" id="edit_chauffer_id" required>
                            <option value="">Select chauffer</option>
                            @foreach($chauffers as $c)
                                <option 
                                    value="{{ $c['employee_id'] }}"
                                    data-employee="{{ $c['employee_id'] }}"
                                >
                                    {{ $c['preferred_name'] }} ({{ $c['whatsapp_number'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assigned Start<span class="text-danger">*</span></label>
                        <input class="form-control" type="datetime-local" name="assigned_start_at" id="edit_start" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assigned End</label>
                        <input class="form-control" type="datetime-local" name="assigned_end_at" id="edit_end">
                    </div>

                   <div class="col-md-6">
                        <label class="form-label">Pickup<span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="edit_pickup" name="pickup_location" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dropoff<span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="edit_dropoff" name="dropoff_location" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Passengers<span class="text-danger">*</span></label>
                        <input class="form-control" type="number" name="passenger_count" id="edit_passengers" min="1" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-warning" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteTransportServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" id="deleteTransportServiceForm">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Delete Transport Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete this transport service?</p>
                <label class="form-label">Note (Required)</label>
                <textarea class="form-control" name="delete_note" id="delete_note" rows="3" placeholder="Please provide a reason for deletion..." required></textarea>
                <small class="text-muted d-block mt-2">A note is required for audit purposes.</small>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" type="submit" id="confirmDeleteBtn">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#transportServicesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[4, 'desc']],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('.vehicle-select-create').select2({ dropdownParent: $('#transportServiceModal'), width: '100%' });
        $('.chauffer-select-create').select2({ dropdownParent: $('#transportServiceModal'), width: '100%' });

        $('.vehicle-select-edit').select2({ dropdownParent: $('#editTransportServiceModal'), width: '100%' });
        $('.chauffer-select-edit').select2({ dropdownParent: $('#editTransportServiceModal'), width: '100%' });

        const createModal = document.getElementById('transportServiceModal');
        const typeInput = document.getElementById('ts_type');
        const title = document.getElementById('transportServiceTitle');

        createModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const type = btn.getAttribute('data-type');
            typeInput.value = type;
            title.textContent = type === 'shuttle' ? 'Add Shuttle Service' : 'Add Transfers Service';
        });

        const editModal = document.getElementById('editTransportServiceModal');
        const editForm  = document.getElementById('editTransportServiceForm');

        editModal.addEventListener('show.bs.modal', function (event) {
            const b = event.relatedTarget;
            const id = b.getAttribute('data-id');

            editForm.action = `{{ url('/transport-services') }}/${id}`;

            document.getElementById('edit_type').value = b.getAttribute('data-type');
            const chaufferId = b.getAttribute('data-chauffer_id');
            $('#edit_chauffer_id').val(chaufferId).trigger('change');
            document.getElementById('edit_start').value = b.getAttribute('data-start');
            document.getElementById('edit_end').value = b.getAttribute('data-end');
            document.getElementById('edit_pickup').value = b.getAttribute('data-pickup');
            document.getElementById('edit_dropoff').value = b.getAttribute('data-dropoff');
            document.getElementById('edit_passengers').value = b.getAttribute('data-passengers');

            const currentVehicleId = b.getAttribute('data-vehicle_id');
            loadAvailableVehicles('#edit_start', '#edit_end', '#edit_vehicle_id', currentVehicleId);
        });

        const deleteModal = document.getElementById('deleteTransportServiceModal');
        const deleteForm = document.getElementById('deleteTransportServiceForm');
        const deleteNoteField = document.getElementById('delete_note');

        deleteModal.addEventListener('show.bs.modal', function (event) {
            const b = event.relatedTarget;
            const id = b.getAttribute('data-id');
            deleteForm.action = `{{ url('/transport-services') }}/${id}`;
            deleteNoteField.value = '';
        });

        deleteForm.addEventListener('submit', function (e) {
            const note = deleteNoteField.value.trim();
            if (!note) {
                e.preventDefault();
                alert('Please provide a note for deletion.');
                deleteNoteField.focus();
            }
        });

        async function loadAvailableVehicles(startSelector, endSelector, vehicleSelector, selectedVehicleId = null) {
            const start = $(startSelector).val();
            const end = $(endSelector).val();

            if (!start) {
                $(vehicleSelector).html('<option value="">Select vehicle</option>').trigger('change');
                return;
            }

            try {
                const response = await fetch(`{{ route('transport-services.available-vehicles') }}?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`);
                const vehicles = await response.json();

                let options = '<option value="">Select vehicle</option>';

                vehicles.forEach(vehicle => {
                    const selected = selectedVehicleId && String(selectedVehicleId) === String(vehicle.id) ? 'selected' : '';
                    options += `<option value="${vehicle.id}" ${selected}>${vehicle.reg_no}</option>`;
                });

                $(vehicleSelector).html(options).trigger('change');
            } catch (error) {
                console.error('Error loading vehicles:', error);
            }
        }

        $('#create_start, #create_end').on('change', function () {
            loadAvailableVehicles('#create_start', '#create_end', '#create_vehicle_id');
        });

        $('#edit_start, #edit_end').on('change', function () {
            const selectedVehicleId = $('#edit_vehicle_id').val();
            loadAvailableVehicles('#edit_start', '#edit_end', '#edit_vehicle_id', selectedVehicleId);
        });

        function toggleVehicleAssignmentMode() {
            const checked = $('#is_vehicle_assigned').is(':checked');

            if (checked) {
                $('#create_vehicle_wrapper').addClass('d-none');
                $('#create_vehicle_type_wrapper').removeClass('d-none');

                $('#create_vehicle_id').val('').trigger('change');
                $('#create_vehicle_id').prop('required', false);
                $('#create_vehicle_type_id').prop('required', true);
            } else {
                $('#create_vehicle_wrapper').removeClass('d-none');
                $('#create_vehicle_type_wrapper').addClass('d-none');

                $('#create_vehicle_type_id').val('');
                $('#create_vehicle_type_id').prop('required', false);
                $('#create_vehicle_id').prop('required', true);
            }
        }

        $('#is_vehicle_assigned').on('change', function () {
            toggleVehicleAssignmentMode();
        });

        $('#transportServiceModal').on('shown.bs.modal', function () {
            toggleVehicleAssignmentMode();
        });

        $('#transportServiceModal').on('hidden.bs.modal', function () {
            $('#is_vehicle_assigned').prop('checked', false);
            toggleVehicleAssignmentMode();
        });
    });
</script>

<script>
    function attachAutocomplete(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const options = {
            componentRestrictions: { country: "lk" }, // Sri Lanka only
            fields: ["formatted_address", "name", "geometry"]
        };

        const autocomplete = new google.maps.places.Autocomplete(input, options);

        autocomplete.addListener("place_changed", function () {
            const place = autocomplete.getPlace();
            input.value = place.formatted_address || place.name || input.value;
        });
    }

    function initSriLankaLocationAutocomplete() {
        // Create modal
        attachAutocomplete("pickup_location");
        attachAutocomplete("dropoff_location");

        // Edit modal
        attachAutocomplete("edit_pickup");
        attachAutocomplete("edit_dropoff");
    }
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initSriLankaLocationAutocomplete"
    async
    defer>
</script>

@endsection
