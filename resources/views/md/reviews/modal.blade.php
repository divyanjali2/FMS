@php
    $inspection = $inspectionReviews->first()->inspection;
    $firstComment = $inspectionReviews->first()->comments;
@endphp

<div class="modal fade" id="mdDecisionModal-{{ $inspectionId }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">

            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">MD Decision - Inspection #00{{ $inspection->id }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Form -->
            <form action="{{ route('md.reviews.storeMultiple', $inspectionId) }}" method="POST">
                @csrf
                <div class="modal-body"  style="max-height: 70vh; overflow-y: auto;">

                    <!-- Inspection Info -->
                    <div class="mb-3 p-3 bg-light rounded shadow-sm">
                        <p><strong>Job Code:</strong> <span class="badge bg-success">{{ $inspection->job_code ?? '-' }}</span></p>
                        <p><strong>Vehicle No:</strong> <span class="badge bg-secondary">{{ $inspection->vehicle->reg_no ?? '-' }}</span></p>
                        <p>
                            <strong>General Manager Comment:</strong>
                            <span class="badge bg-primary fst-italic">{{ ucfirst($firstComment) ?? 'No comment' }}</span>
                        </p>
                    </div>

                    <!-- Hidden Inputs -->
                    @foreach($inspectionReviews as $review)
                        <input type="hidden" name="gm_review_ids[]" value="{{ $review->id }}">
                    @endforeach

                    <!-- Procurement Table -->
                    <div class="table-responsive mb-3">
                        <h6 class="fw-bold">Procurements</h6>
                        <table class="table table-sm table-bordered table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Inventory</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Price</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inspectionReviews as $review)
                                    @php $proc = $review->procurement; @endphp
                                    <tr>
                                        <td>{{ $proc->issueInventory->inventory->name ?? '-' }}</td>
                                        <td>{{ $proc->supplier->name ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($proc->price ?? 0, 2) }}</td>
                                        <td>{{ $proc->remark ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                @php
                                    $totalPrice = $inspectionReviews->sum(fn($r) => $r->procurement->price ?? 0);
                                @endphp
                                <tr>
                                    <th colspan="2" class="text-end">Total:</th>
                                    <th class="text-end">{{ number_format($totalPrice, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Fleet Issues Accordion -->
                    <div class="accordion mb-3" id="fleetAccordion-{{ $inspectionId }}">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFleet-{{ $inspectionId }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFleet-{{ $inspectionId }}" aria-expanded="false">
                                    Fleet Decisions & Issues
                                </button>
                            </h2>
                            <div id="collapseFleet-{{ $inspectionId }}" class="accordion-collapse collapse" aria-labelledby="headingFleet-{{ $inspectionId }}">
                                <div class="accordion-body p-0 table-responsive">
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Reported Dept.</th>
                                                <th>Issue / Fault</th>
                                                <th>Decision</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($inspection->fleetDecisions ?? [] as $fd)
                                                <tr>
                                                    <td>{{ ucfirst($fd->type ?? '-') }} Dept.</td>
                                                    <td>
                                                        @if($fd->issue || $fd->fault)
                                                            {{ $fd->issue->name ?? '' }} {{ $fd->fault->name ?? '' }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ ucfirst($fd->decision ?? '-') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MD Remark -->
                    <div class="mb-3">
                        <label class="fw-bold">Managing Director Remark</label>
                        <textarea name="md_comment" class="form-control" rows="3" placeholder="Enter your remark..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer sticky-bottom bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send to GM</button>
                </div>
            </form>
        </div>
    </div>
</div>
