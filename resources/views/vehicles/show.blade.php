<div class="modal fade" id="vehicleModal{{ $vehicle->id }}" tabindex="-1" aria-labelledby="vehicleModalLabel{{ $vehicle->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="vehicleModalLabel{{ $vehicle->id }}">Vehicle Details : {{ $vehicle->reg_no }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>Registration No:</strong> {{ $vehicle->reg_no }}</p>
                        <p><strong>Make:</strong> {{ $vehicle->make }}</p>
                        <p><strong>Model:</strong> {{ $vehicle->model }}</p>
                        <p><strong>Year:</strong> {{ $vehicle->year_of_manufacture }}</p>
                        <p><strong>Color:</strong> {{ $vehicle->color }}</p>
                        <p><strong>VIN:</strong> {{ $vehicle->vin }}</p>
                        <p><strong>Engine No:</strong> {{ $vehicle->engine_no }}</p>
                        <p><strong>Vehicle Type:</strong> {{ $vehicle->vehicleType->type_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fuel Type:</strong> {{ $vehicle->fuelType->fuel_name ?? 'N/A' }}</p>
                        <p><strong>Transmission:</strong> {{ $vehicle->transmission->transmission_name ?? 'N/A' }}</p>
                        <p><strong>Seating Capacity:</strong> {{ $vehicle->seating_capacity ?? 'N/A' }}</p>
                        <p><strong>Odometer Reading:</strong> {{ $vehicle->odometer_at_registration ?? 'N/A' }}</p>
                        <p><strong>Ownership Type:</strong> {{ $vehicle->ownershipType->ownership_name ?? 'N/A' }}</p>
                        <p><strong>Owner Name:</strong> {{ $vehicle->owner_name ?? 'N/A' }}</p>
                        <p><strong>Company Name:</strong> {{ $vehicle->company->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Company Logo:</strong>
                            @if(!empty($vehicle->company->logo))
                                <img src="{{ asset($vehicle->company->logo) }}" alt="Company Logo" class="img-fluid rounded" style="max-width: 80px; height:auto;">
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                <hr>
                <div class="mt-3">
                    <h6 class="fw-bold">Insurance Details</h6>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <p><strong>Provider:</strong> {{ $vehicle->insurance_provider ?? 'N/A' }}</p>
                            <p><strong>Policy No:</strong> {{ $vehicle->insurance_policy_no ?? 'N/A' }}</p>
                            <p><strong>Expiry Date:</strong> {{ $vehicle->insurance_expiry ? date('Y-m-d', strtotime($vehicle->insurance_expiry)) : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Emission Test Expiry:</strong> {{ $vehicle->emission_test_expiry ? date('Y-m-d', strtotime($vehicle->emission_test_expiry)) : 'N/A' }}</p>
                            <p><strong>Revenue License Expiry:</strong> {{ $vehicle->revenue_license_expiry ? date('Y-m-d', strtotime($vehicle->revenue_license_expiry)) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mt-3">
                    <h6 class="fw-bold">Financial Details</h6>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <p><strong>Purchase Price:</strong> {{ $vehicle->purchase_price ? number_format($vehicle->purchase_price, 2) : 'N/A' }}</p>
                            <p><strong>Purchase Date:</strong> {{ $vehicle->purchase_date ? date('Y-m-d', strtotime($vehicle->purchase_date)) : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Depreciation Rate:</strong> {{ $vehicle->depreciation_rate ? $vehicle->depreciation_rate . '%' : 'N/A' }}</p>
                            <p><strong>Current Value:</strong> {{ $vehicle->current_value ? number_format($vehicle->current_value, 2) : 'N/A' }}</p>
                        </div>
                    </div>
                    @if($vehicle->loan_emi_details)
                    <div class="mt-2">
                        <p><strong>Loan/EMI Details:</strong> {{ $vehicle->loan_emi_details }}</p>
                    </div>
                    @endif
                </div>

                @if($vehicle->lease_start && $vehicle->lease_end)
                <div class="mt-3">
                    <h6 class="fw-bold">Lease Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p><strong>Lease Start:</strong> {{ date('Y-m-d', strtotime($vehicle->lease_start)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Lease End:</strong> {{ date('Y-m-d', strtotime($vehicle->lease_end)) }}</p>
                        </div>
                    </div>
                </div>
                @endif
                <hr>
                <div class="mt-3">
                    <h6 class="fw-bold">Uploaded Documents</h6>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <p><strong>Revenue License:</strong></p>
                            @if($vehicle->revenue_license_file)
                                <a href="{{ $vehicle->revenue_license_file }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <p><strong>Insurance:</strong></p>
                            @if($vehicle->insurance_file)
                                <a href="{{ $vehicle->insurance_file }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <p><strong>Emission Test:</strong></p>
                            @if($vehicle->emission_test_file)
                                <a href="{{ $vehicle->emission_test_file }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <p><strong>Other Document:</strong></p>
                            @if($vehicle->other_doc_file)
                                <a href="{{ $vehicle->other_doc_file }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>
                <div class="mt-3">
                    <h6 class="fw-bold">Vehicle Images</h6>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3 text-center">
                            <p><strong>Front</strong></p>
                            @if($vehicle->vehicle_front)
                                <img src="{{ $vehicle->vehicle_front }}" alt="Front" class="img-fluid rounded">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            <p><strong>Back</strong></p>
                            @if($vehicle->vehicle_back)
                                <img src="{{ $vehicle->vehicle_back }}" alt="Back" class="img-fluid rounded">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            <p><strong>Left Side</strong></p>
                            @if($vehicle->vehicle_left)
                                <img src="{{ $vehicle->vehicle_left }}" alt="Left" class="img-fluid rounded">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            <p><strong>Right Side</strong></p>
                            @if($vehicle->vehicle_right)
                                <img src="{{ $vehicle->vehicle_right }}" alt="Right" class="img-fluid rounded">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <hr>
                        <div class="col-md-12">
                            <p><strong>Remarks : </strong>{{ $vehicle->remarks ? $vehicle->remarks : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
