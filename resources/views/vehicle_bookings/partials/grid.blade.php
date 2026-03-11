<div class="container mb-2">
    <div class="d-flex gap-3 align-items-center">
        <div class="fw-bold"><span class="legend-box past-booking"></span> Past Booking</div>
        <div class="fw-bold"><span class="legend-box current-booking"></span> On Tour</div>
        <div class="fw-bold"><span class="legend-box future-booking"></span> Future Bookings</div>
        <div class="fw-bold"><span class="legend-box arrived-booking"></span> Arrived Rentals</div>
        <div class="fw-bold"><span class="legend-box arrived-with-inspection"></span> Inspected Rentals</div>
        <div class="fw-bold"><span class="legend-box past-date"></span> Underutilized</div>
        <div class="fw-bold"><span class="legend-box vehicle-frozen-date"></span> Frozen Date</div>
        <div class="fw-bold"><span class="legend-box alternative-range"></span> Alternative Vehicle</div>
    </div>
</div>  

<div class="booking-table-container">
    <table class="booking-table">
        <thead>
            <tr>
                <th rowspan="2">Reg No</th>
                <th rowspan="2">Make & Model</th>
                <th rowspan="2">Action</th>
                <th colspan="{{ $daysInMonth }}">Days</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th>{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        
        <tbody>
            @foreach($vehicles as $vehicle)
                @php
                    $user = auth()->user();
                    $vehicleCompany = $vehicle->company_id;

                    $srCompanies = [1, 2, 4, 5];
                    $eliteCompanies = [3, 6];

                    $canInteract = false;

                    if ($user->is_sr && in_array($vehicleCompany, $srCompanies)) {
                        $canInteract = true;
                    }

                    if ($user->is_elite && in_array($vehicleCompany, $eliteCompanies)) {
                        $canInteract = true;
                    }
                @endphp

                @php
                    $today = \Carbon\Carbon::today();
                    $bookings = $vehicle->rentals->sortBy('arrival_date')->values();
                    $bookingMap = [];
                    foreach($bookings as $b) {
                        $arrival = \Carbon\Carbon::parse($b->arrival_date);
                        $departure = \Carbon\Carbon::parse($b->departure_date);
                        $altStart = $b->alternative_start_date ? \Carbon\Carbon::parse($b->alternative_start_date) : null;

                        if ($b->status === 'arrived' && $altStart) {
                            if ($b->is_old_vehicle ?? true) {
                                $normalEnd = $altStart->copy()->subDay();
                                if ($normalEnd->gte($arrival)) {
                                    $startDay = $arrival->month == $month ? $arrival->day : 1;
                                    $endDay = $normalEnd->month == $month ? $normalEnd->day : $daysInMonth;
                                    for ($d = $startDay; $d <= $endDay; $d++) {
                                        $bookingMap[$d] = $b;
                                    }
                                }

                                $altRangeStart = $altStart->month == $month ? $altStart->day : 1;
                                $altRangeEnd = $departure->month == $month ? $departure->day : $daysInMonth;
                                for ($d = $altRangeStart; $d <= $altRangeEnd; $d++) {
                                    $bookingMap[$d] = (object)[
                                        'id' => 'alt-range-'.$b->id,
                                        'is_alt_range' => true,
                                        'booking' => $b
                                    ];
                                }
                            } else {
                                $altRangeStart = $altStart->month == $month ? $altStart->day : 1;
                                $altRangeEnd = $departure->month == $month ? $departure->day : $daysInMonth;
                                for ($d = $altRangeStart; $d <= $altRangeEnd; $d++) {
                                    $bookingMap[$d] = (object)[
                                        'id' => 'alt-range-'.$b->id,
                                        'is_alt_range' => true,
                                        'booking' => $b,
                                        'status' => $b->status, 
                                        'is_old_vehicle' => false
                                    ];
                                }
                            }
                            continue;
                        }

                        if ($b->status === 'rented' && $altStart) {
                            $startDay = $altStart->month == $month ? $altStart->day : 1;
                            $endDay = $departure->month == $month ? $departure->day : $daysInMonth;

                            for ($d = $startDay; $d <= $endDay; $d++) {
                                $bookingMap[$d] = $b;
                            }

                            continue;
                        }

                        /**
                         * NORMAL booking
                         */
                        $startDay = $arrival->month == $month ? $arrival->day : 1;
                        $endDay = $departure->month == $month ? $departure->day : $daysInMonth;

                        for ($d = $startDay; $d <= $endDay; $d++) {
                            $bookingMap[$d] = $b;
                        }
                    }

                    // Map frozen periods per vehicle
                    $freezeMap = [];
                    if($vehicle->freezes->count()) {
                        foreach($vehicle->freezes as $freeze) {
                            $freezeStart = \Carbon\Carbon::parse($freeze->start_date);
                            $freezeEnd = $freeze->end_date
                                ? \Carbon\Carbon::parse($freeze->end_date)
                                : $freezeStart->copy();
                           
                            if (
                                $freezeStart->year > $year ||
                                ($freezeStart->year == $year && $freezeStart->month > $month)
                            ) {
                                continue;
                            }
                            $startDay = ($freezeStart->year == $year && $freezeStart->month == $month)
                                ? $freezeStart->day
                                : null;

                            $endDay = ($freezeEnd->year == $year && $freezeEnd->month == $month)
                                ? $freezeEnd->day
                                : null;

                            if ($startDay === null && $endDay === null) {
                                continue;
                            }

                            $startDay = $startDay ?? 1;
                            $endDay   = $endDay ?? $daysInMonth;

                           for($d = $startDay; $d <= $endDay; $d++) {
                                $freezeMap[$d] = [
                                    'reason'     => $freeze->reason,
                                    'is_start'  => ($d == $startDay),
                                    'start_date'=> $freezeStart->format('Y-m-d'),
                                    'end_date'  => $freezeEnd->format('Y-m-d')
                                ];
                            }

                        }
                    }
                @endphp

                <tr class="{{ !$vehicle->can_interact ? 'vehicle-disabled' : '' }}">
                    <td class="vehicle-reg">{{ $vehicle->reg_no }}</td>
                    <td>
                        <span class="vehicle-make">{{ $vehicle->make }}</span>
                        <span class="vehicle-model">{{ $vehicle->model }}</span>
                    </td>
                
                    @php
                        $user = auth()->user();
                        $canManage = $user->hasRole(['admin', 'rent_a_car']);
                    @endphp

                    <td>
                        @if($vehicle->can_interact && $canManage)
                            <a href="{{ route('rentals.create', $vehicle->id) }}" class="btn btn-success btn-sm">Rent</a>
                        @else
                            <button class="btn btn-secondary btn-sm" disabled>Rent</button>
                        @endif
                    </td>

                    @php $currentDay = 1; @endphp
                    @while($currentDay <= $daysInMonth)
                        @php
                            $booking = $bookingMap[$currentDay] ?? null;
                            $currentDate = \Carbon\Carbon::create($year, $month, $currentDay);
                            $freezeInfo = $freezeMap[$currentDay] ?? null;
                        @endphp

                        @if($booking && isset($booking->is_alt_range))
                            @php
                                $colspan = 1;
                                while(isset($bookingMap[$currentDay + $colspan]) && isset($bookingMap[$currentDay + $colspan]->is_alt_range)) $colspan++;
                                if(($booking->status ?? null) === 'arrived' && ($booking->is_old_vehicle ?? true) === false) {
                                    $cellClass = 'arrived-booking';
                                } else {
                                    $cellClass = 'alternative-range';
                                }
                            @endphp

                            <td class="fw-bold booking-cell {{ $cellClass }}" colspan="{{ $colspan }}" data-bs-toggle="tooltip" title="Alternative vehicle arrived">
                                <span class="booking-number">{{ $booking->booking->booking_number ?? 'N/A' }}</span>
                            </td>

                            @php $currentDay += $colspan; @endphp
                            @continue
                        @endif

                        {{-- NORMAL BOOKING --}}
                        @if($booking)
                        @php
                            $arrival   = \Carbon\Carbon::parse($booking->arrival_date);
                            $departure = \Carbon\Carbon::parse($booking->departure_date);

                            $colspan = 1;
                            while(isset($bookingMap[$currentDay + $colspan]) && $bookingMap[$currentDay + $colspan]->id === $booking->id) {
                                $colspan++;
                            }

                            // Determine cell class
                            if ($booking->status === 'arrived') {
                                $hasInspection = $booking->inspections && $booking->inspections->count() > 0;

                                if ($hasInspection) {
                                    $cellClass = 'arrived-with-inspection';
                                    $canOpenModal = false;

                                } elseif (in_array($booking->repair_type, ['routine', 'emergency'])) {
                                    $cellClass = 'arrived-booking';
                                    $canOpenModal = false;

                                } else {
                                    $cellClass = 'current-booking';
                                    $canOpenModal = true;
                                }

                            } elseif ($booking->status === 'rented') {
                                $cellClass = 'current-booking';
                                $canOpenModal = true;
                            
                            } elseif ($booking->status === 'booked') {
                                $cellClass = 'future-booking'; 
                                $canOpenModal = true;

                            } elseif ($departure->lt($today)) {

                                $cellClass = 'past-booking';
                                $canOpenModal = true;

                            } elseif ($arrival->lte($today) && $departure->gte($today)) {

                                $cellClass = 'current-booking';
                                $canOpenModal = true;

                            } else {

                                $cellClass = 'future-booking';
                                $canOpenModal = true;
                            }

                            $creatorName = data_get($booking, 'creator.causer.name')
                                        ?? data_get($booking, 'creatorName.name')
                                        ?? 'System';  
                                                              
                            // $creatorName = $creatorName === 'N/A' ? 'System' : $creatorName;

                            $tooltipHtml =
                                '<strong>Customer:</strong> '.$booking->salutation.' '.$booking->driver_name.'<br>'.
                                '<strong>From:</strong> '.$arrival->format('d-m-Y').'<br>'.
                                '<strong>To:</strong> '.$departure->format('d-m-Y').'<br>'.
                                '<strong>Created By:</strong> '.$creatorName;

                                if ($booking->alternative_start_date) {
                                    $relatedBookings = \App\Models\Rental::where('booking_number', $booking->booking_number)
                                                        ->with('vehicle') 
                                                        ->get();

                                    if ($relatedBookings->count() === 2) {
                                        $originalVehicle = optional($relatedBookings[0]->vehicle)->reg_no ?? 'N/A';
                                        $altVehicle = optional($relatedBookings[1]->vehicle)->reg_no ?? 'N/A';

                                        $tooltipHtml .=
                                            '<br><strong>Note:</strong> Alternative vehicle added<br>' .
                                            '<strong>Alternative Start:</strong> ' . \Carbon\Carbon::parse($booking->alternative_start_date)->format('d-m-Y') . 
                                            '<br><strong>Reason:</strong> ' . e($booking->change_reason) .
                                            '<br><strong>Assigned Vehicle:</strong> ' . e("Vehicle {$originalVehicle} is assigned to Vehicle {$altVehicle}");
                                    }
                                }
                        @endphp

                      <td class="booking-cell {{ $cellClass }}" 
                        colspan="{{ $colspan }}"
                        data-booking-id="{{ $booking->id }}"
                        data-arrival="{{ $booking->arrival_date }}"
                        data-booking-status="{{ $booking->status }}"
                        data-departure-date="{{ \Carbon\Carbon::parse($booking->departure_date)->format('Y-m-d H:i') }}"
                        data-vehicle-reg="{{ $vehicle->reg_no }}"
                        @if($canOpenModal && $vehicle->can_interact && $canManage)
                            data-action="open-booking-modal"
                        @endif
                        data-bs-toggle="tooltip"
                        data-can-interact="{{ ($vehicle->can_interact && $canManage) ? 1 : 0 }}"
                        data-booked-ranges='@json(
                            $vehicle->rentals->map(fn($r) => [
                                "from" => \Carbon\Carbon::parse($r->arrival_date)->format("Y-m-d"),
                                "to"   => \Carbon\Carbon::parse($r->departure_date)->format("Y-m-d")
                            ])
                        )'
                        data-bs-html="true"
                        title="{{ $tooltipHtml }}">
                        <span class="booking-number">{{ $booking->booking_number }}</span>
                    </td>

                        @php $currentDay += $colspan; @endphp
                        @elseif($freezeInfo)
                            @php
                            $colspan = 1;
                            while(isset($freezeMap[$currentDay + $colspan])) {
                                $colspan++;
                            }

                            $freezeStartDate = \Carbon\Carbon::parse($freezeInfo['start_date'] ?? $currentDate)->format('d-m-Y');
                            $freezeEndDate = \Carbon\Carbon::parse($freezeInfo['end_date'] ?? $currentDate)->format('d-m-Y');

                            $tooltipHtml = "Frozen : {$freezeInfo['reason']}<br>From : {$freezeStartDate}<br>To : {$freezeEndDate}";
                        @endphp

                            <td class="booking-cell vehicle-frozen-date"
                                colspan="{{ $colspan }}"
                                @if($freezeInfo['is_start'])
                                    data-bs-toggle="tooltip"
                                    data-bs-html="true"
                                    title="Freezed Due to : {{$freezeInfo['reason']}} From: {{$freezeInfo['start_date']}} To: {{$freezeInfo['end_date']}}"
                                @endif>
                                @if($freezeInfo['is_start'])
                                    <div><span>Freezed Due To : {{ $freezeInfo['reason'] }}</span></div>
                                @endif
                            </td>

                            @php $currentDay += $colspan; @endphp
                        @else
                            <td class="booking-cell {{ $currentDate->lt($today) ? 'past-date' : '' }}"></td>
                            @php $currentDay++; @endphp
                        @endif
                    @endwhile
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
