<!-- Inspection Details Modal -->
<div class="modal fade" id="inspectionModal{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Inspection Details - Job Code: {{ $inspection->job_code }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="container-fluid p-0">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Job Code:</strong> {{ $inspection->job_code }}</p>
                            <p class="mb-1"><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no }} ({{ $inspection->vehicle->model }})</p>
                            <p class="mb-1"><strong>Inspection Date:</strong> {{ $inspection->inspection_date }}</p>
                            <p class="mb-1"><strong>Odometer Reading:</strong> {{ $inspection->odometer_reading }}</p>
                            <p class="mb-1"><strong>Remarks:</strong> {{ $inspection->remarks ?: 'No remarks' }}</p>
                        </div>
                    </div>

                    @if($inspection->faults->isNotEmpty())
                    <div class="row mt-4">
                        @php
                            $faultsByType = $inspection->faults->groupBy('type');
                        @endphp

                        @foreach($faultsByType as $type => $faultGroup)
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">{{ ucfirst($type) }} Faults</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row flex-column">
                                            @foreach($faultGroup as $fault)
                                                @php
                                                    $inspectionFault = $inspection->faults()->where('fault_id', $fault->id)->first();
                                                    $status = $inspectionFault && $inspectionFault->pivot ? $inspectionFault->pivot->status : null;
                                                @endphp
                                                <div class="col-md-6 mb-2 d-flex align-items-center">
                                                    <span class="flex-grow-1">{{ $fault->name }}</span>
                                                    @if($status)
                                                        <span class="badge bg-info text-dark">{{ ucfirst($status) }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    @if($inspection->remarks)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Remarks</h5>
                            <p>{{ $inspection->remarks }}</p>
                        </div>
                    </div>
                    @endif

                    @if($inspection->images && count($inspection->images) > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5 class="mb-3">Inspection Images</h5>
                            <div class="row">
                                @foreach($inspection->images as $image)
                                <div class="col-md-3 mb-2">
                                    <img src="{{ $image }}" class="img-thumbnail" alt="Inspection Image">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                {{-- You can add actions like "Send to Garage" here --}}
            </div>
        </div>
    </div>
</div>
