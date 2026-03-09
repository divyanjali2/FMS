@if(auth()->user()->hasPermission('manage_procurements'))
@extends('layouts.app')
    @section('content')
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold">Inventory</h3>
                <div>
                    {{-- Go Back to Procurement --}}
                    <a href="{{ route('procurements.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back to Procurement
                    </a>

                    {{-- Restock Button --}}
                    <button type="button" class="btn fw-bold" data-bs-toggle="modal" data-bs-target="#restockModal" style="background-color: #124704; color: white;">
                        <i class="bi bi-arrow-repeat me-1"></i> Restock
                    </button>

                    {{-- Add Inventory Button --}}
                    <a href="{{ route('inventories.create') }}" class="btn btn-primary ms-2">
                        <i class="bi bi-plus-circle me-1"></i> Add Inventory
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle p-3" id="inventoriesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Available Quantity</th>
                                    <th>Remaining Quantity</th>
                                    <th>Minimum Stock Level</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventories as $item)
                                <tr @if($item->remaining_quantity <= $item->min_stock_level) class="table-danger" @endif>
                                    <td>{{ $item->item_code }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->available_quantity }}</td>

                                    {{-- Remaining Quantity --}}
                                    <td class="fw-bold">
                                        {{ $item->remaining_quantity }}
                                    </td>

                                    <td>{{ $item->min_stock_level }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->supplier->name }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('inventories.show', $item->id) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('inventories.edit', $item->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('manage_users'))
                                            <form action="{{ route('inventories.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf 
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this item?')" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    {{-- <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-box-seam"></i> No inventory items found
                                    </td> --}}
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Restock Modal -->
            <div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="restockModalLabel">Restock Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('inventories.restock') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Name</th>
                                <th>Remaining</th>
                                <th>Min Stock</th>
                                <th>Add Quantity</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($allItems as $item)
                                <tr @if($item->remaining_quantity <= $item->min_stock_level) class="table-danger" @endif>
                                    <td>{{ $item->item_code }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td class="@if($item->remaining_quantity <= $item->min_stock_level) text-danger fw-bold @endif">
                                        {{ $item->remaining_quantity }}
                                    </td>
                                    <td>{{ $item->min_stock_level }}</td>
                                    <td>
                                        <input type="number" name="restock[{{ $item->id }}]" 
                                            class="form-control" min="0" placeholder="Enter Stock Amount">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="bi bi-box-seam"></i> No inventory items found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>

                        <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Update Stock
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            {{-- <div class="mt-3">
                {{ $inventories->links('pagination::bootstrap-5') }}
            </div> --}}
        </div>
    @endsection
@endif

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#inventoriesTable').DataTable({
            paging: true,        
            ordering: false,    
            info: false,         
            lengthChange: true,  
            searching: true   
        });
    });
</script>