<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefectCategory;
use App\Models\Supplier;

class DefectCategoryController extends Controller
{
    public function index()
    {
        $categories = DefectCategory::with('suppliers')->paginate(10);
        return view('defect_categories.index', compact('categories'));
    }

    public function create()
    {
        $suppliers = Supplier::all(); 
        return view('defect_categories.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:defect_categories,name',
            'description' => 'nullable|string',
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'exists:suppliers,id',
        ]);

        $category = DefectCategory::create($request->only('name', 'description'));

        // Sync suppliers
        if($request->suppliers) {
            $category->suppliers()->sync($request->suppliers);
        }

        return redirect()->route('defect_categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(DefectCategory $defectCategory)
    {
        $suppliers = Supplier::all(); // Get all suppliers
        $selectedSuppliers = $defectCategory->suppliers->pluck('id')->toArray(); 
        return view('defect_categories.edit', compact('defectCategory', 'suppliers', 'selectedSuppliers'));
    }

    public function update(Request $request, DefectCategory $defectCategory)
    {
        $request->validate([
            'name' => 'required|unique:defect_categories,name,' . $defectCategory->id,
            'description' => 'nullable|string',
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'exists:suppliers,id',
        ]);

        $defectCategory->update($request->only('name', 'description'));

        // Sync suppliers
        $defectCategory->suppliers()->sync($request->suppliers ?? []);

        return redirect()->route('defect_categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(DefectCategory $defectCategory)
    {
        $defectCategory->suppliers()->detach(); 
        $defectCategory->delete();
        return redirect()->route('defect_categories.index')->with('success', 'Category deleted successfully.');
    }
}
