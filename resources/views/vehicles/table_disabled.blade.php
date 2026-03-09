<table id="disabledVehiclesTable" class="table table-striped table-hover mb-0">
    <thead class="table-dark">
        <tr>
            <th>Reg No</th>
            <th>Make</th>
            <th>Model</th>
            <th>Type</th>
            <th>Fuel</th>
            <th>Transmission</th>
            <th>Ownership</th>
            <th>Company</th>
            <th width="160">Actions</th>
        </tr>
    </thead>

    <tbody>
        @foreach($vehicles as $vehicle)
            <tr class="table-danger">
                <td>{{ $vehicle->reg_no }}</td>
                <td>{{ $vehicle->make }}</td>
                <td>{{ $vehicle->model }}</td>
                <td>{{ $vehicle->vehicleType->type_name }}</td>
                <td>{{ $vehicle->fuelType->fuel_name }}</td>
                <td>{{ $vehicle->transmission->transmission_name }}</td>
                <td>{{ $vehicle->ownershipType->ownership_name }}</td>
                <td>{{ $vehicle->company->name }}</td>

                <td>
                    <button class="btn btn-sm btn-success enable-btn"
                            data-id="{{ $vehicle->id }}"
                            data-name="{{ $vehicle->reg_no }}">
                        <i class="bi bi-check-circle"></i> Enable
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
