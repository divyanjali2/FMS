<?php

use App\Http\Controllers\TransportServiceController;
use App\Http\Controllers\Api\VehicleLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_token')->get('/vehicles/by-reg-no', [VehicleLookupController::class, 'byRegNo']);

Route::post('/transport-services/validate-vehicle', 
    [TransportServiceController::class, 'validateVehicleForTransport']
);