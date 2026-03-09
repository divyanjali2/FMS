@if(auth()->user()->hasPermission('manage_garage-reports'))

@php
$action = isset($issue) ? route('issues.update', $issue->id) : route('issues.store');
$method = isset($issue) ? 'PUT' : 'POST';
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if(isset($issue)) @method('PUT') @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Issue Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $issue->name ?? old('name') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (isset($issue) && $issue->category_id==$cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('issues.index') }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-success">{{ isset($issue) ? 'Update' : 'Save' }}</button>
    </div>
</form>
@endif