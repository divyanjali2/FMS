<div class="modal fade" id="dailyRecordModal" tabindex="-1" aria-labelledby="dailyRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="dailyRecordModalLabel">Daily Vehicle Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- Today Arrivals --}}
                <h6 class="fw-bold text-success mb-2">
                    🚗 Today Arrivals ({{ count($todayArrivals) }})
                </h6>                
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered align-middle" id="todayArrivalsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Booking No</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Company</th>
                                <th>Arrival Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayArrivals as $rental)
                                <tr>
                                    <td>{{ $rental->booking_number ?? '-' }}</td>
                                    <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                                    <td>{{ $rental->salutation ?? '-' }} {{ $rental->driver_name ?? '-' }}</td>
                                    <td>{{ $rental->vehicle->vehicleType->type_name ?? '-' }}</td>
                                    <td>{{ $rental->company->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($rental->arrival_date)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                {{-- <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No arrivals today
                                    </td>
                                </tr> --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Today Departures --}}
                <h6 class="fw-bold text-danger mb-2">🚙 Today Departures ({{ count($todayDepartures) }})</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" id="todayDeparturesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Booking No</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Company</th>
                                <th>Departure Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayDepartures as $rental)
                                @php
                                    $departure =
                                        $rental->emer_departure_date ?? $rental->departure_date;
                                @endphp
                                <tr>
                                    <td>{{ $rental->booking_number ?? $rental->emer_booking_number ?? '-' }}</td>
                                    <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                                    <td>{{ $rental->salutation ?? '-' }} {{ $rental->driver_name ?? '-' }}</td>
                                    <td>{{ $rental->vehicle->vehicleType->type_name ?? '-' }}</td>
                                    <td>{{ $rental->company->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($departure)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                {{-- <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No departures today
                                    </td>
                                </tr> --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let arrivalsTable, departuresTable;
        $('#dailyRecordModal').on('shown.bs.modal', function () {

            if (!$.fn.DataTable.isDataTable('#todayArrivalsTable')) {
                arrivalsTable = $('#todayArrivalsTable').DataTable({
                    pageLength: 5,
                    lengthChange: false,
                    ordering: true,
                    searching: true,
                    info: false,
                    autoWidth: false 
                });
            }

            if (!$.fn.DataTable.isDataTable('#todayDeparturesTable')) {
                departuresTable = $('#todayDeparturesTable').DataTable({
                    pageLength: 5,
                    lengthChange: false,
                    ordering: true,
                    searching: true,
                    info: false,
                    autoWidth: false
                });
            }

            arrivalsTable?.columns.adjust();
            departuresTable?.columns.adjust();
        });
    });
</script>