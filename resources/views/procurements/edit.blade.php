@if(auth()->user()->hasPermission('manage_procurements'))
    @extends('layouts.app')
    @section('content')
    <div class="container">
        <h2 class="mb-4 fw-bold">Edit Procurement Request (Report #00{{ $req->garage_report_id }})</h2>
        <form action="{{ route('procurements.update', $req->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @foreach($allReqs->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $inventory)
                        <div class="col-md-6">
                            <div class="card mb-3" style="height: 36rem">
                                <div class="card-body">
                                    <h5 class="fw-bold">{{ $inventory->inventory->name }}</h5>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1">
                                                <strong>Requested Qty:</strong> {{ $inventory->quantity }} <br>
                                                <strong>Available Stock:</strong> {{ $inventory->inventory->remaining_quantity }}
                                            </p>

                                            @if($inventory->inventory->remaining_quantity < $inventory->quantity)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @else
                                                <span class="badge bg-success">In Stock</span>
                                            @endif
                                        </div>

                                        @if($inventory->inventory->remaining_quantity < $inventory->quantity)
                                            <a href="{{ route('inventories.index') }}" class="btn btn-sm btn-warning ms-3 mt-1">
                                                <i class="bi bi-box-arrow-in-down"></i> Restock
                                            </a>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label>Status</label>
                                        <select name="statuses[{{ $inventory->id }}]" class="form-control status-select" data-target="supplierFields{{ $inventory->id }}" required>
                                            @if($inventory->inventory->remaining_quantity >= $inventory->quantity)
                                                <option value="from_stock" 
                                                    @if($inventory->inventory->remaining_quantity >= $inventory->quantity) selected @endif>
                                                    Use From Stock
                                                </option>
                                            @endif

                                            <option value="outsourced">Outsource</option>
                                            {{-- <option value="out_of_stock" 
                                                @if($inventory->inventory->remaining_quantity < $inventory->quantity) selected @endif>
                                                Out of Stock
                                            </option> --}}
                                        </select>
                                    </div>

                                    <div id="supplierFields{{ $inventory->id }}" class="supplier-fields" style="display: none;">
                                        <div class="mb-3">
                                            <label>Supplier</label>
                                            <select name="suppliers[{{ $inventory->id }}]" class="form-control">
                                                <option value="">-- Select Supplier --</option>
                                                @if($inventory->inventory->supplier)
                                                    <option value="{{ $inventory->inventory->supplier->id }}" >
                                                        {{ $inventory->inventory->supplier->name }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Price</label>
                                            <input type="number" name="prices[{{ $inventory->id }}]" step="0.01" class="form-control">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label>Remark</label>
                                        <textarea name="remarks[{{ $inventory->id }}]" class="form-control"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label>Upload Bill (PDF/Image)</label>
                                        <input type="file" name="bills[{{ $inventory->id }}]" class="form-control" accept=".pdf,image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-success">Save All</button>
            </div>
        </form>
    </div>

    <script>
        function toggleSupplierFields(selectEl) {
            let targetId = selectEl.dataset.target;
            let supplierFields = document.getElementById(targetId);

            if (selectEl.value === 'outsourced') {
                supplierFields.style.display = 'block';
            } else {
                supplierFields.style.display = 'none';
            }
        }

        document.querySelectorAll('.status-select').forEach(select => {
            toggleSupplierFields(select);
            select.addEventListener('change', () => toggleSupplierFields(select));
        });
    </script>
    @endsection
@endif