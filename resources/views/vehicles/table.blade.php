<div class="card shadow-sm border-0">
    <div class="table-responsive card-body p-3">
        <table id="vehiclesTable" class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Reg No</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Transmission</th>
                    <th>Ownership</th>
                    <th>Company</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>

            <tbody>
              @forelse($vehicles as $vehicle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>                        
                        <td>{{ $vehicle->reg_no }}</td>
                        <td>{{ $vehicle->make }}</td>
                        <td>{{ $vehicle->model }}</td>
                        <td>{{ $vehicle->vehicleType->type_name ?? '' }}</td>
                        <td>{{ $vehicle->vehicleCategory->name ?? '' }}</td>
                        <td>{{ $vehicle->transmission->transmission_name ?? '' }}</td>
                        <td>{{ $vehicle->ownershipType->ownership_name ?? '' }}</td>
                        <td>{{ $vehicle->company->name ?? '' }}</td>

                        <td>
                            @if($vehicle->status === 'active')
                            <div class="btn-group gap-2">

                                <!-- View -->
                                <button class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#vehicleModal{{ $vehicle->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Edit -->
                                <a href="{{ route('vehicles.edit', $vehicle->id) }}" 
                                class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <!-- Disable -->
                                <button type="button" 
                                        class="btn btn-sm btn-danger disable-btn"
                                        data-id="{{ $vehicle->id }}"
                                        data-name="{{ $vehicle->reg_no }}">
                                    <i class="bi bi-slash-circle"></i>
                                </button>

                            </div>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                    </tr>
                @empty

                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            No vehicles found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>
