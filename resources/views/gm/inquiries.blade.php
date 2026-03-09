@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4 fw-bold">Inquiries from General Manager</h3>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Inspection Id</th>
                        <th>Job Code</th>
                        <th>Comments</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        <tr>
                            <td>00{{ $inquiry->inspection->id ?? '-' }}</td>
                            <td>{{ $inquiry->inspection->job_code ?? '-' }}</td>
                            <td>{{ $inquiry->comments ?? '-' }}</td>
                            <td><span class="badge bg-warning">Inquired</span></td>
                            <td>{{ $inquiry->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#inquiryModal{{ $inquiry->id }}">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle"></i> No inquiries found for the logged in user!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @foreach($inquiries as $inquiry)
        @php
            $procurements = $inquiry->inspection->procurements ?? collect();
            $userDispatch = $inquiry->dispatches->firstWhere('user_id', auth()->id());
        @endphp

        <div class="modal fade" id="inquiryModal{{ $inquiry->id }}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title fw-bold">
                            Inquiry Details (Inspection #00{{ $inquiry->inspection->id ?? '-' }})
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @if($userDispatch && $userDispatch->status === 'received')
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Noted to GM
                            </div>
                        @endif

                        <h6 class="fw-bold mb-3">Inspection Details</h6>
                        <ul class="list-group mb-3">
                            <li class="list-group-item"><strong>Job Code:</strong> {{ $inquiry->inspection->job_code ?? '-' }}</li>
                            <li class="list-group-item"><strong>Date:</strong> {{ $inquiry->inspection->created_at->format('Y-m-d') }}</li>
                        </ul>

                        @if($procurements->count())
                            <h6 class="fw-bold">Procurements & Inventory</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Inventory Name</th>
                                            <th>Supplier</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Quantity</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($procurements as $p)
                                            <tr>
                                                <td>{{ $p->issueInventory->inventory->name ?? '-' }}</td>
                                                <td>{{ $p->supplier->name ?? '-' }}</td>
                                                <td>{{ $p->price ?? '-' }}</td>
                                                <td>{{ ucfirst($p->status ?? '-') }}</td>
                                                <td>{{ $p->issueInventory->quantity ?? '-' }}</td>
                                                <td>{{ $p->remark ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted"><i class="bi bi-info-circle"></i> No procurement or inventory details available.</p>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @if($userDispatch && $userDispatch->status !== 'received')
                            <form action="{{ route('gm.inquiry.receive', $userDispatch->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Mark as Received</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-3">{{ $inquiries->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
