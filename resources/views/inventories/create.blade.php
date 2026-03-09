@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')
@section('content')
<h3>Add Item</h3>
<form action="{{ route('inventories.store') }}" method="POST">
    @include('inventories.form')
</form>
@endsection
@endif