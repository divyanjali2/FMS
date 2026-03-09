@if(auth()->user()->hasPermission('manage_users'))

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Permission</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('permissions.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Permission Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
            <small class="form-text text-muted">Use only letters, numbers, dashes, and underscores.</small>
        </div>

        <div class="mb-3">
            <label for="label" class="form-label">Label (optional)</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ old('label') }}">
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
@endif