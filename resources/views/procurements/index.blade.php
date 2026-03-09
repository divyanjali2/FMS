@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')
@section('content')
    <div class="container py-4">
        {{-- Procurement Requests --}}
        <h2 class="mb-4 fw-bold text-primary">Procurement Requests</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Report ID</th>
                        <th scope="col">Job Code</th>
                        <th scope="col">Vehicle No</th>
                        <th scope="col">Issue / Fault</th>
                        <th scope="col">Inventory Name</th>
                        <th scope="col">Requested Qty</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grouped = $requests->groupBy('garage_report_id');
                    @endphp

                    @if($grouped->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No procurement requests found.
                            </td>
                        </tr>
                    @else
                        @foreach($grouped as $reportId => $items)
                            @foreach($items as $index => $req)
                                <tr>
                                    <td>00{{ $req->garage_report_id }}</td>
                                    <td>{{ $req->job_code }}</td>
                                    <td>{{ $req->reg_no }}</td>
                                    <td>
                                        @if($req->issue_name)
                                            <span class="badge bg-info me-1">{{ $req->issue_name }}</span>
                                        @endif
                                        @if($req->fault_name)
                                            <span class="badge bg-warning text-dark">{{ $req->fault_name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $req->inventory_name }}</td>
                                    <td>{{ $req->quantity }}</td>
                                    <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-Y') }}</td>

                                    @if($loop->first)
                                        <td rowspan="{{ $items->count() }}" class="text-center align-middle">
                                            @if($req->procurement_exists == 0)
                                                <a href="{{ route('procurements.edit', $items->first()->id) }}" class="btn btn-md btn-primary mb-1" title="Edit Procurement">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-md btn-success mb-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $reportId }}" title="View Procurement">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <div class="modal fade" id="viewModal{{ $reportId }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Procurement Details (Report #00{{ $reportId }})</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <table class="table table-bordered table-striped">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Inventory</th>
                                                                            <th>Requested Qty</th>
                                                                            <th>Status</th>
                                                                            <th>Supplier</th>
                                                                            <th>Price</th>
                                                                            <th>Remark</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($items as $item)
                                                                        @php
                                                                            $proc = \App\Models\Procurement::where('issue_inventory_id', $item->id)
                                                                                ->where('procurement_status', '!=', 'cancelled')
                                                                                ->first();
                                                                        @endphp
                                                                            <tr>
                                                                                <td>{{ $item->inventory_name }}</td>
                                                                                <td>{{ $item->quantity }}</td>
                                                                                <td>
                                                                                    <span class="badge bg-secondary">
                                                                                        {{ $proc ? ucfirst(str_replace('_', ' ', $proc->status)) : '-' }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>{{ $proc->supplier->name ?? '-' }}</td>
                                                                                <td>{{ $proc->price ?? '-' }}</td>
                                                                                <td>{{ $proc->remark ?? '-' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $inspection = \App\Models\GarageReport::find($req->garage_report_id)->inspection ?? null;
                                                    $allFromStock = $inspection?->procurements->every(fn($p) => $p->status === 'from_stock');
                                                    $hasReview = \App\Models\AccountantReview::where('inspection_id', $inspection?->id)->exists();
                                                @endphp
                                                @if(!$allFromStock && $hasReview)
                                                    <a href="{{ route('accountant.purchaseOrder', $inspection->id) }}" target="_blank" class="btn btn-md btn-info" title="View Purchase Order">
                                                        <i class="bi bi-file-earmark-text me-1"></i> View PO
                                                    </a>
                                                    @php
                                                        $outsourcedPOs = \App\Models\Procurement::where('inspection_id', $inspection->id)
                                                            ->where('status', 'outsourced')
                                                            ->where('procurement_status', '!=', 'cancelled') 
                                                            ->get();
                                                    @endphp

                                                    @if($outsourcedPOs->isNotEmpty())
                                                        <button type="button" class="btn btn-md btn-danger" data-bs-toggle="modal" data-bs-target="#cancelPOModal{{ $inspection->id }}">
                                                            <i class="bi bi-x-circle"></i> Cancel PO
                                                        </button>
                                                    @endif

                                                    <div class="modal fade" id="cancelPOModal{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title">Cancel Purchase Orders (Report #00{{ $inspection->id }})</h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <table class="table table-bordered">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>PO ID</th>
                                                                                <th>Inventory</th>
                                                                                <th>Supplier</th>
                                                                                <th>Price</th>
                                                                                <th class="text-center">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @forelse($outsourcedPOs as $po)
                                                                                <tr>
                                                                                    <td>{{ $po->po_id }}</td>
                                                                                    <td>{{ $po->issueInventory->inventory->name ?? '-' }}</td>
                                                                                    <td>{{ $po->supplier->name ?? '-' }}</td>
                                                                                    <td>{{ $po->price ?? '-' }}</td>
                                                                                    <td class="text-center">
                                                                                        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#recreatePOModal{{ $po->id }}">
                                                                                            <i class="bi bi-arrow-repeat"></i> Cancel & New PO
                                                                                        </button>
                                                                                    </td>
                                                                                </tr>
                                                                            @empty
                                                                                <tr>
                                                                                    <td colspan="5" class="text-center text-muted">No outsourced POs found.</td>
                                                                                </tr>
                                                                            @endforelse
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @foreach($outsourcedPOs as $po)
                                                        <div class="modal fade" id="recreatePOModal{{ $po->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-warning text-dark">
                                                                        <h5 class="modal-title fw-bold">Recreate PO ({{ $po->po_id }})</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        <form action="{{ route('procurements.recreatePO', $po->id) }}" method="POST" enctype="multipart/form-data">
                                                                            @csrf
                                                                            @method('PATCH')

                                                                            <p><strong>Inventory:</strong> {{ $po->issueInventory->inventory->name }}</p>
                                                                            <p><strong>Requested Qty:</strong> {{ $po->issueInventory->quantity }}</p>

                                                                            <hr> 

                                                                            <div class="row mt-3">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label>Supplier <span class="text-danger">*</span></label>
                                                                                    <select name="supplier_id" class="form-control" required>
                                                                                        <option value="">-- Select Supplier --</option>
                                                                                        @foreach(\App\Models\Supplier::all() as $supplier)
                                                                                            <option value="{{ $supplier->id }}" @if($supplier->id == $po->supplier_id) selected @endif>
                                                                                                {{ $supplier->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
    
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label>Price <span class="text-danger">*</span></label>
                                                                                    <input type="number" name="price" class="form-control" value="{{ $po->price }}" required>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row mt-3">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label>Remark</label>
                                                                                    <textarea name="remark" class="form-control">{{ $po->remark }}</textarea>
                                                                                </div>
    
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label>Upload Bill (PDF/Image)</label>
                                                                                    <input type="file" name="bill" class="form-control" accept=".pdf,image/*">
                                                                                </div>
                                                                            </div>

                                                                            <div class="text-end">
                                                                                <button type="submit" class="btn btn-success">
                                                                                    <i class="bi bi-check-circle"></i> Save New PO
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="8" style="background-color: transparent"><hr class="my-1" style="opacity: 1.5"></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- GRN Section --}}
        <h3 class="mt-5 fw-bold text-success">Goods Received Note (GRN)</h3>

        @if($outsourcedGroups->isEmpty())
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-circle me-2"></i> No GRNs available at the moment.
            </div>
        @else
            @foreach($outsourcedGroups as $inspectionId => $procs)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm mb-4">
                        <thead class="table-success">
                            <tr class="text-center">
                                <th style="width: 5%;">#</th>
                                <th style="width: 35%;">Inventory</th>
                                <th style="width: 20%;">Requested Qty</th>
                                <th style="width: 20%;">Status</th>
                                <th style="width: 20%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($procs as $index => $proc)
                                <tr>
                                    <td class="text-center">00{{ $index + 1 }}</td>
                                    <td>{{ $proc->issueInventory->inventory->name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $proc->fulfilled_qty }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($inspectionId, $existingGRNs))
                                            <span class="badge bg-success">GRN Created</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>

                                    {{-- Show action only once per inspection --}}
                                    @if($loop->first)
                                        <td rowspan="{{ $procs->count() }}" class="text-center align-middle">
                                            @if(!in_array($inspectionId, $existingGRNs))
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#grnModal{{ $inspectionId }}">
                                                    <i class="bi bi-pencil-square me-1"></i> Enter GRN
                                                </button>
                                            @else
                                                <a href="{{ route('procurements.viewGRN', $inspectionId) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="bi bi-file-earmark-text me-1"></i> View GRN
                                                </a>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal for this inspection --}}
                <div class="modal fade" id="grnModal{{ $inspectionId }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" action="{{ route('procurements.storeGRN', $inspectionId) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-box-seam me-2"></i> Enter GRN - Inspection #{{ $inspectionId }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach($procs as $proc)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                {{ $proc->issueInventory->inventory->name }}
                                                <span class="badge bg-secondary ms-2">Requested: {{ $proc->fulfilled_qty }}</span>
                                            </label>
                                            <input type="number" name="received_qty[{{ $proc->id }}]" class="form-control mb-2"
                                                placeholder="Enter Received Amount" required>
                                            <textarea name="remarks[{{ $proc->id }}]" class="form-control" placeholder="Remark"></textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save me-1"></i> Save GRN
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

        <div class="mt-3">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
@endif
