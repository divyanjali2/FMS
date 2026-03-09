@extends('layouts.app')
@section('content')
<div class="container py-4">
    {{-- Fleet Decisions Table --}}
    <div class="table-responsive card shadow-sm mb-4">
        <div class="card-header bg-success text-white fw-bold">Fleet Decisions</div>
        <div class="card-body">
            <table class="table table-bordered" id="fleetDecisionsTable">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Fleet Insp Date</th>
                        <th>Maintenance Insp Date</th>
                        <th>Vehicle</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                        @php
                            $sortedGroups = $reports->groupBy('inspection_id')
                                ->sortBy(function ($group) {
                                    $status = $group->first()->status;

                                    $isEyeRow = in_array($status, [
                                        'owner_repair',
                                        'owner_repair_done',
                                        'sent_to_garage',
                                        'sent_back_to_garage',
                                    ]);

                                    return $isEyeRow ? 1 : 0;
                                });
                        @endphp

                        @forelse($sortedGroups as $inspectionId => $groupedReports)
                            @php
                                $status = $groupedReports->first()->status;
                            @endphp

                        <tr>
                            <td>{{ $groupedReports->first()->inspection->job_code }}</td>
                            <td>{{ optional($groupedReports->first()->inspection->created_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ optional($groupedReports->first()->updated_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $groupedReports->first()->inspection->vehicle->reg_no }}</td>
                            <td>
                                @if($status === 'sent_to_fleet')
                                    <a href="{{ route('fleet-decisions.show', $groupedReports->first()) }}" class="btn btn-info btn-sm">
                                        Review
                                    </a>
                                    <button type="button" class="btn btn-warning btn-sm owner-repair-btn"
                                            data-id="{{ $groupedReports->first()->id }}"
                                            @if($status !== 'sent_to_fleet') disabled @endif>
                                        Owner Repair
                                    </button>

                                @elseif(in_array($status, ['owner_repair', 'owner_repair_done']))
                                    <button type="button" class="btn btn-warning btn-sm" disabled>
                                        Owner Repair
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#inspectionModal{{ $groupedReports->first()->inspection_id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @else
                                    <a href="{{ route('fleet-decisions.view', $groupedReports->first()) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>

                            <!-- Modal -->
                            <div class="modal fade" id="inspectionModal{{ $groupedReports->first()->inspection_id }}" tabindex="-1"
                                aria-labelledby="inspectionModalLabel{{ $groupedReports->first()->inspection_id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"
                                                id="inspectionModalLabel{{ $groupedReports->first()->inspection_id }}">
                                                Inspection Details - #00{{ $groupedReports->first()->inspection_id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="fw-bold mb-3">Vehicle Details</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <p><strong>Reg No:</strong> {{ $groupedReports->first()->inspection->vehicle->reg_no ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <p><strong>Make:</strong> {{ $groupedReports->first()->inspection->vehicle->make ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <p><strong>Model:</strong> {{ $groupedReports->first()->inspection->vehicle->model ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <p><strong>Status:</strong> {{ \Illuminate\Support\Str::title(str_replace('_',' ',$status)) }}</p>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <p><strong>Created At:</strong> {{ $groupedReports->first()->created_at->format('d M Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold mb-3">Faults</h6>
                                                    @if($groupedReports->first()->inspection->faults->count())
                                                        <ul class="list-group mb-3">
                                                            @foreach($groupedReports->first()->inspection->faults as $fault)
                                                                <li class="list-group-item">{{ $fault->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted">No faults recorded for this inspection.</p>
                                                    @endif
                                                </div>

                                                <div class="col-md-6">
                                                    <h6 class="fw-bold mb-3">Garage Issues</h6>
                                                    @if($groupedReports->first()->inspection->garageReports->count())
                                                        @foreach($groupedReports->first()->inspection->garageReports as $gReport)
                                                            @if($gReport->issue)
                                                                <div class="mb-2 p-2 border rounded">
                                                                    <p>{{ $gReport->issue->name }}</p>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <p class="text-muted">No garage issues recorded.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </tr>
                    @empty
                        {{-- no rows --}}
                    @endforelse
                </tbody>
            </table>
            {{-- {{ $reports->links() }} --}}
        </div>
    </div>

    {{-- Approval For Maintenance Team Table --}}
    <div class="table-responsive card shadow-sm mb-4">
        <div class="card-header bg-info text-dark fw-bold">Approval For Maintenance Team</div>
        <div class="card-body">
            <table class="table table-bordered" id="approvalForMaintenance">
                <thead>
                    <tr>
                        <th>Inspection Id</th>
                        <th>Job Code</th>
                        <th>Vehicle</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceTeam->groupBy('inspection_id') as $inspectionId => $reviews)
                        @php
                            $inspection = $reviews->first()->inspection;
                        @endphp
                        <tr>
                            <td>00{{ $inspection->id ?? '-' }}</td>
                            <td>{{ $inspection->job_code ?? '-' }}</td>
                            <td>{{ $inspection->vehicle->reg_no ?? '-' }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#accountantModal{{ $inspection->id }}">
                                    {{ in_array($inspection->id, $approvals) ? 'View' : 'Review' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="3" class="text-center text-muted">No work pending review.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
            {{-- {{ $maintenanceTeam->links('pagination::bootstrap-5') }} --}}

            {{-- Modals outside the table --}}
           @foreach($maintenanceTeam->groupBy('inspection_id') as $inspectionId => $reviews)
                @php
                    $inspection = $reviews->first()->inspection;
                    $inspectionProcurements = $procurements[$inspection->id] ?? collect();
                @endphp

                <div class="modal fade" id="accountantModal{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Inspection #{{ $inspection->job_code }} - Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6 class="fw-bold">Inspection Info</h6>
                                <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>
                                <p><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>

                                <h6 class="fw-bold mt-3">Work Details</h6>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Inventory</th>
                                            <th>Issue / Fault</th>
                                            <th>Status</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                  <tbody>
                                        @forelse($inspectionProcurements as $proc)
                                            @php
                                                $gi = $proc->issueInventory->garageInbuildIssue;
                                                $issueName = $gi && $gi->issue ? $gi->issue->name : null;
                                                $faultName = $gi && $gi->fault ? $gi->fault->name : null;
                                                $issueFault = collect([$issueName, $faultName])->filter()->implode(' / ');
                                            @endphp
                                            <tr>
                                                <td>{{ $proc->issueInventory->inventory->name ?? '-' }}</td>
                                                <td>{{ $issueFault ?: '' }}</td>
                                                <td>{{ ucfirst($proc->status) }}</td>
                                                <td>{{ $proc->fulfilled_qty ?? '-' }}</td>
                                                <td>{{ $proc->price ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No procurements found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                                @unless(in_array($inspection->id, $approvals))
                                    <form action="{{ route('fm-work-decisions.approve', $inspection->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @foreach($inspectionProcurements as $proc)
                                            <input type="hidden" name="issue_inventory_ids[]" value="{{ $proc->issueInventory->id }}">
                                        @endforeach
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Fleet Post-Checks Table --}}
    <div class="table-responsive card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white fw-bold">Fleet Post-Checks (Send to Fleet Manager)</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Job</th>
                            <th>Vehicle</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedPostChecks = $postChecks->groupBy('inspection_id');
                        @endphp
                        @forelse($groupedPostChecks as $inspectionId => $checks)
                            @php
                                $firstCheck = $checks->first();
                                $released = \App\Models\FleetVehicleRelease::whereHas('fleetPostCheck', function($query) use ($checks) {
                                    $query->whereIn('id', $checks->pluck('id'));
                                })->where('status', 'vehicle_release')->exists();
                            @endphp
                            <tr>
                                <td>{{ $firstCheck->inspection->job_code }}</td>
                                <td>{{ $firstCheck->inspection->vehicle->reg_no }}</td>
                                <td>
                                    <a href="{{ route('fleet-vehicle-release.show', $inspectionId) }}" class="btn btn-success btn-sm {{ $released ? 'disabled' : '' }}" {{ $released ? 'aria-disabled=true tabindex=-1' : '' }}>
                                        <i class="bi bi-eye"></i> Complete
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No post-checks pending.</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
                {{ $postChecks->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <!-- Owner Repairs -->
        <div class="table-responsive card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark fw-bold">Owner Repairs</div>
            <div class="card-body">
                <table class="table table-bordered table-hover rounded" id="ownerRepairsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ownerRepairs->groupBy('inspection_id') as $inspectionId => $groupedReports)
                            @php
                                $report = $groupedReports->first();
                            @endphp
                            <tr>
                                <td>{{ $report->inspection->vehicle->reg_no ?? '-' }}</td>
                                <td>{{ $report->inspection->vehicle->vehicleType->type_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ ucfirst(str_replace('_',' ', $report->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $isDone = $report->status === 'owner_repair_done';
                                    @endphp
                                    <button class="btn btn-success btn-sm ownerRepairBtn" data-id="{{ $report->inspection_id }}"
                                        {{ $isDone ? 'disabled' : '' }}>
                                        {{ $isDone ? 'Received' : 'Mark as Received' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cashier Payments (Send to FM) -->
        <div class="table-responsive card shadow-sm mt-4">
            <div class="card-header bg-primary text-white fw-bold">Cashier Payments (Send to FM)</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Cashier Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashiers as $cashier)
                                @php
                                    $paymentCoordinator = \App\Models\PaymentCoordinator::where('cashier_id', $cashier->id)->first();
                                @endphp
                                <tr>
                                    <td>{{ $cashier->vehicle->reg_no ?? '-' }}</td>
                                    <td>{{ number_format($cashier->amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('fleet-decisions.paymentPage', $cashier->id) }}" class="btn btn-primary btn-sm
                                        @if($paymentCoordinator && !empty($paymentCoordinator->status)) disabled @endif">
                                            Process Payment
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No cashier records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $cashiers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#fleetDecisionsTable').DataTable({
            paging: true,        
            ordering: false,    
            info: false,         
            lengthChange: true,  
            searching: true   
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#approvalForMaintenance').DataTable({
            paging: true,        
            ordering: false,    
            info: false,         
            lengthChange: true,  
            searching: true   
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let actionCallback = null;
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        const messageEl = document.getElementById('confirmationMessage');
        const confirmBtn = document.getElementById('confirmActionBtn');

        const buttons = document.querySelectorAll('.ownerRepairBtn, .owner-repair-btn');

        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const reportId = this.dataset.id;
                const isOwnerRepair = this.classList.contains('owner-repair-btn');
                const actionName = isOwnerRepair ? 'Owner Repair' : 'Mark as Received';

                // Set modal message
                messageEl.textContent = `Are you sure you want to mark this as ${actionName}?`;

                // Define action for confirm button
                actionCallback = () => {
                    this.disabled = true;
                    this.textContent = actionName;

                    fetch("{{ route('fleet-decisions.store.ownerRepair') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            garage_report_id: reportId,
                            [isOwnerRepair ? 'owner_repair' : 'owner_repair_done']: true
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            const badge = this.closest('tr').querySelector('span.badge');
                            if(badge){
                                if(isOwnerRepair){
                                    badge.innerText = 'Owner Repair';
                                    badge.classList.remove('bg-success');
                                    badge.classList.add('bg-warning', 'text-dark');
                                } else {
                                    badge.innerText = 'Owner Repair Done';
                                    badge.classList.remove('bg-warning');
                                    badge.classList.add('bg-success', 'text-white');
                                }
                            }
                            location.reload(); // optional: remove if you want fully dynamic update
                        } else {
                            alert(data.message || 'Something went wrong.');
                            this.disabled = false;
                            this.textContent = isOwnerRepair ? 'Owner Repair' : 'Mark as Received';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Something went wrong.');
                        this.disabled = false;
                        this.textContent = isOwnerRepair ? 'Owner Repair' : 'Mark as Received';
                    });
                };

                // Show modal
                confirmationModal.show();
            });
        });

        // Confirm button click
        confirmBtn.addEventListener('click', function() {
            if(actionCallback) actionCallback();
            confirmationModal.hide();
        });
    });
</script>

@endsection

