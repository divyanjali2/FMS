@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')
@section('content')
<h3>Edit Item</h3>
<form action="{{ route('inventories.update', $inventory->id) }}" method="POST">
    @method('PUT')
    @include('inventories.form')
</form>
@endsection
@endif