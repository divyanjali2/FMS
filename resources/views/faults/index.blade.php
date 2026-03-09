@if(auth()->user()->hasPermission('manage_inspection-reports'))
    @extends('layouts.app')
    @section('content')
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: #820000">
            <h4 class="fw-bold">Faults from Fleet
                <a href="{{ route('faults.create') }}" class="btn btn-sm bg-white float-end">Add Fault</a>
            </h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($faults as $fault)
                        <tr>
                            <td>{{ $fault->name }}</td>
                            <td>{{ ucfirst($fault->type) }}</td>
                            <td>
                                <a href="{{ route('faults.edit', $fault->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if(auth()->user()->hasPermission('manage_users'))
                                    <form action="{{ route('faults.destroy', $fault->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $faults->links() }}
        </div>
    </div>
    @endsection
@endif