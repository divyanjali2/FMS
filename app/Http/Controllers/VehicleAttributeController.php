<?php

namespace App\Http\Controllers;

use App\Models\VehicleAttribute;
use Illuminate\Http\Request;

class VehicleAttributeController extends Controller
{
    public function index()
    {
        $attributes = VehicleAttribute::all();
        return view('vehicle-attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('vehicle-attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);

        VehicleAttribute::create($request->all());

        return redirect()->route('vehicle-attributes.index')->with('success', 'Vehicle attribute added successfully.');
    }

    public function edit(VehicleAttribute $vehicle_attribute)
    {
        return view('vehicle-attributes.edit', compact('vehicle_attribute'));
    }

    public function update(Request $request, VehicleAttribute $vehicle_attribute)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);

        $vehicle_attribute->update($request->all());

        return redirect()->route('vehicle-attributes.index')->with('success', 'Vehicle attribute updated successfully.');
    }

    public function destroy(VehicleAttribute $vehicle_attribute)
    {
        $vehicle_attribute->delete();

        return redirect()->route('vehicle-attributes.index')->with('success', 'Vehicle attribute deleted successfully.');
    }
}

