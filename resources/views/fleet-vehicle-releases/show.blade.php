@extends('layouts.app')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h4>Fleet Vehicle Release - {{ $inspection->vehicle->reg_no ?? '-' }}</h4>
    </div>
    <div class="card-body">
        <p><strong>Job Code:</strong> {{ $inspection->job_code ?? '-' }}</p>
        <p><strong>Vehicle:</strong> {{ $inspection->vehicle->reg_no ?? '-' }}</p>

        <form action="{{ route('fleet-vehicle-release.store', $inspection->id) }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Issue / Fault</th>
                        <th>Verified by Fleet Officer</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($postChecks as $postCheck)
                        <tr>
                            <td>{{ $postCheck->issue->name ?? $postCheck->fault->name ?? '-' }}</td>
                            <td>
                                <input type="checkbox" name="verified[{{ $postCheck->id }}]" value="1"
                                    {{ $postCheck->verified ? 'checked' : '' }} disabled>
                            </td>
                            <td>
                                <input type="text" name="remarks[{{ $postCheck->id }}]" class="form-control"
                                    value="{{ $postCheck->remarks }}" readonly>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">Save Decision & Release Vehicle</button>
            </div>
        </form>
    </div>
</div>
@endsection
