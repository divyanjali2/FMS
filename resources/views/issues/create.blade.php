@if(auth()->user()->hasPermission('manage_garage-reports'))
    @extends('layouts.app')
    @section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white"><h4>Add Issue</h4></div>
        <div class="card-body">
            @include('issues.form')
        </div>
    </div>
    @endsection
@endif