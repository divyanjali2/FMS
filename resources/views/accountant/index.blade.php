@if(auth()->user()->hasPermission('manage_accountant'))
@extends('layouts.app')

@section('content')
    <div class="container my-4">
        <h3 class="mb-4 fw-bold">Accountant Reviews</h3>

        <!-- Pending Reviews Accordion -->
        <div class="accordion mb-4" id="pendingAccordion">
            <div class="accordion-item shadow-sm rounded">
                <h2 class="accordion-header" id="headingPending">
                    <button class="accordion-button bg-success text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePending" aria-expanded="true">
                        Purchase Orders <span class="badge bg-dark ms-2">{{ $pendingReviews->count() }}</span>
                    </button>
                </h2>
                <div id="collapsePending" class="accordion-collapse collapse show" aria-labelledby="headingPending" data-bs-parent="#pendingAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Inspection</th>
                                        <th>Job Code</th>
                                        <th>Vehicle No.</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingReviews as $inspectionId => $procurements)
                                        @php
                                            $firstProcurement = $procurements->first();
                                            $inspection = $firstProcurement->issueInventory->garageReport->inspection ?? null;
                                            $jobCode = $inspection->job_code ?? '-';
                                            $vehicleReg = $inspection->vehicle->reg_no ?? '-';
                                            $totalPrice = $procurements->sum('price');
                                        @endphp
                                        <tr class="hover-shadow transition">
                                            <td>00{{ $inspectionId }}</td>
                                            <td>{{ $jobCode }}</td>
                                            <td>{{ $vehicleReg }}</td>
                                            <td>{{ number_format($totalPrice, 2) }}</td>
                                            <td>
                                                @php
                                                $allApproved = $procurements->every(function($p) {
                                                    $procStatus = optional($p->accountantReview)->status;
                                                    // Collect GRN statuses for this procurement, filter out nulls
                                                    $grnStatuses = optional($p->grns)->pluck('accountantReview.status')->filter();

                                                    $procApproved = in_array($procStatus, ['send_to_procurement', 'send_to_fm']);
                                                    $grnApproved = $grnStatuses->isEmpty() || $grnStatuses->every(fn($s) => $s === 'send_to_fm');

                                                    return $procApproved && $grnApproved;
                                                });
                                                @endphp

                                                @if($allApproved)
                                                    <a href="{{ route('accountant.purchaseOrder', $inspectionId) }}" 
                                                    class="btn btn-sm btn-primary" 
                                                    target="_blank" 
                                                    title="View Purchase Order">
                                                        <i class="bi bi-eye-fill me-1"></i> View PO
                                                    </a>
                                                @else
                                                    <a href="{{ route('accountant.show', $inspectionId) }}" 
                                                    class="btn btn-sm btn-info" 
                                                    title="Review">
                                                        <i class="bi bi-pencil-square me-1"></i> Review
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No pending reviews</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRNs Accordion -->
        <div class="accordion mt-5" id="grnAccordion">
            <div class="accordion-item shadow-sm rounded">
                <h2 class="accordion-header" id="headingGRN">
                    <button class="accordion-button bg-info text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGRN" aria-expanded="true">
                        GRNs <span class="badge bg-dark ms-2">{{ $grnsReviews->count() }}</span>
                    </button>
                </h2>
                <div id="collapseGRN" class="accordion-collapse collapse show" aria-labelledby="headingGRN" data-bs-parent="#grnAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Inspection</th>
                                        <th>Vehicle No.</th>
                                        <th>Total Items</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grnsReviews as $inspectionId => $grns)
                                        @php
                                            $firstGrn = $grns->first();
                                            $inspection = $firstGrn->procurement->inspection ?? null;
                                            $vehicleReg = $inspection->vehicle->reg_no ?? '-';
                                            $totalItems = $grns->sum('received_qty');

                                            // Check if all GRNs are approved
                                            $allApproved = $grns->every(function($grn) {
                                                return optional($grn->accountantReview)->status === 'send_to_fm';
                                            });
                                        @endphp
                                        <tr>
                                            <td>00{{ $inspectionId }}</td>
                                            <td>{{ $vehicleReg }}</td>
                                            <td>{{ $totalItems }}</td>
                                            <td>
                                                <a href="{{ route('procurements.viewGRN', $inspectionId) }}" class="btn btn-sm btn-primary me-1">
                                                    <i class="bi bi-eye-fill me-1"></i> View GRN
                                                </a>
                                                @unless($allApproved)
                                                <form action="{{ route('accountant.approveGRN', $inspectionId) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle me-1"></i> Approve
                                                    </button>
                                                </form>
                                                @endunless
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed / Sent to GM Accordion -->
        <div class="accordion mt-5" id="completedAccordion">
            <div class="accordion-item shadow-sm rounded">
                <h2 class="accordion-header" id="headingCompleted">
                    <button class="accordion-button bg-secondary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompleted" aria-expanded="true">
                        Completed / Sent to FM <span class="badge bg-dark ms-2">{{ $completedReviews->count() }}</span>
                    </button>
                </h2>
                <div id="collapseCompleted" class="accordion-collapse collapse show" aria-labelledby="headingCompleted" data-bs-parent="#completedAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Inspection</th>
                                        <th>Job Code</th>
                                        <th>Vehicle No.</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($completedReviews as $inspectionId => $reviews)
                                        @php
                                            $firstReview = $reviews->first();
                                            $inspection = $firstReview->inspection ?? null;
                                            $vehicleReg = $inspection->vehicle->reg_no ?? '-';
                                            $jobCode = $inspection->job_code ?? '-';
                                            $totalPrice = $reviews->sum(fn($r) => $r->procurement->price ?? 0);
                                        @endphp
                                        <tr class="hover-shadow transition">
                                            <td>00{{ $inspectionId }}</td>
                                            <td>{{ $jobCode }}</td>
                                            <td>{{ $vehicleReg }}</td>
                                            <td>{{ number_format($totalPrice, 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $inspectionId }}" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No completed reviews</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modals --}}
        @foreach($completedReviews as $inspectionId => $reviews)
            @php $inspection = $reviews->first()->inspection ?? null; @endphp
            <div class="modal fade" id="viewModal{{ $inspectionId }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-primary text-white rounded-top-4">
                            <h5 class="modal-title fw-bold">Procurement Bill (Inspection 00{{ $inspectionId }})</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4 border-bottom pb-3">
                                <h6 class="fw-bold text-muted">Vehicle & Job Details</h6>
                                <p class="mb-1"><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>
                                <p class="mb-0"><strong>Vehicle No:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-secondary text-center">
                                        <tr>
                                            <th>Inventory</th>
                                            <th>Supplier</th>
                                            <th>Price</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reviews as $review)
                                            @php $proc = $review->procurement; @endphp
                                            @if($proc->status === 'outsourced')
                                                <tr>
                                                    <td>{{ $proc->issueInventory->inventory->name ?? '-' }}</td>
                                                    <td>{{ $proc->supplier->name ?? '-' }}</td>
                                                    <td class="text-end">{{ number_format($proc->price ?? 0, 2) }}</td>
                                                    <td>{{ $proc->remark ?? '-' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        @php
                                            $totalOutsourcedPrice = $reviews->sum(fn($r) => ($r->procurement->status === 'outsourced') ? ($r->procurement->price ?? 0) : 0);
                                        @endphp
                                        <tr>
                                            <th colspan="2" class="text-end">Total:</th>
                                            <th class="text-end">{{ number_format($totalOutsourcedPrice, 2) }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between px-4 py-3 border-top">
                            <small class="text-muted">Generated on {{ now()->format('d M Y, H:i') }}</small>
                            <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .transition {
            transition: all 0.3s ease;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table tfoot th {
            font-size: 1.05rem;
            font-weight: 600;
        }
    </style>
@endsection
@endif
