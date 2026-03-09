@extends('layouts.app')
@section('content')
<div class="mb-3 d-flex justify-content-end align-items-end">
    <a href="{{ route('garage_reports.index') }}" class="btn btn-primary">Back</a>
</div>

<div class="table-responsive card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h4>Inspection Details (Job {{ $inspection->job_code }})</h4>
    </div>

    <div class="card-body">
        <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no }} ({{ $inspection->vehicle->model }})</p>

        <h5 class="mt-4 fw-bold">Issues Overview</h5>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Reported By</th>
                    <th>Issue / Fault</th>
                    <th>Status</th>
                    <th>Remarks by Fleet</th>
                    <th>Hours</th>
                    <th>Notes</th>
                    <th>Images</th>
                </tr>
            </thead>
            <tbody>
                {{-- Fleet Reported Faults --}}
                @if($inspection->faults->isNotEmpty())
                    @foreach($inspection->faults as $fault)
                        <tr>
                            <td>Fleet</td>
                            <td>{{ $fault->name }}</td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $fault->pivot->status ?? '-')) }}
                                @if(!is_null($fault->pivot->percentage))
                                    - {{ $fault->pivot->percentage }}%
                                @endif
                            </td>
                            <td>{{ $inspection->remarks ?? '— No remarks provided —' }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                @php
                                    $fleetImages = is_array($inspection->images)
                                        ? $inspection->images
                                        : json_decode($inspection->images, true);
                                @endphp

                                @if(!empty($fleetImages))
                                    <div class="row">
                                        @foreach($fleetImages as $img)
                                            <div class="col-md-3 mb-2">
                                                <img src="{{ $img }}" class="img-fluid rounded border preview-image" alt="Fleet Inspection Image" style="cursor:pointer;">
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No images uploaded</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Fleet</td>
                        <td colspan="2" class="text-center text-muted">No faults reported</td>
                        <td>{{ $inspection->remarks ?? '— No remarks provided —' }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            @php
                                $fleetImages = is_array($inspection->images)
                                    ? $inspection->images
                                    : json_decode($inspection->images, true);
                            @endphp

                            @if(!empty($fleetImages))
                                <div class="row">
                                    @foreach($fleetImages as $img)
                                        <div class="col-md-6 mb-2">
                                            <img src="{{ $img }}" class="img-fluid rounded border preview-image" alt="Fleet Inspection Image"style="height: 12rem; object-fit: cover; cursor:pointer;">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">No images uploaded</span>
                            @endif
                        </td>
                    </tr>
                @endif

                {{-- Garage Reports --}}
                @foreach($garageReports as $report)
                    <tr>
                        <td>Garage</td>
                        <td>{{ $report->issue->name ?? '-' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $report->status ?? '-')) }}</td>
                        <td>{{ $report->hours ?? '-' }}</td>
                        <td>{{ $report->remarks ?? '-' }}</td>
                        <td>{{ $report->notes ?? '-' }}</td>
                        <td>
                            @php
                                $garageImages = is_array($report->images)
                                    ? $report->images
                                    : json_decode($report->images, true);
                            @endphp

                            @if(!empty($garageImages))
                                <div class="row">
                                    @foreach($garageImages as $img)
                                        <div class="col-md-3 mb-2">
                                            <img src="{{ $img }}" class="img-fluid rounded preview-image" alt="Garage Image"style="cursor:pointer;">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">No images uploaded</span>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if($inspection->faults->isEmpty() && $garageReports->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center text-muted">No issues reported.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center p-0">
                <img id="previewModalImage" src="" class="img-fluid rounded" alt="Preview">
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            const modalImg = document.getElementById('previewModalImage');

            document.querySelectorAll('.preview-image').forEach(img => {
                img.addEventListener('click', function() {
                    modalImg.src = this.src;
                    modal.show();
                });
            });
        });
    </script>
@endpush
@endsection
