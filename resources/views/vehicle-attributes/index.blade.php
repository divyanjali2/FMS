@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="fw-bold">Vehicle Attributes</h2>

    <div class="d-flex justify-content-end">
        <a href="{{ route('vehicle-attributes.create') }}" class="btn btn-primary mb-3">Add New</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attributes as $attr)
                <tr>
                    <td>{{ $attr->id }}</td>
                    <td>{{ $attr->make }}</td>
                    <td>{{ $attr->model }}</td>
                    <td>
                        <a href="{{ route('vehicle-attributes.edit', $attr->id) }}" class="btn btn-warning btn-sm me-1">Edit</a>
                        <form action="{{ route('vehicle-attributes.destroy', $attr->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
