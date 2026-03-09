@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-info text-dark fw-bold"><h4>Edit Supplier</h4></div>
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf @method('PUT')
            @include('suppliers.partials.form')
            <div class="d-flex justify-content-end">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary mx-2">Cancel</a>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
@endif