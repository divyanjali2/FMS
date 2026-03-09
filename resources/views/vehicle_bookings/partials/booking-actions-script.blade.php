<script>
document.addEventListener('DOMContentLoaded', function () {

    // ----- ELEMENT REFERENCES -----
    const modalEl = document.getElementById('bookingActionsModal');
    const modal = new bootstrap.Modal(modalEl);
    const bookingIdInput = document.getElementById('modalBookingId');
    const markArrivedDiv = document.getElementById('markArrivedOptions');
    const extendForm = document.getElementById('extendDepartureForm');
    const cancelTableDiv = document.getElementById('cancelBookingTable');
    const cancelTableBody = document.getElementById('cancelBookingTableBody');
    const alternativeForm = document.getElementById('alternativeVehicleForm');
    const altVehicleContent = document.getElementById('altVehicleContent');
    const dropdownBtn = document.getElementById('bookingActionsDropdown');
    const dropdownMenu = dropdownBtn.nextElementSibling;

    let fpInstance = null;
    let fpAltInstance = null;

    // ----- HELPER FUNCTIONS -----
    function resetModal() {
        // Hide all sections
        markArrivedDiv.classList.add('d-none');
        extendForm.classList.add('d-none');
        extendForm.reset();
        cancelTableDiv.classList.add('d-none');
        cancelTableBody.innerHTML = '';
        alternativeForm.classList.add('d-none');
        altVehicleContent.innerHTML = '';
        // Reset flatpickr instances
        if (fpInstance) { fpInstance.destroy(); fpInstance = null; }
        if (fpAltInstance) { fpAltInstance.destroy(); fpAltInstance = null; }
    }

    function updateDropdownActions(status, arrivalDate) {
        dropdownMenu.querySelectorAll('.booked-only, .not-booked')
            .forEach(el => el.classList.add('d-none'));

        const today = new Date();
        const arrival = new Date(arrivalDate);

        if (status === 'booked') {
            dropdownMenu.querySelectorAll('.booked-only')
                .forEach(el => el.classList.remove('d-none'));

            // Hide "Mark as On Tour" until arrival date
            if (today < arrival) {
                dropdownMenu
                    .querySelector('[data-action="mark-on-tour"]')
                    ?.classList.add('d-none');
            }
        } else {
            dropdownMenu.querySelectorAll('.not-booked')
                .forEach(el => el.classList.remove('d-none'));
        }

        dropdownBtn.textContent = 'Select an action to perform';
    }

    function formatDate(isoString) {
        const d = new Date(isoString);
        return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
    }

    // ----- OPEN MODAL -----
    document.addEventListener('click', function(e){
        const cell = e.target.closest('[data-action="open-booking-modal"]');
        if(!cell) return;

        bookingIdInput.value = cell.dataset.bookingId;

        const bookingStatus = cell.dataset.bookingStatus;
        const arrivalDate = cell.dataset.arrival;

        resetModal();
        updateDropdownActions(bookingStatus, arrivalDate);

        modal.show();
    });

    // ----- DROPDOWN ACTIONS -----
    dropdownMenu.addEventListener('click', function(e){
        const actionBtn = e.target.closest('[data-action]');
        if(!actionBtn) return;

        const action = actionBtn.dataset.action;
        const bookingId = bookingIdInput.value;
        const cell = document.querySelector(`[data-booking-id="${bookingId}"]`);
        if(!bookingId || !cell) return;

        // Hide all sections before showing new
        resetModal();

        switch(action){
            // ----- MARK ARRIVED -----
            case 'mark-arrived':
                markArrivedDiv.classList.remove('d-none');
                document.getElementById('routineArrivalForm').action = `/vehicle-bookings/${bookingId}/arrived`;
                document.getElementById('emergencyArrivalForm').action = `/vehicle-bookings/${bookingId}/arrived`;
                break;

            // ----- CANCEL BOOKING -----
            case 'cancel-booking':
                fetch(`/rentals/${bookingId}/related-rentals`)
                    .then(res => res.json())
                    .then(data => {
                        cancelTableBody.innerHTML = '';
                        data.relatedRentals.forEach((r,i) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${i+1}</td>
                                <td>${r.driver_name}</td>
                                <td>${formatDate(r.arrival_date)}</td>
                                <td>${formatDate(r.departure_date)}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger btnCancelSingle" data-id="${r.id}">Cancel</button>
                                </td>
                            `;
                            cancelTableBody.appendChild(row);
                        });
                        cancelTableDiv.classList.remove('d-none');

                        // Attach cancel button handlers
                        cancelTableBody.querySelectorAll('.btnCancelSingle').forEach(btn => {
                            btn.addEventListener('click', async function(){
                                if(!confirm("Are you sure you want to cancel this booking?")) return;
                                const res = await fetch(`/rentals/${this.dataset.id}/cancel`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'Content-Type': 'application/json'
                                    }
                                });
                                if(res.ok) location.reload();
                                else alert("Failed to cancel booking");
                            });
                        });
                    });
            break;

            // ----- EXTEND DEPARTURE -----
            case 'extend-departure':
                extendForm.action = `/vehicle-bookings/${bookingId}/extend-departure`;
                document.getElementById('vehicleRegNo').value = cell.dataset.vehicleReg;
                document.getElementById('currentDepartureDate').value = cell.dataset.departureDate;

                const bookedRanges = JSON.parse(cell.dataset.bookedRanges || '[]');

                if(fpInstance){ fpInstance.destroy(); fpInstance = null; }
                fpInstance = flatpickr("#newDepartureDate", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: cell.dataset.departureDate,
                    disable: bookedRanges,
                    onDayCreate: (_,__,___,dayElem) => {
                        bookedRanges.forEach(range => {
                            const date = dayElem.dateObj;
                            if(date >= new Date(range.from) && date <= new Date(range.to)){
                                dayElem.classList.add("booked-day");
                            }
                        });
                    }
                });

                extendForm.classList.remove('d-none');

            break;


            // ----- ADD ALTERNATIVE VEHICLE -----
            case 'add-alternative-vehicle':
                fetch(`/vehicle-bookings/${bookingId}/alternative-vehicles`)
                    .then(res => res.json())
                    .then(data => {
                        const rental = data.rental;
                        const availableVehicles = data.availableVehicles;

                        let html = `
                            <p><strong>Vehicle Number :</strong> ${rental.vehicle.reg_no}</p>
                            <p><strong>Customer : </strong> ${rental.driver_name}</p>
                            <p><strong>Booking Dates:</strong> ${formatDate(rental.arrival_date)} → ${formatDate(rental.departure_date)}</p>
                            <div class="alert alert-info">
                                If the preferred vehicle is not listed, it may be unavailable due to overlapping bookings.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select New Vehicle</label>
                                <select class="select2 form-select" id="alternativeVehicleSelect" name="new_vehicle_id" required>
                                    ${availableVehicles.map(v => `<option value="${v.id}" data-booked='${JSON.stringify(v.bookedRanges || [])}'>${v.reg_no}</option>`).join('')}
                                </select>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alternative Vehicle Start Date<span class="text-danger">*</span></label>
                                    <input type="text" id="alternativeStartDate" name="alternative_start_date" class="form-control" required placeholder="Select Start Date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reason for Vehicle Change<span class="text-danger">*</span></label>
                                    <textarea name="change_reason" class="form-control" rows="3" required placeholder="Reason"></textarea>
                                </div>
                            </div>
                        `;

                        altVehicleContent.innerHTML = html;
                        alternativeForm.action = `/vehicle-bookings/${bookingId}/assign-alternative`;
                        alternativeForm.classList.remove('d-none');
                        document.getElementById('altVehicleFormButtons').classList.remove('d-none');

                        $('#alternativeVehicleSelect').select2({
                            dropdownParent: $('#bookingActionsModal'), 
                            placeholder: "Search...",
                            allowClear: true,
                            width: '100%'
                        });

                        // Flatpickr for selected vehicle
                        const selectEl = document.getElementById('alternativeVehicleSelect');
                        function initAltDatePicker(optionEl){
                            const bookedRanges = JSON.parse(optionEl.dataset.booked || '[]');
                            if(fpAltInstance){ fpAltInstance.destroy(); fpAltInstance = null; }
                            fpAltInstance = flatpickr("#alternativeStartDate", {
                                dateFormat: "Y-m-d",
                                minDate: rental.arrival_date,
                                maxDate: rental.departure_date,
                                disable: bookedRanges,
                                onDayCreate: (_,__,___,dayElem)=>{
                                    bookedRanges.forEach(range=>{
                                        const date = dayElem.dateObj;
                                        if(date >= new Date(range.from) && date <= new Date(range.to)){
                                            dayElem.classList.add("booked-day");
                                        }
                                    });
                                }
                            });
                        }
                        initAltDatePicker(selectEl.selectedOptions[0]);
                        selectEl.addEventListener('change', function(){
                            initAltDatePicker(this.selectedOptions[0]);
                        });
                    });
            break;

            case 'mark-on-tour':
                if (!confirm('Are you sure you want to mark this booking as On Tour?')) return;

                fetch(`/vehicle-bookings/${bookingId}/mark-on-tour`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed');
                    return res.json();
                })
                .then(() => {
                    location.reload();
                })
                .catch(() => {
                    alert('Failed to mark booking as On Tour.');
                });
            break;

            case 'change-vehicle':
                fetch(`/vehicle-bookings/${bookingId}/change-vehicles`)
                    .then(res => res.json())
                    .then(data => {
                        const rental = data.rental;
                        const vehicles = data.vehicles;

                        let html = `
                            <p><strong>Current Vehicle:</strong> ${rental.vehicle.reg_no}</p>
                            <p><strong>Booking:</strong> ${formatDate(rental.arrival_date)} → ${formatDate(rental.departure_date)}</p>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Select New Vehicle</label>
                                <select class="select2 form-select" id="changeVehicleSelect" required>
                                    ${vehicles.map(v => `
                                        <option value="${v.id}">
                                            ${v.reg_no} — ${v.make} ${v.model} ${v.same_model ? '(Same Model)' : ''}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-primary" id="btnSaveVehicleChange">
                                    Save
                                </button>
                            </div>
                        `;

                        altVehicleContent.innerHTML = html;
                        alternativeForm.classList.remove('d-none');
                        document.getElementById('altVehicleFormButtons').classList.add('d-none');

                        $('#changeVehicleSelect').select2({
                            dropdownParent: $('#bookingActionsModal'), 
                            placeholder: "Search...",
                            allowClear: true,
                            width: '100%'
                        });

                        // Handle vehicle change
                        document.getElementById('btnSaveVehicleChange')
                        .addEventListener('click', () => {
                            const newVehicleId = document.getElementById('changeVehicleSelect').value;

                            if (!confirm('Confirm vehicle change?')) return;

                            fetch(`/vehicle-bookings/${bookingId}/change-vehicle`, {
                                method: 'POST', 
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'PATCH', 
                                    vehicle_id: newVehicleId
                                })                    
                            })
                            .then(res => {
                                if (!res.ok) throw new Error('Failed to change vehicle');
                                return res.json();
                            })
                            .then(() => {
                                alert('Vehicle changed successfully');
                                location.reload(); 
                            })
                            .catch(err => alert(err.message));
                        });
                    });
            break;

            // ----- CANCEL BOOKING -----
            case 'remove-booking':
                fetch(`/rentals/${bookingId}/related-rentals-tour`)
                    .then(res => res.json())
                    .then(data => {
                        cancelTableBody.innerHTML = '';
                        data.relatedRentals.forEach((r,i) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${i+1}</td>
                                <td>${r.driver_name}</td>
                                <td>${formatDate(r.arrival_date)}</td>
                                <td>${formatDate(r.departure_date)}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger btnCancelSingle" data-id="${r.id}">Cancel</button>
                                </td>
                            `;
                            cancelTableBody.appendChild(row);
                        });
                        cancelTableDiv.classList.remove('d-none');

                        // Attach cancel button handlers
                        cancelTableBody.querySelectorAll('.btnCancelSingle').forEach(btn => {
                            btn.addEventListener('click', async function(){
                                if(!confirm("Are you sure you want to cancel this booking?")) return;
                                const res = await fetch(`/rentals/${this.dataset.id}/remove`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'Content-Type': 'application/json'
                                    }
                                });
                                if(res.ok) location.reload();
                                else alert("Failed to cancel booking");
                            });
                        });
                    });
            break;
        }
    });

    document.getElementById('btnBackToActionsArrived').addEventListener('click', resetModal);
    document.getElementById('btnBackToActions').addEventListener('click', resetModal);
    document.getElementById('btnBackToActionsAlt')?.addEventListener('click', resetModal);
    document.getElementById('btnBackToActionsCancel')?.addEventListener('click', resetModal);
});
</script>