<?php

namespace App\Http\Controllers;

use App\Models\Fault;
use App\Models\DefectCategory;
use Illuminate\Http\Request;

class FaultController extends Controller
{
    public function index()
    {
        $faults = Fault::with('category')->latest()->paginate(10);
        return view('faults.index', compact('faults'));
    }

    public function create()
    {
        $categories = DefectCategory::all();
        return view('faults.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:interior,exterior,tires & wheels,glass & lights,odometer & fuel,engine & fluid,accessories & documents',
            'category_id' => 'required|exists:defect_categories,id',
        ]);

        Fault::create($request->all());

        return redirect()->route('faults.index')->with('success', 'Fault created successfully.');
    }

    public function edit(Fault $fault)
    {
        $categories = DefectCategory::all();
        return view('faults.edit', compact('fault', 'categories'));
    }

    public function update(Request $request, Fault $fault)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:interior,exterior,tires & wheels,glass & lights,odometer & fuel,engine & fluid,accessories & documents',
            'category_id' => 'required|exists:defect_categories,id',
        ]);

        $fault->update($request->all());

        return redirect()->route('faults.index')->with('success', 'Fault updated successfully.');
    }

    public function destroy(Fault $fault)
    {
        $fault->delete();
        return redirect()->route('faults.index')->with('success', 'Fault deleted successfully.');
    }
}

