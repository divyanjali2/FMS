@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4 fw-bold text-dark">Fleet Post Checks</h3>

    <div class="row g-3">
        @forelse($inspections as $inspection)
            @php
                $postCheck = $inspection->fleetPostCheck;
                $statusLabels = [
                    'send_to_fm' => 'Send to Fleet Manager',
                    'send_back_to_garage' => 'Send Back to Garage',
                ];
            @endphp

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-primary fw-bold">{{ $inspection->job_code }}</h5>
                            <p class="card-text mb-2">
                                <span class="fw-semibold">Vehicle:</span> {{ $inspection->vehicle->reg_no }}
                            </p>
                        </div>

                        @if($postCheck)
                            <button type="button" class="btn btn-outline-secondary mt-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#statusModal{{ $inspection->id }}">
                                {{ $statusLabels[$postCheck->status] ?? ucfirst(str_replace('_', ' ', $postCheck->status)) }}
                            </button>
                        @else
                            <a href="{{ route('fleet_post_checks.show', $inspection->id) }}" 
                               class="btn btn-primary mt-3 rounded-pill">
                                View / Post Check
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Modal for showing post check status --}}
            @if($postCheck)
                <div class="modal fade" id="statusModal{{ $inspection->id }}" tabindex="-1" aria-labelledby="statusModalLabel{{ $inspection->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusModalLabel{{ $inspection->id }}">Post Check Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Job Code:</strong> {{ $inspection->job_code }}</p>
                                <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no }}</p>
                                <p><strong>Status:</strong> {{ $statusLabels[$postCheck->status] ?? ucfirst(str_replace('_', ' ', $postCheck->status)) }}</p>
                                @if($postCheck->remarks)
                                    {{-- <p><strong>Remarks:</strong> {{ $postCheck->remarks }}</p> --}}
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12">
                <div class="alert alert-secondary text-center">
                    No fleet post checks available.
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
</style>
@endsection
