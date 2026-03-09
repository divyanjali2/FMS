<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleLookupController extends Controller
{
    public function byRegNo(Request $request)
    {
        $regNo = trim((string) $request->query('reg_no', ''));

        if ($regNo === '') {
            return response()->json(['error' => 'invalid_request'], 400);
        }

        $vehicle = Vehicle::query()
            ->select(['reg_no', 'make', 'model'])
            ->where('reg_no', $regNo)
            ->first();

        if (!$vehicle) {
            return response()->json(['error' => 'vehicle_not_found'], 404);
        }

        return response()->json([
            'reg_no' => $vehicle->reg_no,
            'make'   => $vehicle->make,
            'model'  => $vehicle->model,
        ]);
    }
}