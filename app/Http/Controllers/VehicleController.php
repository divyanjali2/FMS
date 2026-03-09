<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\Company;
use App\Models\VehicleAttribute;
use App\Models\VehicleCategory;
use App\Models\VehicleFreeze;
use App\Models\OwnershipType;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        // Get IDs of all vehicles that have any freeze record
        $frozenVehicleIds = VehicleFreeze::pluck('vehicle_id')->toArray();

        // Active Vehicles (excluding frozen)
        $vehicles = Vehicle::whereNotIn('id', $frozenVehicleIds)
            ->where('status', 'active')
            ->orderBy('reg_no')
            ->get();

        // Disabled Vehicles (excluding frozen)
        $disabledVehicles = Vehicle::whereNotIn('id', $frozenVehicleIds)
            ->where('status', 'disabled')
            ->orderBy('reg_no')
            ->get();

        // All freezes
        $freezes = VehicleFreeze::with('vehicle')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('vehicles.index', compact('vehicles', 'disabledVehicles', 'freezes'));
    }


    public function create()
    {
        $vehicleTypes = VehicleType::all();
        $vehicleCategories = VehicleCategory::all();
        $fuelTypes = FuelType::all();
        $transmissions = Transmission::all();
        $ownershipTypes = OwnershipType::all();
        $companies = Company::all();
        $vehicleAttributes = VehicleAttribute::all();

        return view('vehicles.create', compact('vehicleCategories','vehicleTypes','fuelTypes','transmissions','ownershipTypes','companies','vehicleAttributes'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'reg_no' => strtoupper($request->reg_no_part1 . '-' . $request->reg_no_part2)
            ]);

            $validated = $request->validate([
                'reg_no' => 'required|unique:vehicles,reg_no',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
                'make' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year_of_manufacture' => 'required|integer|min:1900|max:' . date('Y'),
                'color' => 'nullable|string|max:50',
                'vin' => 'nullable|string|max:100',
                'engine_no' => 'nullable|string|max:100',
                'fuel_type_id' => 'required|exists:fuel_types,id',
                'transmission_id' => 'required|exists:transmissions,id',
                'seating_capacity' => 'nullable|integer|min:1',
                'odometer_at_registration' => 'nullable|integer|min:0',
                'ownership_type_id' => 'required|exists:ownership_types,id',
                'owner_name' => 'nullable|string|max:150',
                'owner_phone' => 'nullable|string|max:150',
                'lease_start' => 'nullable|date',
                'lease_end' => 'nullable|date|after_or_equal:lease_start',
                'insurance_provider' => 'nullable|string|max:150',
                'insurance_policy_no' => 'nullable|string|max:100',
                'insurance_expiry' => 'nullable|date',
                'emission_test_expiry' => 'nullable|date',
                'revenue_license_expiry' => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'depreciation_rate' => 'nullable|numeric|min:0|max:100',
                // 'current_value' => 'nullable|numeric|min:0',
                // 'loan_emi_details' => 'nullable|string',
                'company_id' => 'nullable|exists:companies,id', 
                // Docs & Images
                'revenue_license_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'insurance_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'emission_test_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'other_doc_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'vehicle_front' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_back' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_left' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_right' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'remarks' => 'nullable|string',
            ]);

            // 🔹 Conditional validation for Company Owned
            $ownershipType = OwnershipType::find($validated['ownership_type_id']);
            if ($ownershipType && strtolower($ownershipType->name) === 'company owned') {
                $request->validate([
                    'company_name' => 'required|string|in:EV,Rent a Car,Ellite Rent a Car',
                    'company_logo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
                ]);
            }

            // Handle file uploads
            $fileFields = [
                'revenue_license_file' => 'documents/revenue_license',
                'insurance_file' => 'documents/insurance',
                'emission_test_file' => 'documents/emission_test',
                'other_doc_file' => 'documents/other_doc',
                'vehicle_front' => 'images/front',
                'vehicle_back' => 'images/back',
                'vehicle_left' => 'images/left',
                'vehicle_right' => 'images/right',
                'company_logo' => 'company/logos',
            ];

            $currentYear = date('Y'); 
            $baseFolder = $currentYear . ' - vehicle details';

            foreach ($fileFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $validated['reg_no'] . '_' . $field . '.' . $extension;

                    $path = $file->storeAs("$baseFolder/$folder", $fileName, 'public');
                    $validated[$field] = 'storage/' . $path; // Save relative path
                }
            }

            $vehicle = Vehicle::create($validated);

            return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create vehicle: ' . $e->getMessage()]);
        }
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicleTypes = VehicleType::all();
        $vehicleCategories = VehicleCategory::all();
        $fuelTypes = FuelType::all();
        $transmissions = Transmission::all();
        $ownershipTypes = OwnershipType::all();
        $companies = Company::all();
        $vehicleAttributes = VehicleAttribute::all();

        return view('vehicles.edit', compact('vehicleCategories','vehicle','vehicleTypes','fuelTypes','transmissions','ownershipTypes','companies','vehicleAttributes'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        try {
            \Log::info('Updating vehicle ID: ' . $vehicle->id);

            $validated = $request->validate([
                'reg_no' => 'required|unique:vehicles,reg_no,' . $vehicle->id,
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
                'make' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year_of_manufacture' => 'nullable|integer|min:1900|max:' . date('Y'),
                'color' => 'nullable|string|max:50',
                'vin' => 'nullable|string|max:100|unique:vehicles,vin,' . $vehicle->id,
                'engine_no' => 'nullable|string|max:100|unique:vehicles,engine_no,' . $vehicle->id,
                'fuel_type_id' => 'required|exists:fuel_types,id',
                'transmission_id' => 'required|exists:transmissions,id',
                'seating_capacity' => 'nullable|integer|min:1',
                'odometer_at_registration' => 'nullable|integer|min:0',
                'ownership_type_id' => 'required|exists:ownership_types,id',
                'owner_name' => 'nullable|string|max:150',
                'owner_phone' => 'nullable|string|max:150',
                'lease_start' => 'nullable|date',
                'lease_end' => 'nullable|date|after_or_equal:lease_start',
                'insurance_provider' => 'nullable|string|max:150',
                'insurance_policy_no' => 'nullable|string|max:100',
                'insurance_expiry' => 'nullable|date',
                'emission_test_expiry' => 'nullable|date',
                'revenue_license_expiry' => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'depreciation_rate' => 'nullable|numeric|min:0|max:100',
                // 'current_value' => 'nullable|numeric|min:0',
                // 'loan_emi_details' => 'nullable|string',
                'company_id' => 'nullable|exists:companies,id', 
                // Files
                'revenue_license_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'insurance_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'emission_test_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'other_doc_file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
                'vehicle_front' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_back' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_left' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'vehicle_right' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'remarks' => 'nullable|string',
            ]);

            // Handle file uploads
            $fileFields = [
                'revenue_license_file' => 'documents/revenue_license',
                'insurance_file' => 'documents/insurance',
                'emission_test_file' => 'documents/emission_test',
                'other_doc_file' => 'documents/other_doc',
                'vehicle_front' => 'images/front',
                'vehicle_back' => 'images/back',
                'vehicle_left' => 'images/left',
                'vehicle_right' => 'images/right',
            ];

            $currentYear = date('Y');
            $baseFolder = $currentYear . ' - vehicle details';

            foreach ($fileFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $validated['reg_no'] . '_' . $field . '.' . $extension;

                    // Store under "2025 - vehicle details/..."
                    $path = $file->storeAs("$baseFolder/$folder", $fileName, 'public');

                    $validated[$field] = asset('storage/' . $path);
                }
            }

            $vehicle->update($validated);

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehicle updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update vehicle: ' . $e->getMessage()]);
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        try {
            \Log::info('Deleting vehicle ID: ' . $vehicle->id);
            
            $vehicle->delete();
            \Log::info('Vehicle deleted successfully');

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehicle deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error deleting vehicle: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->withErrors(['error' => 'Failed to delete vehicle: ' . $e->getMessage()]);
        }
    }

    public function disable(Vehicle $vehicle)
    {
        try {
            $vehicle->update(['status' => 'disabled']);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
