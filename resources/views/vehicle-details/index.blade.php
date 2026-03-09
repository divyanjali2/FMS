@extends('layouts.app')
@section('content')
<div class="container py-2">
  <div class="card shadow-sm border-0 p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
      <h2 class="mb-0 fw-bold me-3">
        Vehicle Details
      </h2>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-success fs-6">
          {{ $vehicles->whereNotNull('emi_number')->where('emi_number', '!=', '')->count() }}
        </span>

        <a href="{{ route('vehicle-details.export') }}" class="btn btn-success btn-sm">
          Export CSV
        </a>
      </div>
    </div>
    
    <hr class="mb-4 mt-0">

  @php
      $canEdit = auth()->user()->email === 'it@explorevacations.lk';
  @endphp

    <table class="table table-sm table-bordered align-middle" id="vehicleAnalyzeTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Vehicle No</th>
          <th>EMI Number</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($vehicles as $v)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $v->reg_no }}</td>

            <td>
              <form class="d-flex gap-2" method="POST" action="{{ route('vehicle-details.update', $v->id) }}">
                @csrf
                <input type="text" name="emi_number" class="form-control form-control-sm"
                       value="{{ old('emi_number', $v->emi_number) }}" placeholder="EMI number" {{ !$canEdit ? 'disabled' : '' }}>
            </td>

            <td>
                <input type="date" name="emi_date" class="form-control form-control-sm"
                       value="{{ old('emi_date', $v->emi_date ? $v->emi_date->format('Y-m-d') : '') }}" {{ !$canEdit ? 'disabled' : '' }}>
            </td>

            <td>
                <button class="btn btn-sm btn-primary" {{ !$canEdit ? 'disabled' : '' }}>Save</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#vehicleAnalyzeTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 25,
                columnDefs: [
                    { orderable: false, targets: -1 } 
                ]
            });
        });
    </script>
  </div>
</div>
@endsection