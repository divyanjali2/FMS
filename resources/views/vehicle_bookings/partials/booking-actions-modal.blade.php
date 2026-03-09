<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    .booked-day {
        background: #f5a1a938 !important;
        color: #000000 !important;
        border-radius: 50%;
    }
</style>

<div class="modal fade" id="bookingActionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="fw-bold modal-title">Booking Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Hidden booking ID -->
                <input type="hidden" id="modalBookingId">
                <!-- Action Dropdown -->
                <div class="dropdown mb-3">
                    <button class="fw-bold btn btn-outline-secondary dropdown-toggle w-100" type="button" id="bookingActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Select an action to perform
                    </button>
                   <ul class="dropdown-menu w-100" id="bookingActionMenu">
                        <!-- BOOKED ONLY OPTIONS -->
                        <li class="dropdown-item booked-only d-none fw-bold text-primary" data-action="mark-on-tour">
                            Mark as On Tour
                        </li>
                        <li class="dropdown-item booked-only d-none fw-bold text-warning" data-action="change-vehicle">
                            Change Vehicle
                        </li>
                        <li class="dropdown-item booked-only d-none fw-bold text-danger" data-action="cancel-booking">
                            Cancel Booking
                        </li>
                    
                        <li class="dropdown-item not-booked d-none fw-bold text-primary" data-action="mark-arrived">
                            Mark Arrived
                        </li>
                        <li class="dropdown-item not-booked d-none fw-bold text-success" data-action="extend-departure">
                            Extend Departure Date
                        </li>
                        <li class="dropdown-item not-booked d-none fw-bold text-info" data-action="add-alternative-vehicle">
                            Add Alternative Vehicle
                        </li>
                        @if(auth()->user()->hasPermission('manage_general-manager'))
                        <li class="dropdown-item not-booked d-none fw-bold text-danger" data-action="remove-booking">
                            Remove Booking
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Mark Arrived Options -->
                <div id="markArrivedOptions" class="d-none mt-3">
                    <h6 class="fw-bold">Select Arrival Type</h6>
                    <div class="d-flex gap-2">
                        <form id="routineArrivalForm" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="arrival_type" value="routine">
                            <button type="submit" class="btn btn-success">Routine</button>
                        </form>

                        <form id="emergencyArrivalForm" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="arrival_type" value="emergency">
                            <button type="submit" class="btn btn-danger">Emergency</button>
                        </form>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-secondary mt-2" id="btnBackToActionsArrived">Back</button>
                    </div>
                </div>

                <!-- Cancel booking table -->
                <div id="cancelBookingTable" class="d-none mt-3">
                    <h6>Rented Bookings</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cancelBookingTableBody"></tbody>
                    </table>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-secondary mt-2" id="btnBackToActionsCancel">Back</button>
                    </div>
                </div>

                <!-- Extend Departure Form -->
                <form id="extendDepartureForm" method="POST" class="d-none mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Vehicle</label>
                        <input type="text" id="vehicleRegNo" class="fw-bold form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Departure Date</label>
                        <input type="text" id="currentDepartureDate" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Departure Date</label>
                        <input type="text" id="newDepartureDate" name="new_departure_date" class="form-control" required placeholder="Select New Departure Date">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="btnBackToActions">Back</button>
                        <button type="submit" class="btn btn-info">Extend</button>
                    </div>
                </form>

                <!-- Alternative Vehicle Form -->
                <form id="alternativeVehicleForm" class="d-none mt-3" method="POST">
                    @csrf
                    @method('PATCH')
                    <div id="altVehicleContent"></div>
                    <div id="altVehicleFormButtons" class="text-end mt-3">
                        <button type="button" class="btn btn-secondary" id="btnBackToActionsAlt">Back</button>
                        <button type="submit" class="btn btn-primary">Assign Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
