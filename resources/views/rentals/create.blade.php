@extends('layouts.app')

@section('content')

<script>
    const bookedRanges = @json($bookedDates);
</script>

<div class="container py-5">
    <h3 class="mb-4 text-primary fw-bold">
        Vehicle No : {{ $vehicle->reg_no }}
    </h3>
    <div class="mb-3 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-1">
            <span style="width: 20px; height: 20px; display: inline-block; background-color: #b2505a; border-radius: 4px;"></span>
            <span>Booked Dates</span>
        </div>
        <div class="d-flex align-items-center gap-1">
            <span style="width: 20px; height: 20px; display: inline-block; background-color: #4a90e2; border-radius: 4px;"></span>
            <span>Freezed Dates</span>
        </div>
    </div>

    <form id="rentalForm" action="{{ route('rentals.store') }}" method="POST"
          class="shadow p-4 rounded bg-light border" novalidate>
        @csrf

        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

        {{-- Booking + Name --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Booking Number <span class="text-danger">*</span>
                </label>
                <input type="text" name="booking_number" id="booking_number" class="form-control" value="{{ old('booking_number') }}" placeholder="Enter booking number" required>

                <small id="bookingError" class="text-danger d-none">
                    Booking number already exists
                </small>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Salutation <span class="text-danger">*</span>
                </label>
                <select name="salutation" class="form-control" required>
                    <option value="">Select</option>
                    @foreach(['Mr','Ms','Mrs','Dr','Prof','Rev'] as $s)
                        <option value="{{ $s }}"
                            {{ old('salutation') == $s ? 'selected' : '' }}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Customer Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="driver_name" class="form-control" value="{{ old('driver_name') }}" placeholder="Enter customer name" required>
            </div>
        </div>

        {{-- Dates --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Arrival Date & Time <span class="text-danger">*</span>
                </label>
                <input type="text" id="arrival_date" name="arrival_date" class="form-control" value="{{ old('arrival_date') }}" placeholder="Select arrival date & time" required>
                <small class="text-muted">24-hour format (HH:MM)</small>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Departure Date & Time <span class="text-danger">*</span>
                </label>
                <input type="text" id="departure_date" name="departure_date" class="form-control" value="{{ old('departure_date') }}" placeholder="Select departure date & time" required>
                <small class="text-muted">24-hour format (HH:MM)</small>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Vehicle Pickup Date & Time <span class="text-danger">*</span>
                </label>
                <input type="text" id="vehicle_pickup" name="vehicle_pickup" class="form-control" value="{{ old('vehicle_pickup') }}" placeholder="Select vehicle pickup date & time" required>
                <small class="text-muted">24-hour format (HH:MM)</small>
            </div>
        </div>

        {{-- Other fields --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    Passengers <span class="text-danger">*</span>
                </label>
                <input type="number" name="passengers" class="form-control" value="{{ old('passengers') }}" min="1" placeholder="Enter number of passengers" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">
                    Booking From <span class="text-danger">*</span>
                </label>
                <select name="company_id" class="form-select" required>
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Notes</label>
            <textarea name="notes" class="form-control" placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('vehicle.bookings') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" id="submitBtn" class="btn btn-success">
                Save Rental
            </button>
        </div>
    </form>

    
</div>

<script>
    const form = document.getElementById("rentalForm");
    const submitBtn = document.getElementById("submitBtn");

    form.addEventListener("submit", function (e) {

        // Always reset first (important)
        submitBtn.disabled = false;
        submitBtn.innerText = "Save Rental";

        const arrival   = arrival_date.value.trim();
        const departure = departure_date.value.trim();
        const pickup    = vehicle_pickup.value.trim();

        // ❌ Validation failed
        if (!arrival || !departure || !pickup) {
            e.preventDefault();

            [arrival_date, departure_date, vehicle_pickup].forEach(i => {
                i.classList.toggle("is-invalid", !i.value);
            });

            return;
        }

        // ✅ Validation passed → NOW disable
        submitBtn.disabled = true;
        submitBtn.innerText = "Saving...";
    });
</script>


<script>
    let timer;
    booking_number.addEventListener("input", () => {
        clearTimeout(timer);

        const val = booking_number.value.trim();
        if (!val) return;

        timer = setTimeout(() => {
            fetch("{{ route('rentals.checkBookingNumber') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ booking_number: val })
            })
            .then(r => r.json())
            .then(d => {
                submitBtn.disabled = d.exists;
                bookingError.classList.toggle("d-none", !d.exists);

                if (!d.exists) {
                    submitBtn.innerText = "Save Rental";
                }
            });
        }, 400);
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const bookedRanges = @json($bookedDates);
    const frozenRanges = @json($frozenDates);

    const disabledBookedRanges = bookedRanges.map(r => ({
        from: `${r.from}T00:00:00`,
        to:   `${r.to}T23:59:59`
    }));

    const disabledFrozenRanges = frozenRanges.map(r => ({
        from: `${r.from}T00:00:00`,
        to:   `${r.to}T23:59:59`
    }));

    function isBooked(dateObj) {
        return bookedRanges.some(range => {
            const from = new Date(range.from + "T00:00:00");
            const to = new Date(range.to + "T23:59:59");
            return dateObj >= from && dateObj <= to;
        });
    }

    function isFrozen(dateObj) {
        return frozenRanges.some(range => {
            const from = new Date(range.from + "T00:00:00");
            const to = new Date(range.to + "T23:59:59");
            return dateObj >= from && dateObj <= to;
        });
    }

    const commonSettings = {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        minDate: "today",
        onDayCreate(dObj, dStr, fp, dayElem) {
            if (isBooked(dayElem.dateObj)) {
                dayElem.classList.add("booked-date");
                dayElem.setAttribute("title", "Already Booked");
            }
            if (isFrozen(dayElem.dateObj)) {
                dayElem.classList.add("frozen-date");
                dayElem.setAttribute("title", "Vehicle Frozen");
            }
        },
        disable: [...disabledBookedRanges, ...disabledFrozenRanges],
    };

    const departurePicker = flatpickr("#departure_date", commonSettings);
    const arrivalPicker = flatpickr("#arrival_date", {
        ...commonSettings,
        onChange(selectedDates) {
            if (selectedDates.length) {
                const nextDay = new Date(selectedDates[0]);
                nextDay.setDate(nextDay.getDate() + 1);
                departurePicker.set("minDate", nextDay);
            }
        }
    });
    flatpickr("#vehicle_pickup", commonSettings);
});

</script>

<style>
    .flatpickr-day.booked-date {
        background: #b2505a74 !important;  
        color: #fff !important;
        border-radius: 50%;
        cursor: not-allowed !important;
    }

.flatpickr-day.frozen-date {
    background: #3498db59 !important;  /* Different color */
    color: #fff !important;
    border-radius: 50%;
    cursor: not-allowed !important;
}


    .flatpickr-day.disabled {
        background: #e9ecef !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
    }

    button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
</style>

@endsection
