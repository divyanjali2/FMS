@php
    $showSendButton = $showSendButton ?? true;
@endphp

<div class="modal fade" id="{{ $showSendButton ? "viewModal{$inspectionId}" : "viewModalCompleted{$inspectionId}" }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Procurement & Fleet Details (Inspection 00{{ $inspectionId }})</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Vehicle & Job Info -->
                <div class="mb-3">
                    <p><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>
                    <p><strong>Vehicle No:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>
                </div>

                <!-- Procurement Table -->
                <div class="table-responsive mb-3">
                    <h6 class="fw-bold">Procurements</h6>
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead class="table-dark">
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
                                $totalPrice = $reviews->sum(fn($r) => ($r->procurement->status === 'outsourced') ? ($r->procurement->price ?? 0) : 0);
                            @endphp
                            <tr>
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($totalPrice, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Fleet Decisions / Issues / Faults -->
                <div class="table-responsive">
                    <h6 class="fw-bold">Fleet Decisions & Issues</h6>
                    <table class="table table-sm table-bordered table-hover">
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
                                            {{ $fd->issue->name ?? '' }}{{ $fd->fault->name ?? '' }}
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

                <!-- GM Decision -->
                <hr>
                @if($showSendButton)
                    {{-- Decision Form --}}
                    <form action="{{ route('gm.reviewDecision', $inspectionId) }}" method="POST" id="gmDecisionForm{{ $inspectionId }}">
                        @csrf
                        <div class="mb-3">
                            <label class="fw-bold">GM Decision</label><br>

                            <div class="form-check form-check-inline">
                                <label class="form-check-label text-success">
                                    <input class="form-check-input" type="radio" name="decision" value="approved" required>
                                    <i class="bi bi-check-circle-fill"></i> Approve
                                </label>
                            </div>

                            {{-- <div class="form-check form-check-inline">
                                <label class="form-check-label text-danger">
                                    <input class="form-check-input reject-radio" type="radio" name="decision" value="rejected">
                                    <i class="bi bi-x-circle-fill"></i> Reject
                                </label>
                            </div> --}}

                            <div class="form-check form-check-inline">
                                <label class="form-check-label text-warning">
                                    <input class="form-check-input inquire-radio" type="radio" name="decision" value="inquired">
                                    <i class="bi bi-question-circle-fill"></i> Inquire
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <label class="form-check-label text-info">
                                    <input class="form-check-input md-radio" type="radio" name="decision" value="sent_to_md">
                                    <i class="bi bi-arrow-right-circle-fill"></i> Send to MD
                                </label>
                            </div>
                        </div>

                        <!-- Inquiry + Reject Fields -->
                        <div class="inquiry-fields d-none border p-3 rounded bg-light mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assign To <i class="bi bi-person-badge"></i></label>
                                <select name="user_id" class="form-select">
                                    <option value="">-- Select Person --</option>
                                    @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Message <i class="bi bi-chat-left-text"></i></label>
                                <textarea name="message" rows="3" class="form-control" placeholder="Add a message..."></textarea>
                            </div>
                        </div>

                        <div class="reject-fields d-none border p-3 rounded bg-light mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rejection Reason <i class="bi bi-file-earmark-text"></i></label>
                                <textarea name="rejection_reason" rows="3" class="form-control" placeholder="Explain the reason for rejection..."></textarea>
                            </div>
                        </div>

                        <!-- MD Textarea -->
                        <div class="md-fields d-none border p-3 rounded bg-light mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Message for MD <i class="bi bi-chat-left-text"></i></label>
                                <textarea name="md_message" rows="3" class="form-control" placeholder="Add a message for MD..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-fill"></i> Submit Decision
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Show GM decision --}}
                  <div class="alert alert-info">
                    <strong>GM Decision:</strong> 
                    {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $inspection->latest_gm_review->status ?? 'Pending')) }}

                    @if($inspection->latest_gm_review?->status === 'rejected')
                        <br><strong>Reason:</strong> {{ $inspection->latest_gm_review->comments ?? '-' }}
                    @endif

                    @if($inspection->latest_gm_review?->status === 'sent_to_md')
                        <br><strong>Reason:</strong> {{ $inspection->latest_gm_review->comments ?? '-' }}
                    @endif

                    @if($inspection->latest_gm_review?->status === 'inquired')
                        <br><strong>Assigned To:</strong> {{ $inspection->latest_gm_dispatch->user->name ?? '-' }}
                        <br><strong>Message:</strong> {{ $inspection->latest_gm_review->comments ?? '-' }}
                    @endif
                </div>

                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('#gmDecisionForm{{ $inspectionId }} .form-check-input').forEach(radio => {
        radio.addEventListener('change', function() {
            const inquiryFields = document.querySelector('#gmDecisionForm{{ $inspectionId }} .inquiry-fields');
            const rejectFields = document.querySelector('#gmDecisionForm{{ $inspectionId }} .reject-fields');
            const mdFields = document.querySelector('#gmDecisionForm{{ $inspectionId }} .md-fields');

            if (this.value === 'inquired') {
                inquiryFields.classList.remove('d-none');
                rejectFields.classList.add('d-none');
                mdFields.classList.add('d-none');
            } else if (this.value === 'rejected') {
                rejectFields.classList.remove('d-none');
                inquiryFields.classList.add('d-none');
                mdFields.classList.add('d-none');
            } else if (this.value === 'sent_to_md') {
                mdFields.classList.remove('d-none');
                inquiryFields.classList.add('d-none');
                rejectFields.classList.add('d-none');
            } else {
                inquiryFields.classList.add('d-none');
                rejectFields.classList.add('d-none');
                mdFields.classList.add('d-none');
            }
        });
    });

</script>
