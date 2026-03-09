@if(auth()->user()->hasPermission('manage_procurements'))
    @extends('layouts.app')
    @section('content')
    <div class="table-responsive card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #820000">
            <h4 class="fw-bold">Suppliers</h4>
            <a href="{{ route('suppliers.create') }}" class="btn btn-light btn-sm">+ Add Supplier</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->address }}</td>
                            <td>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if(auth()->user()->hasPermission('manage_users'))
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $suppliers->links() }}
        </div>
    </div>
    @endsection
@endif
