<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryType;
use App\Models\Supplier;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::paginate(10);

        $allItems = Inventory::all();

        return view('inventories.index', compact('inventories', 'allItems'));
    }

    public function create()
    {
        $lastInventory = Inventory::latest('id')->first();
        $nextCode = $lastInventory ? str_pad($lastInventory->id + 1, 3, '0', STR_PAD_LEFT) : '001';
        $suppliers = Supplier::all();
        $inventoryTypes = InventoryType::all();

        return view('inventories.create', compact('nextCode', 'suppliers', 'inventoryTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'available_quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:20',
            'min_stock_level' => 'nullable|integer|min:0',
            'inventory_type_id' => 'required|exists:inventory_types,id',
        ]);

        // Auto-generate item_code
        $lastInventory = Inventory::latest('id')->first();
        $nextCode = $lastInventory ? str_pad($lastInventory->id + 1, 3, '0', STR_PAD_LEFT) : '001';

        Inventory::create($validated + $request->only([
            'description', 'purchase_date', 'supplier_id'
        ]) + [
            'item_code' => $nextCode,
            'remaining_quantity' => $request->available_quantity 
        ]);

        return redirect()->route('inventories.index')->with('success', 'Item added successfully.');
    }

    public function show(Inventory $inventory)
    {
        return view('inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        $suppliers = Supplier::all();
        $inventoryTypes = InventoryType::all();
        return view('inventories.edit', compact('inventory', 'suppliers','inventoryTypes'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'item_code' => 'required|unique:inventories,item_code,' . $inventory->id,
            'name' => 'required|string|max:150',
            'available_quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
            'min_stock_level' => 'nullable|integer|min:0',
            'inventory_type_id' => 'required|exists:inventory_types,id',
        ]);

        $inventory->update($validated + $request->only([
            'description', 'purchase_date', 'supplier_id'
        ]));

        return redirect()->route('inventories.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventories.index')->with('success', 'Item deleted successfully.');
    }

    public function restock(Request $request)
    {
        $restockData = $request->input('restock', []);

        foreach ($restockData as $id => $quantity) {
            if (!empty($quantity) && $quantity > 0) {
                $inventory = Inventory::find($id);
                if ($inventory) {
                    $inventory->remaining_quantity += $quantity;
                    $inventory->available_quantity += $quantity;
                    $inventory->save();
                }
            }
        }

        return redirect()->route('inventories.index')->with('success', 'Stock updated successfully!');
    }
}

