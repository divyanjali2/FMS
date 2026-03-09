<div class="modal fade" id="freezeVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('vehicle-freezes.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">❄ Freeze Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                @php
                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                    $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                @endphp
                
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle<span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select select2" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->reg_no }} - {{ $vehicle->make }} {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date<span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required min="{{ $today }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">End Date<span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required min="{{ $tomorrow }}" value="{{ $tomorrow }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        
                        <select name="reason" id="reasonSelect" class="form-control" required>
                            <option value="">-- Select Reason --</option>
                            <option value="need more time for vehicle repair">Need more time for vehicle repair</option>
                            <option value="need more time for vehicle maintenance">Need more time for vehicle maintenance</option>
                            <option value="need more time for police clearance">Need more time for police clearance</option>
                            <option value="need more time for the insurance DR approval">Need more time for the insurance DR approval</option>
                            <option value="owner requested more time for the repair">Owner requested more time for the repair</option>
                            <option value="owner requested the vehicle for his personal use">Owner requested the vehicle for his personal use</option>
                            <option value="personal use for company staff">Personal use for company staff</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" name="other_reason" id="otherReasonInput" class="form-control mt-2" placeholder="Type reason" style="display: none;">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Freeze Vehicle</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')

<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#freezeVehicleModal'),
            placeholder: "Search...",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const reasonSelect = document.getElementById('reasonSelect');
    const otherInput = document.getElementById('otherReasonInput');

    reasonSelect.addEventListener('change', function () {
        if (this.value === 'Other') {
            otherInput.style.display = 'block';
            otherInput.required = true; // Make it required when visible
        } else {
            otherInput.style.display = 'none';
            otherInput.required = false;
        }
    });
});
</script>

@endpush
