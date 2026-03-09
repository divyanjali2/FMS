@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h4 class="fw-bold mb-0">Inspections Received to Garage</h4>
    </div>

    <div class="table-responsive card-body">
        @php
            $pendingReports = $reports->filter(function($inspectionReports) {
                $statuses = $inspectionReports->pluck('status')->unique();
                return in_array('pending', $statuses->toArray());
            });
        @endphp

        @if($pendingReports->isNotEmpty())
        <table class="table table-bordered" id="pendingReportsTable">
                <thead>
                    <tr>
                        <th>Job Code</th>
                        <th>Vehicle</th>
                        <th>Issues Identified by Garage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReports as $inspectionId => $inspectionReports)
                        @php
                            $inspection = $inspectionReports->first()->inspection;
                        @endphp
                        <tr>
                            <td>{{ $inspection->job_code }}</td>
                            <td>{{ $inspection->vehicle->reg_no }}</td>
                            <td>
                                @foreach($inspectionReports as $report)
                                    <span class="badge bg-primary">{{ $report->issue->name ?? '-' }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge bg-secondary">Pending</span></td>
                            <td>
                                <a href="{{ route('garage_reports.edit', $inspectionReports->first()->id) }}" class="btn btn-sm btn-success">
                                    Edit
                                </a>
                                <a href="{{ route('garage_reports.show', $inspectionId) }}" class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mt-2">No pending inspections found.</p>
        @endif

        <hr>
        @php
            $nonPendingReports = $reports->filter(function($inspectionReports) {
                $statuses = $inspectionReports->pluck('status')->unique();
                return !in_array('pending', $statuses->toArray());
            });
        @endphp

        @if($nonPendingReports->isNotEmpty())
            <h5 class="mb-3 fw-bold">Completed / Sent to Fleet Manager</h5>
            <div class="table-responsive">
            <table class="table table-bordered" id="completedReportsTable">
                    <thead>
                        <tr>
                            <th>Job Code</th>
                            <th>Vehicle</th>
                            <th>Issues Identified by Garage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach($nonPendingReports as $inspectionId => $inspectionReports)
    @php
        $inspection = $inspectionReports->first()->inspection;
        $statuses = $inspectionReports->pluck('status')->unique();
    @endphp

    @if(!$statuses->contains('disappear'))
        <tr>
            <td>{{ $inspection->job_code }}</td>
            <td>{{ $inspection->vehicle->reg_no }}</td>
            <td>
                @foreach($inspectionReports as $report)
                    <span class="badge bg-primary">
                        {{ $report->issue->name ?? '-' }}
                    </span>
                @endforeach
            </td>
            <td>
                <span class="badge bg-success">
                    Send to Fleet Manager
                </span>
            </td>
            <td>
                <a href="{{ route('garage_reports.show', $inspectionId) }}"
                   class="btn btn-md btn-info fw-bold">
                    View
                </a>
            </td>
        </tr>
    @endif
@endforeach

                    </tbody>
                </table>
                {{-- {{ $reportsPaginated->appends(['fleet_page' => request('fleet_page')])->links() }} --}}
            </div>
        @endif
        </div>
    </div>

    <div class="row">
        {{-- === Fleet Decisions Table === --}}
        <div class="col-12">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-dark">
                    <h4 class="fw-bold mb-0">Fleet Manager Decisions (Inbuild Only)</h4>
                </div>
                <div class="table-responsive card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Job Code</th>
                                <th>Vehicle</th>
                                <th>Issues</th>
                                <th>Decision</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fleetDecisions->sortByDesc(fn($decisions) => $decisions->first()->garageReport->created_at) as $garageReportId => $decisions)
                                @php
                                    $garageReport = $decisions->first()->garageReport;
                                    $inspection = $garageReport->inspection;
                                    $garageReport->inbuildIssues = \DB::table('garage_inbuild_issues')
                                        ->where('garage_report_id', $garageReport->id)
                                        ->get();
                                @endphp
                                <tr>
                                    <td>{{ $inspection->job_code }}</td>
                                    <td>{{ $inspection->vehicle->reg_no }}</td>
                                    <td>
                                        @foreach($garageReport->inbuildIssues as $inbuild)
                                            <div class="mb-1">
                                                @if($inbuild->issue_id)
                                                    <span class="badge bg-info">{{ \App\Models\Issue::find($inbuild->issue_id)->name }}</span>
                                                @endif
                                                @if($inbuild->fault_id)
                                                    <span class="badge bg-secondary">{{ \App\Models\Fault::find($inbuild->fault_id)->name }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Inbuild</span>
                                    </td>
                                    <td>
                                        @if($garageReport->status === 'sent_back_to_garage')
                                            <button class="btn btn-md btn-warning" disabled>
                                                Edit
                                            </button>
                                        @else
                                            <a href="#" class="btn btn-md btn-warning assign-inventory-btn" data-bs-toggle="modal" data-bs-target="#assignInventoryModal-{{ $garageReport->id }}">
                                                Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No inbuild fleet decisions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- {{ $fleetDecisionsPaginated->appends(['reports_page' => request('reports_page')])->links() }} --}}
                </div>
            </div>
        </div>

        {{-- === FM Approved Jobs Table === --}}
        <div class="col-12">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="fw-bold mb-0">FM Approved Jobs (Start Work)</h4>
                </div>
                <div class="table-responsive card-body">
                    @if($fmApproved->isNotEmpty())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Job Code</th>
                                    <th>Vehicle</th>
                                    <th>Procurements / Issues & Faults</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fmApproved as $inspectionId => $decisions)
                                    @php
                                        $inspection = $decisions->first()->inspection;
                                        $inbuildItems = $fmInbuildIssues[$inspectionId] ?? collect();

                                        // Work status progress check
                                        $allStatuses = \App\Models\GMWorkStatus::where('inspection_id', $inspectionId)
                                            ->pluck('status')
                                            ->toArray();

                                        $allDone = !empty($allStatuses)
                                            && count($allStatuses) === $inbuildItems->count()
                                            && collect($allStatuses)->every(fn($s) => $s === 'work_done');
                                    @endphp
                                    <tr>
                                        <td>{{ $inspection->job_code }}</td>
                                        <td>{{ $inspection->vehicle->reg_no }}</td>
                                        <td>
                                            @php
                                                $issues = $inbuildItems->pluck('issue_id')->filter()->unique();
                                                $faults = $inbuildItems->pluck('fault_id')->filter()->unique();
                                            @endphp

                                            @foreach($issues as $issueId)
                                                <span class="badge bg-info">{{ \App\Models\Issue::find($issueId)->name }}</span>
                                            @endforeach

                                            @foreach($faults as $faultId)
                                                <span class="badge bg-secondary">{{ \App\Models\Fault::find($faultId)->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($allDone)
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#workCompletedModal-{{ $inspectionId }}">
                                                    Work Completed
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#workStartedModal-{{ $inspectionId }}">
                                                    Update Status
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Work Started Modal --}}
                                    <div class="modal fade" id="workStartedModal-{{ $inspectionId }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-wrench-adjustable-circle me-2"></i>
                                                        Update Work Status - Inspection {{ $inspection->job_code }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <form action="{{ route('gm.workStartedMultiple', $inspectionId) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <p><strong>Inspection:</strong> {{ $inspection->job_code }}</p>
                                                            <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no }}</p>
                                                        </div>

                                                        @foreach($inbuildItems as $item)
                                                            @php
                                                                $workStatus = \App\Models\GMWorkStatus::where('issue_inventory_id', $item->inbuild_id)
                                                                    ->where('inspection_id', $inspection->id)
                                                                    ->latest()
                                                                    ->first();
                                                            @endphp

                                                            <div class="card mb-3 shadow-sm">
                                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        @if($item->issue_id)
                                                                            <i class="bi bi-exclamation-circle-fill text-danger me-1"></i>
                                                                            <strong>Issue:</strong> {{ \App\Models\Issue::find($item->issue_id)->name }}
                                                                        @elseif($item->fault_id)
                                                                            <i class="bi bi-tools text-warning me-1"></i>
                                                                            <strong>Fault:</strong> {{ \App\Models\Fault::find($item->fault_id)->name }}
                                                                        @endif
                                                                    </div>
                                                                    <span class="badge 
                                                                        @if($workStatus && $workStatus->status === 'work_done') 
                                                                            bg-success 
                                                                        @elseif($workStatus && $workStatus->status === 'in_progress') 
                                                                            bg-primary 
                                                                        @else 
                                                                            bg-secondary 
                                                                        @endif">
                                                                        {{ ucfirst(str_replace('_', ' ', $workStatus->status ?? 'Pending')) }}
                                                                    </span>
                                                                </div>

                                                                <div class="card-body">
                                                                    <p>
                                                                        <strong>Assigned Inventory:</strong> {{ $item->inventory_name ?? '-' }} | 
                                                                        <strong>Quantity:</strong> {{ $item->quantity ?? '-' }}
                                                                    </p>

                                                                    @if(!$workStatus || $workStatus->status !== 'work_done')
                                                                        <label class="form-label fw-bold">Set Status</label>
                                                                        <select name="statuses[{{ $item->inbuild_id }}]" class="form-select mb-2" required>
                                                                            <option value="">-- Select Status --</option>
                                                                            <option value="in_progress" {{ $workStatus && $workStatus->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                                            <option value="work_done">Completed</option>
                                                                        </select>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle me-1"></i> Close
                                                        </button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-save me-1"></i> Save All
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Work Completed Modal --}}
                                    <div class="modal fade" id="workCompletedModal-{{ $inspectionId }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Work Completed - {{ $inspection->job_code }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Inspection:</strong> {{ $inspection->job_code }}</p>
                                                    <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no }}</p>
                                                    <hr>
                                                    @foreach($inbuildItems as $item)
                                                        <p>
                                                            @if($item->issue_id)
                                                                <strong>Issue:</strong> {{ \App\Models\Issue::find($item->issue_id)->name }}
                                                            @elseif($item->fault_id)
                                                                <strong>Fault:</strong> {{ \App\Models\Fault::find($item->fault_id)->name }}
                                                            @endif
                                                            <span class="badge bg-success float-end">Completed</span>
                                                        </p>
                                                    @endforeach
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mt-2">No FM-approved jobs available for work.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- === Pending Fleet Post Checks Table === --}}
        <div class="col-12 mt-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="fw-bold mb-0">Repairs Below Standard</h4>
                </div>
                <div class="table-responsive card-body">
                    @if($pendingPostChecks->isNotEmpty())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Job Code</th>
                                    <th>Vehicle</th>
                                    <th>Not Fixed</th>
                                    <th>Verified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPostChecks as $inspectionId => $checks)
                                    @php
                                        $inspection = $checks->first()->inspection;
                                    @endphp
                                    <tr>
                                        <td>{{ $inspection->job_code }}</td>
                                        <td>{{ $inspection->vehicle->reg_no }}</td>
                                        <td>
                                            @foreach($checks as $check)
                                                <div class="mb-1">
                                                    @if($check->issue)
                                                        <span class="badge bg-info">{{ $check->issue->name }}</span>
                                                    @endif
                                                    @if($check->fault)
                                                        <span class="badge bg-secondary">{{ $check->fault->name }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge {{ $checks->first()->verified ? 'bg-success' : 'bg-warning' }}">
                                                {{ $checks->first()->verified ? 'Verified' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#postCheckModal-{{ $inspectionId }}">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                    {{-- Modal --}}
                                    <div class="modal fade" id="postCheckModal-{{ $inspectionId }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Inspection {{ $inspection->job_code }} - Pending Checks</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <form action="{{ route('fleetPostChecks.completeAll', $inspectionId) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        @foreach($checks as $check)
                                                            <div class="card mb-3 shadow-sm">
                                                                <div class="card-body">
                                                                    <p>
                                                                        @if($check->issue)
                                                                            <strong>Issue:</strong> {{ $check->issue->name }}
                                                                        @elseif($check->fault)
                                                                            <strong>Fault:</strong> {{ $check->fault->name }}
                                                                        @endif
                                                                    </p>
                                                                    <p><strong>Verified by Fleet :</strong> {{ $check->verified ? 'Yes' : 'No' }}</p>
                                                                    <p><strong>Remarks:</strong> {{ $check->remarks ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle me-1"></i> All Completed
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mt-2">No pending fleet post-checks found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Inventory Assignment Modals --}}
@foreach($fleetDecisions as $garageReportId => $decisions)
    @php
        $garageReport = $decisions->first()->garageReport;
    @endphp
    @include('garage_reports.edit_inventory_issue', [
        'garageReport' => $garageReport,
        'inbuildIssues' => $garageReport->inbuildIssues
    ])
@endforeach

@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#pendingReportsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });

        $('#completedReportsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>

