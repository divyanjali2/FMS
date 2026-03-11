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
    });
</script>