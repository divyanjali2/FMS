@if(auth()->user()->hasPermission('manage_users'))

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Permission Details</h1>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $permission->name }}</h5>
            <p class="card-text"><strong>Label:</strong> {{ $permission->label ?? '—' }}</p>
        </div>
    </div>

    <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
@endif