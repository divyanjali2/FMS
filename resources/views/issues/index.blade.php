@if(auth()->user()->hasPermission('manage_inspection-reports'))
    @extends('layouts.app')
    @section('content')
    <div class="card shadow-sm">
        <div class="card-header text-white d-flex justify-content-between" style="background-color: #820000">
            <h4 class="fw-bold">Issues from Garage</h4>
            <a href="{{ route('issues.create') }}" class="btn bg-white text-dark btn-sm">Add Issue</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issues as $issue)
                    <tr>
                        <td>{{ $issue->name }}</td>
                        <td>{{ $issue->category->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('issues.edit', $issue->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if(auth()->user()->hasPermission('manage_users'))
                                <form action="{{ route('issues.destroy', $issue->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $issues->links() }}
        </div>
    </div>
    @endsection
@endif