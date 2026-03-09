@if(auth()->user()->hasPermission('manage_procurements'))
@csrf
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row">
           <div class="col-md-4 mb-3">
                <label class="form-label">Item Code</label>
                <input type="text" name="item_code" class="form-control" value="{{ old('item_code', $inventory->item_code ?? $nextCode) }}" readonly>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $inventory->name ?? '') }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Inventory Type</label>
                <select name="inventory_type_id" class="form-control select2" required>
                    <option value="">-- Select Type --</option>
                    @foreach($inventoryTypes as $type)
                        <option value="{{ $type->id }}" 
                            {{ old('inventory_type_id', $inventory->inventory_type_id ?? '') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $inventory->description ?? '') }}</textarea>
            </div>
           <div class="col-md-3 mb-3">
                <label class="form-label">Available Quantity</label>
                <input type="number" name="available_quantity" class="form-control" value="{{ old('available_quantity', $inventory->available_quantity ?? '') }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Minimum Stock Level</label>
                <input type="number" name="min_stock_level" class="form-control" value="{{ old('min_stock_level', $inventory->min_stock_level ?? 0) }}" min="0">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Unit</label>
                <select name="unit" class="form-control" required>
                    <option value="pcs" {{ old('unit', $inventory->unit ?? '') == 'pcs' ? 'selected' : '' }}>Pieces</option>
                    <option value="kg" {{ old('unit', $inventory->unit ?? '') == 'kg' ? 'selected' : '' }}>Kilograms</option>
                    <option value="liters" {{ old('unit', $inventory->unit ?? '') == 'liters' ? 'selected' : '' }}>Liters</option>
                    <option value="boxes" {{ old('unit', $inventory->unit ?? '') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                    <option value="packs" {{ old('unit', $inventory->unit ?? '') == 'packs' ? 'selected' : '' }}>Packs</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $inventory->purchase_date ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-control select2" required>
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" 
                            {{ old('supplier_id', $inventory->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-end">
        <a href="{{ route('inventories.index') }}" class="btn btn-secondary me-2">
            <i class="bi bi-x-circle me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-1"></i> Save
        </button>
    </div>
</div>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Search...",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
