@if(auth()->user()->hasPermission('manage_inspection-reports'))
    @extends('layouts.app')
    @section('content')
    @include('faults.form', ['fault' => $fault])
    @endsection
@endif