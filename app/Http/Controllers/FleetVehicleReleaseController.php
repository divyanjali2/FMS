<?php

namespace App\Http\Controllers;

use App\Models\FleetPostCheck;
use App\Models\Rental;
use App\Models\FleetVehicleRelease;
use Illuminate\Http\Request;

class FleetVehicleReleaseController extends Controller
{
    public function show($inspectionId)
    {
        $postChecks = FleetPostCheck::with(['issue', 'fault', 'gmWorkStatus', 'inspection.vehicle'])
            ->where('inspection_id', $inspectionId)
            ->get();

        $inspection = $postChecks->first()->inspection ?? null;

        return view('fleet-vehicle-releases.show', compact('postChecks', 'inspection'));
    }

    public function store(Request $request, $inspectionId)
    {
        $postChecks = FleetPostCheck::with('inspection.vehicle')->where('inspection_id', $inspectionId)->get();

        foreach ($postChecks as $postCheck) {
            // Create or update vehicle release
            FleetVehicleRelease::updateOrCreate(
                ['fleet_post_check_id' => $postCheck->id],
                ['status' => 'vehicle_release']
            );

            if ($postCheck->inspection && $postCheck->inspection->vehicle) {
                $vehicleId = $postCheck->inspection->vehicle->id;

                // Update related rentals
                Rental::where('vehicle_id', $vehicleId)
                    ->whereIn('status', ['arrived', 'emergency_completed'])
                    ->update(['status' => 'completed']);

                $postCheck->inspection->update([
                    'vehicle_status' => 'completed'
                ]);
            }
        }

        return redirect()->route('fleet-decisions.index')->with('success', 'Vehicle release status saved successfully!');
    }
}
