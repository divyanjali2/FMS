<?php

namespace App\Http\Controllers;

use App\Models\InventoryType;
use Illuminate\Http\Request;

class InventoryTypeController extends Controller
{
    public function index()
    {
        $types = InventoryType::latest()->get();
        return view('inventory-types.index', compact('types'));
    }

    public function create()
    {
        return view('inventory-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:inventory_types,name',
            'description' => 'nullable|string',
        ]);

        InventoryType::create($request->all());

        return redirect()->route('inventory-types.index')
            ->with('success', 'Type added successfully.');
    }

    public function edit(InventoryType $inventoryType)
    {
        return view('inventory-types.edit', compact('inventoryType'));
    }

    public function update(Request $request, InventoryType $inventoryType)
    {
        $request->validate([
            'name' => 'required|unique:inventory_types,name,' . $inventoryType->id,
            'description' => 'nullable|string',
        ]);

        $inventoryType->update($request->all());

        return redirect()->route('inventory-types.index')
            ->with('success', 'Type updated successfully.');
    }

    public function destroy(InventoryType $inventoryType)
    {
        $inventoryType->delete();

        return redirect()->route('inventory-types.index')
            ->with('success', 'Type deleted successfully.');
    }
}
