<x-app-layout>
    <style>
        body {
            background-color: #f4f6f9; 
        }

        .dashboard-container {
            max-width: 1200px;
            margin: auto;
        }

        .card-dashboard {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .card-dashboard h5 {
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .expiry-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .expiry-table thead th {
            background: #472764c6;
            color: #fff;
            font-weight: 600;
            text-align: center;
        }

        .expiry-table tbody tr:hover {
            background-color: #f0f4f8;
            transform: scale(1.01);
            transition: all 0.2s ease-in-out;
        }

        .badge-urgency {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            font-weight: 600;
            margin-left: 5px;
            border-radius: 8px;
        }

        .expiry-date {
            font-weight: 500;
        }

        .today-label {
            font-size: 1.5rem;
            color: #2d2d2d;
            margin-bottom: 1.5rem;
        }

        .section-header {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            background: #329374da;
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>

    @if(auth()->user()->hasPermission('manage_general-manager'))
        <div class="dashboard-container py-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white fw-bold">
                    👥 Users Logged in Today
                </div>
                <div class="card-body">
                    @if($loggedInToday->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>#</th>
                                        <th>Logged In User</th>
                                        <th>Login Date</th>
                                        <th>Login Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loggedInToday as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <i class="bi bi-person-check-fill text-success me-2"></i>
                                                <strong>{{ $user->name }}</strong>
                                            </td>
                                            <td>{{ $user->last_login_at->format('Y-m-d') }}</td>
                                            <td>{{ $user->last_login_at->format('H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No users logged in today.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_general-manager'))
        <div class="dashboard-container">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white fw-bold">
                    🧾 Recent User Activity
                </div>
                <div class="card-body">
                    @if($recentActivity->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle" id="activityTable">
                                <thead class="table-success">
                                    <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Date & Time</th>
                                    </tr>
                                </thead>
                                 <tbody>
                                    @foreach($recentActivity as $index => $log)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ optional($log->causer)->name ?? 'System' }}</td>
                                            <td>{{ $log->description }}</td>
                                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No recent activity.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($pendingReportsCount > 0)
        <a href="{{ route('reports.index') }}" class="text-decoration-none text-dark">
            <div class="dashboard-container card-dashboard p-3 mb-4 shadow-sm rounded bg-warning bg-opacity-25">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1 fw-bold">
                            New Reports Received
                        </h5>
                        <p class="mb-0 text-muted">
                            You have {{ $pendingReportsCount }} report{{ $pendingReportsCount > 1 ? 's' : '' }} awaiting your review.
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-danger fs-5 px-3 py-2">
                            {{ $pendingReportsCount }}
                        </span>

                        <span class="btn btn-outline-dark fw-semibold">
                            View
                        </span>
                    </div>
                </div>
            </div>
        </a>
    @endif

    @if(auth()->user()->hasPermission('manage_dashboard'))
        <div class="dashboard-container mt-5">
            <h5 class="my-2 fw-bold text-center">🚗 Vehicle Licenses Expiry Dashboard</h5>
            <p class="fw-bold text-center text-lg">📅 Today: {{ now()->format('F j, Y') }}</p>

            @php
                $today = now()->startOfDay();
                $oneMonth = $today->copy()->addMonth()->endOfDay();
                $twoMonths = $today->copy()->addMonths(2)->endOfDay();
            @endphp

            {{-- Expiry within 1 Month --}}
            <div class="card-dashboard">
                <span class="section-header">⚠️ Expiry within 1 Month</span>
                <div class="table-responsive mt-3">
                    <table class="table expiry-table table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th>Vehicle Reg No</th>
                                <th>Insurance Expiry</th>
                                <th>Emission Test Expiry</th>
                                <th>Revenue License Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringWithinOneMonth as $vehicle)
                                @php
                                    $expiries = [
                                        'Insurance' => $vehicle->insurance_expiry,
                                        'Emission Test' => $vehicle->emission_test_expiry,
                                        'Revenue License' => $vehicle->revenue_license_expiry,
                                    ];
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $vehicle->reg_no }}</td>
                                    @foreach($expiries as $type => $date)
                                        @if($date && \Carbon\Carbon::parse($date)->between($today, $oneMonth))
                                            @php
                                                $daysLeft = $today->diffInDays(\Carbon\Carbon::parse($date));
                                                if ($daysLeft <= 7) {
                                                    $badgeClass = 'bg-danger text-white';
                                                } elseif ($daysLeft <= 15) {
                                                    $badgeClass = 'bg-warning text-dark';
                                                } else {
                                                    $badgeClass = 'bg-success text-white';
                                                }
                                            @endphp
                                            <td>
                                                <span class="expiry-date">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</span>
                                                <span class="badge badge-urgency {{ $badgeClass }}">
                                                    {{ $daysLeft }} days
                                                </span>
                                            </td>
                                        @else
                                            <td>-</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">🎉 No expiries within the next month</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expiry in 1–2 Months --}}
            <div class="card-dashboard">
                <span class="section-header">📅 Expiry in next 2 Months</span>
                <div class="table-responsive mt-3">
                    <table class="table expiry-table table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th>Vehicle Reg No</th>
                                <th>Insurance Expiry</th>
                                <th>Emission Test Expiry</th>
                                <th>Revenue License Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringInNextTwoMonths as $vehicle)
                                @php
                                    $expiries = [
                                        'Insurance' => $vehicle->insurance_expiry,
                                        'Emission Test' => $vehicle->emission_test_expiry,
                                        'Revenue License' => $vehicle->revenue_license_expiry,
                                    ];
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $vehicle->reg_no }}</td>
                                    @foreach($expiries as $type => $date)
                                        @if($date && \Carbon\Carbon::parse($date)->between($oneMonth->copy()->addDay(), $twoMonths))
                                            @php
                                                $daysLeft = $today->diffInDays(\Carbon\Carbon::parse($date));
                                                $badgeClass = 'bg-info text-white';
                                            @endphp
                                            <td>
                                                <span class="expiry-date">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</span>
                                                <span class="badge badge-urgency {{ $badgeClass }}">
                                                    {{ $daysLeft }} days
                                                </span>
                                            </td>
                                        @else
                                            <td>-</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">🎉 No expiries in the upcoming 2 months</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_garage-reports'))
        <div class="dashboard-container card-dashboard p-3 shadow-sm rounded bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-header fw-bold">📄 Inspections Received by Garage</span>
                <span class="badge bg-primary fs-6">{{ $sentToGarage->count() }} Inspections</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle" id="garageInspections">
                    <thead class="table-dark">
                        <tr>
                            <th>🆔 Inspection ID</th>
                            <th>📝 Job Code</th>
                            <th>🚗 Vehicle</th>
                            <th>📅 Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sentToGarage as $inspection)
                            <tr class="align-middle">
                                <td class="fw-bold text-primary">00{{ $inspection->id }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $inspection->job_code ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <i class="bi bi-car-front-fill me-1"></i>
                                    {{ $inspection->vehicle->reg_no ?? '-' }}
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $inspection->created_at->format('Y-m-d') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No inspections received by the garage yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_procurements'))
        <div class="dashboard-container card-dashboard mt-5 p-3 shadow-sm rounded bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-header fw-bold">📦 Recent Procurements</span>
                <span class="badge bg-success fs-6">{{ $procurements->count() }} Procurements</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>🆔 Procurement ID</th>
                            <th>📝 Inspection ID</th>
                            <th>📦 Inventory</th>
                            <th>💰 Price</th>
                            <th>📅 Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($procurements as $procurement)
                            <tr class="align-middle">
                                <td class="fw-bold text-primary">00{{ $procurement->id }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        00{{ $procurement->inspection_id }}
                                    </span>
                                </td>
                                <td>{{ $procurement->issueInventory->inventory->name ?? '-' }}</td>
                                <td>{{ $procurement->price ? number_format($procurement->price, 2) : '-' }}</td>
                                <td>
                                    <span class="text-muted small">{{ $procurement->created_at->format('Y-m-d') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No procurements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_payment_cordinator'))
        <div class="dashboard-container card-dashboard mt-5 p-3 shadow-sm rounded bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-header fw-bold">💵 Rental Payments</span>
                <span class="badge bg-success fs-6">{{ $cashiers->count() }} Rentals</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle" id="rentalPayments">
                    <thead class="table-dark">
                        <tr>
                            <th>🆔 Rental ID</th>
                            <th>🚗 Vehicle</th>
                            <th>📅 Due Date</th>
                            <th>💰 Amount</th>
                            <th>📌 Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashiers as $cashier)
                            <tr class="align-middle">
                                <td class="fw-bold text-primary">00{{ $cashier->id }}</td>
                                <td>
                                    <i class="bi bi-car-front-fill me-1"></i>
                                    {{ $cashier->vehicle->reg_no ?? '-' }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($cashier->due_date)->format('Y-m-d') }}
                                </td>
                                <td>
                                    {{ number_format($cashier->amount, 2) }}
                                </td>
                                <td>
                                    @php
                                        $statusColors = ['send_to_fm' => 'bg-warning text-dark','noted' => 'bg-info text-white',null => 'bg-secondary text-white'];
                                    @endphp
                                    <span class="badge {{ $statusColors[$cashier->status] ?? 'bg-secondary text-white' }}">
                                        {{ $cashier->status ? ucfirst(str_replace('_',' ', $cashier->status)) : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No rental payments recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_rent_a_car') || auth()->user()->hasPermission('manage_general-manager'))
        <div class="dashboard-container card-dashboard mt-5 p-3 shadow-sm rounded bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-header fw-bold">🚐 Active Rentals</span>
                <span class="badge bg-success fs-6">{{ $rentals->count() }} Rentals</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle" id="activeRentals">
                    <thead class="table-dark">
                        <tr>
                            <th>🆔 Rental ID</th>
                            <th>🚗 Vehicle</th>
                            <th>👤 Driver</th>
                            <th>📅 Rent Start Date</th>
                            <th>📅 Rent End Date</th>
                            <th>👥 Passengers</th>
                            <th>📌 Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentals as $rental)
                            <tr class="align-middle">
                                <td class="fw-bold text-primary">00{{ $rental->id }}</td>
                                <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                                <td>{{ $rental->driver_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($rental->arrival_date)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($rental->departure_date)->format('Y-m-d') }}</td>
                                <td>{{ $rental->passengers }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'rented' => 'bg-warning text-dark',
                                            'arrived' => 'bg-primary text-white',
                                            'completed' => 'bg-success text-white'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$rental->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($rental->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No active rentals found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasPermission('manage_accountant'))
        <div class="dashboard-container card-dashboard mt-5 p-3 shadow-sm rounded bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-header fw-bold">💼 Accountant Reviews</span>
                <span class="badge bg-success fs-6">{{ $accountantReviews->count() }} Reviews</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>🆔 Review ID</th>
                            <th>📝 Inspection ID</th>
                            <th>📦 Procurement ID</th>
                            <th>💳 Type</th>
                            <th>📌 Status</th>
                            <th>📅 Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountantReviews as $review)
                            <tr class="align-middle">
                                <td class="fw-bold text-primary">00{{ $review->id }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        00{{ $review->inspection_id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        00{{ $review->procurement_id }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($review->types) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-secondary text-white',
                                            'send_to_fm' => 'bg-warning text-dark',
                                            'send_to_procurement' => 'bg-primary text-white',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$review->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst(str_replace('_',' ', $review->status)) }}
                                    </span>
                                </td>
                                <td>{{ $review->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No accountant reviews recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        const tables = ['#activityTable', '#garageInspections', '#activeRentals', '#rentalPayments'];

        tables.forEach(function(selector) {
            $(selector).DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                order: [[3, 'desc']], 
                columnDefs: [
                    { orderable: false, targets: 0 } 
                ]
            });
        });
    });
</script>

</x-app-layout>
