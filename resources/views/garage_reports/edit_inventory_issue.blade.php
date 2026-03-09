<div class="modal fade" id="assignInventoryModal-{{ $garageReport->id }}" tabindex="-1" aria-labelledby="assignInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('garage_reports.assign_inventory', $garageReport->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Inventory to Issues/Faults</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @foreach($inbuildIssues as $inbuild)
                        <h6>
                            @if($inbuild->issue_id)
                                <span class="badge bg-info">{{ \App\Models\Issue::find($inbuild->issue_id)->name }}</span>
                            @endif
                            @if($inbuild->fault_id)
                                <span class="badge bg-secondary">{{ \App\Models\Fault::find($inbuild->fault_id)->name }}</span>
                            @endif
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <select name="inventories[{{ $inbuild->id }}][inventory_id]" class="form-select select2" required>
                                    <option value="">Select Inventory</option>
                                    @foreach($inventories as $inv)
                                        <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->quantity }} {{ $inv->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="inventories[{{ $inbuild->id }}][quantity]" class="form-control" min="1" placeholder="Quantity" required>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
                @push('scripts')
                <script>
                    $(document).ready(function() {
                        $('.select2').each(function () {
                            $(this).select2({
                                placeholder: "Search...",
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $(this).closest('.modal') 
                            });
                        });
                    });
                </script>
                @endpush
            </div>
        </form>
    </div>
</div>
