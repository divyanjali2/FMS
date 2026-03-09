@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h4>Edit Garage Report for Job {{ $garageReport->inspection->job_code }}</h4>
    </div>

    <div class="card-body">
        <!-- Vehicle Info -->
        <div class="mb-3">
            <label class="form-label fw-bold">Vehicle</label>
            <p class="form-control-plaintext">
                {{ $garageReport->inspection->vehicle->reg_no }} ({{ $garageReport->inspection->vehicle->model }})
            </p>
        </div>

        <!-- Fleet Reported Issues -->
        <div>
            <label class="form-label fw-bold">Fleet Reported Issues</label>
            @if(!empty($garageReport->inspection->remarks))
                <div class="mt-1">
                        <strong>Remark:</strong> {{ $garageReport->inspection->remarks }}
                </div>
            @endif
            @if($garageReport->inspection->faults->count())
                <ul class="list-group list-group-flush mb-3">
                    @foreach($garageReport->inspection->faults as $fault)
                        <li class="list-group-item">
                            • {{ $fault->name }}

                            @php
                                $status = $fault->pivot->status ?? null;
                                $percentage = $fault->pivot->percentage ?? null;
                                $isFuel = strtoupper($fault->name) === 'FUEL';
                            @endphp

                            @if($status || $percentage)
                                (
                                @if($isFuel && is_numeric($status))
                                    {{ $status }}%
                                @else
                                    @if($status)
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    @endif

                                    @if(!is_null($percentage))
                                        @if($status) - @endif
                                        {{ $percentage }}%
                                    @endif
                                @endif
                                )
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted mb-1">No issues reported by fleet.</p>
                @if(!empty($garageReport->inspection->remarks))
                    <p><strong>Remarked as:</strong> {{ $garageReport->inspection->remarks }}</p>
                @endif
            @endif
        </div>

        <!-- Fleet Uploaded Images -->
        @php
            $fleetImages = is_array($garageReport->inspection->images)
                ? $garageReport->inspection->images
                : json_decode($garageReport->inspection->images, true);
        @endphp

        <div class="mt-3">
            <label class="form-label fw-bold">Fleet Uploaded Images</label>
            @if(!empty($fleetImages))
                <div class="row">
                    @foreach($fleetImages as $img)
                        <div class="col-md-3 mb-2">
                            <img src="{{ $img }}" class="img-fluid rounded border preview-image" alt="Fleet Inspection Image" style="height:14rem; object-fit:cover; cursor:pointer;">
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No images uploaded by fleet.</p>
            @endif
        </div>

        <hr class="my-4" style="opacity:4.5;">

        <!-- Garage Report Form -->
        <form action="{{ route('garage_reports.update', $garageReport) }}" method="POST" enctype="multipart/form-data">            
            @csrf
            @method('PUT')

            <div class="row g-3">
                @foreach($defectCategories as $category)
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light text-dark text-sm" data-bs-toggle="collapse" data-bs-target="#category-{{ $category->id }}" style="cursor:pointer;">
                                <h5 class="mb-0">{{ $category->name }}</h5>
                            </div>
                            <div id="category-{{ $category->id }}" class="collapse">
                                <div class="card-body">
                                    <div class="row g-3">
                                        @foreach($category->issues as $issue)
                                            <div class="col-md-4">
                                                <div class="d-flex flex-column p-2 border rounded">
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" name="issue_id[]" value="{{ $issue->id }}" class="form-check-input issue-checkbox" id="issue-{{ $issue->id }}">
                                                        <label class="form-check-label fw-bold" for="issue-{{ $issue->id }}">
                                                            {{ $issue->name }}
                                                        </label>
                                                    </div>
                                                    <select name="issue_action[{{ $issue->id }}]"
                                                            class="form-select form-select-sm issue-action-dropdown" disabled>
                                                        <option value="">-- Action --</option>
                                                        <option value="repair">Repair</option>
                                                        <option value="replace">Replace</option>
                                                        <option value="top-up">Top-up</option>
                                                        <option value="refill">Refill</option>
                                                    </select>

<div class="mt-2 d-none issue-images-wrapper">
    <input type="file"
           name="images[{{ $issue->id }}][]"
           class="form-control form-control-sm issue-images"
           accept="image/*"
           multiple>
    <small class="text-muted">Upload images for this issue</small>
</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

           <div class="row mt-4 g-3 d-none" id="hours-wrapper">
                <div class="col-md-6">
                    <label for="hours" class="form-label fw-bold">Estimated Hours to Complete the Work</label>
                    <input type="number" step="0.1" name="hours" id="hours" class="form-control"
                        value="{{ old('hours', $garageReport->hours ?? '') }}">
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <label for="next_step" class="form-label fw-bold">Next Step<span class="text-danger">*</span></label>
                <select name="next_step" id="next_step" class="form-select" required>
                    <option value="send_to_repair" selected>Send to Repair</option>
                    <option value="make_available">Make Available</option>
                </select>
            </div>

            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i>
                Submitting this form will update the report and send it to the fleet manager.
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
                <a href="{{ route('garage_reports.index', $garageReport->inspection_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Update & Send to Fleet Manager</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center p-0">
                <img src="" id="modalImage" class="img-fluid rounded" alt="Preview">
            </div>
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const hoursWrapper = document.getElementById('hours-wrapper');
        const hoursInput = document.getElementById('hours');

        function toggleHoursField() {
            let show = false;

            document.querySelectorAll('.issue-action-dropdown').forEach(select => {
                if (select.value !== "") {
                    show = true;
                }
            });

            if (show) {
                hoursWrapper.classList.remove('d-none');
            } else {
                hoursWrapper.classList.add('d-none');
                hoursInput.value = '';
            }
        }

      document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
            const container = checkbox.closest('.d-flex.flex-column');
            const dropdown = container?.querySelector('.issue-action-dropdown');
            const imagesWrapper = container?.querySelector('.issue-images-wrapper');

            const toggle = () => {
                if (dropdown) {
                    dropdown.disabled = !checkbox.checked;
                    if (!checkbox.checked) dropdown.value = "";
                }

                if (imagesWrapper) {
                    imagesWrapper.classList.toggle('d-none', !checkbox.checked);
                    if (!checkbox.checked) {
                        const fileInput = imagesWrapper.querySelector('input[type="file"]');
                        if (fileInput) fileInput.value = '';
                    }
                }

                toggleHoursField();
            };

            toggle();
            checkbox.addEventListener('change', toggle);
        });

        // Detect dropdown changes
        document.querySelectorAll('.issue-action-dropdown').forEach(select => {
            select.addEventListener('change', toggleHoursField);
        });

        // Run once on page load
        toggleHoursField();

        // Image preview modal
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        const modalImage = document.getElementById('modalImage');

        document.querySelectorAll('.preview-image').forEach(img => {
            img.addEventListener('click', () => {
                modalImage.src = img.src;
                modal.show();
            });
        });
    });
</script>
@endpush
@endsection
