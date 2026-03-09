@extends('layouts.app')

@section('content')

<style>
    .btn-active {
        filter: brightness(100%); 
    }
</style>

<div class="container-fluid py-2" style="background: #fff;">
    <h1 class="fw-bold mb-4 text-center">Reports Dashboard</h1>

    <div class="d-flex mt-4 gap-3 align-items-center justify-content-center">
        <button id="btnViewReports" class="btn btn-primary">View Uploaded Reports</button>
        <button id="btnAddReport" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReportModal">Add Report</button>
        <button id="btnViewRequests" class="btn btn-warning">
            View Report Requests
            @if($pendingCount > 0)
                <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
            @endif
        </button>
    </div>

    <!-- Reports Table -->
    <div id="reportsTableContainer" class="mt-5">
        <div class="table-responsive">
            <table id="reportsTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Report Title</th>
                        <th>Date</th>
                        <th>Send To</th>
                        <th>Report</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($uploadedReports as $report)
                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>{{ strtoupper($report->report_title) }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->report_date)->format('Y-m-d') }}</td>
                            <td>
                                {{ $report->user->name ?? 'N/A' }}
                                ({{ strtoupper($report->user->position ?? '') }} – {{ strtoupper($report->user->department ?? '') }})
                            </td>
                            <td>
                                <a href="{{ route('reports.view', [
                                        $report->id,
                                        basename($report->file_path)
                                    ]) }}"
                                target="_blank"
                                class="btn btn-sm btn-primary">
                                    View Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="6" class="text-center">No reports uploaded yet.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Report Requests Table -->
    <div id="reportRequestsTableContainer" class="mt-5" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4>Report Requests Received</h4>
        </div>
        <div class="table-responsive">
            <table id="reportRequestsTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Report Title</th>
                        <th>From</th>
                        <th>Uploaded Date</th>
                        <th>Report Effective Date</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivedReports as $report)
                        <tr>
                            <td>{{ strtoupper($report->report_title) }}</td>
                            <td>
                                {{ $report->uploader->name ?? 'N/A' }}
                                {{ $report->uploader?->department ? ' - ' . strtoupper($report->uploader->department) : '' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($report->report_date)->format('Y-m-d') }}</td>
                            <td>
                                {{ \Carbon\Carbon::create()->month((int) $report->report_month)->format('F') }}
                                – Week {{ $report->report_week }}
                            </td>
                            <td>{{ ($report->remark ?? '-') }}</td>
                            <td>
                                @if(!$report->accepted)
                                    <button
                                        class="btn btn-sm btn-success btn-accept-report"
                                        data-id="{{ $report->id }}"
                                        data-url="{{ route('reports.accept', $report) }}"
                                        data-file="{{ route('reports.view', [
                                            $report->id,
                                            basename($report->file_path)
                                        ]) }}"
                                    >
                                        View & Accept
                                    </button>
                                @else
                                    <a href="{{ route('reports.view', [
                                            $report->id,
                                            basename($report->file_path)
                                        ]) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-primary">
                                        View Report
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- <tr>
                            <td colspan="5" class="text-center">No reports received yet.</td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding Report -->
    <div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold modal-title" id="addReportModalLabel">Add New Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Report Name -->
                        <div class="mb-3">
                            <label for="reportName" class="form-label fw-bold">Report Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reportName" name="reportName" required>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="reportDate" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reportDate" name="reportDate" value="{{ now()->format('Y-m-d') }}" readonly>
                        </div>

                        <!-- Month & Week -->
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="effectiveMonth" class="form-label fw-bold">Effective Month <span class="text-danger">*</span></label>
                                <select class="form-select" id="effectiveMonth" name="effectiveMonth" required>
                                    <option value="" disabled selected>Select Month</option>
                                    @foreach(range(1, 12) as $month)
                                        <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="effectiveWeek" class="form-label fw-bold">Week <span class="text-danger">*</span></label>
                                <select class="form-select" id="effectiveWeek" name="effectiveWeek" required>
                                    <option value="" disabled selected>Select Week</option>
                                    @foreach(range(1, 5) as $week)
                                        <option value="{{ $week }}">Week {{ $week }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Upload PDF -->
                        <div class="mb-3">
                            <label for="pdfFile" class="form-label fw-bold">Upload Report <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="pdfFile" name="pdfFile" required>
                        </div>

                        <div class="mb-3">
                            <label for="send_to_user_id" class="form-label fw-bold">
                                Send Report To <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="send_to_user_id" name="send_to_user_id" required>
                                <option value="">Select Recipient</option>
                                @foreach($receivers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ strtoupper($user->name) }}
                                        ({{ strtoupper($user->position) }} – {{ strtoupper($user->department) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Remark -->
                        <div class="mb-3">
                            <label for="remark" class="form-label fw-bold">Remark</label>
                            <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="Optional remark"></textarea>
                        </div>

                        <hr>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSaveReport" class="btn btn-primary">
                            Save Report
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    $(document).on('click', '.btn-accept-report', function () {

        const button = $(this);
        const url = button.data('url');
        const fileUrl = button.data('file');

        // Open PDF first
        window.open(fileUrl, '_blank');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function () {

                // Update Accepted badge
                const row = button.closest('tr');
                row.find('td:eq(4)').html('<span class="badge bg-success">Accepted</span>');

                // Replace button with View button
                button.replaceWith(`
                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary">
                        View Report
                    </a>
                `);

                // Update pending counter
                let badge = $('#btnViewRequests .badge');
                if (badge.length) {
                    let count = parseInt(badge.text());
                    count--;

                    if (count > 0) {
                        badge.text(count);
                    } else {
                        badge.remove();
                    }
                }
            }
        });
    });

    $(document).ready(function() {
        $('#addReportModal form').on('submit', function () {
            const btn = $('#btnSaveReport');
            btn.prop('disabled', true).text('Saving...');
        });

        $('#reportsTable, #reportRequestsTable').DataTable({
            responsive: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            order: [[0, 'desc']]
        });

        function showTable(showId, hideId, activeBtn, inactiveBtn, activeClass, inactiveClass) {
            $(showId).show();
            $(hideId).hide();

            $(activeBtn).addClass(activeClass).removeClass(inactiveClass);
            $(inactiveBtn).removeClass(activeClass).addClass(inactiveClass);
        }

        $('#btnViewReports').click(function() {
            showTable('#reportsTableContainer', '#reportRequestsTableContainer', 
                    '#btnViewReports', '#btnViewRequests', 'btn-primary', 'btn-warning');
        });

        $('#btnViewRequests').click(function() {
            showTable('#reportRequestsTableContainer', '#reportsTableContainer', 
                    '#btnViewRequests', '#btnViewReports', 'btn-warning', 'btn-primary');
        });

        $('#btnViewReports').addClass('btn-primary');

    });
</script>
@endpush
@endsection
