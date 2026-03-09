@if(auth()->user()->hasPermission('manage_inspection-reports'))
    @extends('layouts.app')
    @section('content')
    <div class="card shadow-sm">
        <div class="card-header text-white d-flex justify-content-between" style="background-color: #820000">
            <h4 class="fw-bold">Defect Categories</h4>
            <a href="{{ route('defect_categories.create') }}" class="btn bg-white text-dark btn-sm">Add Category</a>
        </div>
        <div class="table-responsive card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Suppliers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>00{{ $cat->id }}</td>
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->description }}</td>
                            <td>
                                @if($cat->suppliers->count())
                                    <ul class="list-unstyled mb-0">
                                        @foreach($cat->suppliers as $supplier)
                                            <li><span class="badge bg-primary">{{ $supplier->name }}</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">No suppliers</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('defect_categories.edit', $cat->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if(auth()->user()->hasPermission('manage_users'))
                                    <form action="{{ route('defect_categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $categories->links() }}
        </div>
    </div>
    @endsection
@endif