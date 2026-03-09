@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Role: {{ $role->name }} (ID: {{ $role->id }})</h3>
    <p><strong>Label: </strong> {{ $role->label }}</p>

    <h5>Permissions</h5>
    <div>
        @foreach($role->permissions as $p)
            <span class="badge bg-secondary">{{ $p->name }}</span>
        @endforeach
    </div>

    <h5 class="mt-3">Users with this role</h5>
    <ul>
        @foreach($role->users as $u)
            <li>{{ $u->name }} ({{ $u->email }})</li>
        @endforeach
    </ul>

    <a href="{{ route('roles.index') }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
