@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Edit Role #{{ $role->id }}</h3>
    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Name (slug)</label>
            <input name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
        </div>
        <div class="mb-3">
            <label>Label</label>
            <input name="label" class="form-control" value="{{ old('label', $role->label) }}">
        </div>

        <div class="mb-3">
            <label>Permissions</label>
            <div class="row">
                @foreach($permissions as $p)
                    <div class="col-md-4">
                        <label class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="form-check-input"
                                {{ $role->permissions->contains($p->id) ? 'checked' : '' }}>
                            <span class="form-check-label">{{ $p->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
