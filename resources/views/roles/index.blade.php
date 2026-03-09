@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Roles</h3>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">New Role</a>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-striped">
        <thead><tr><th>#</th><th>Name</th><th>Label</th><th>Permissions</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($roles as $role)
            <tr>
                <td>{{ $role->id }}</td>
                <td>{{ $role->name }}</td>
                <td>{{ $role->label }}</td>
                <td>
                    @foreach($role->permissions as $p)
                        <span class="badge bg-secondary">{{ $p->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete role?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $roles->links() }}
</div>
@endsection
