<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\DefectCategory;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index()
    {
        $issues = Issue::with('category')->latest()->paginate(50);
        return view('issues.index', compact('issues'));
    }

    public function create()
    {
        $categories = DefectCategory::all();
        return view('issues.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:defect_categories,id',
        ]);

        Issue::create($request->all());

        return redirect()->route('issues.index')->with('success', 'Issue added successfully.');
    }

    public function edit(Issue $issue)
    {
        $categories = DefectCategory::all();
        return view('issues.edit', compact('issue', 'categories'));
    }

    public function update(Request $request, Issue $issue)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:defect_categories,id',
        ]);

        $issue->update($request->all());

        return redirect()->route('issues.index')->with('success', 'Issue updated successfully.');
    }

    public function destroy(Issue $issue)
    {
        $issue->delete();
        return redirect()->route('issues.index')->with('success', 'Issue deleted successfully.');
    }
}
