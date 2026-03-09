<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    public function index()
    {
        $receivers = User::whereIn('position', [
            'gm',
            'md',
            'hod',
        ])
        ->orderBy('name')
        ->get(['id', 'name', 'position', 'department']);

        $uploadedReports = Report::where('uploaded_by', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        $receivedReports = Report::where('user_id', auth()->id())
            ->orderBy('accepted', 'asc') 
            ->with(['uploader'])
            ->get();

        $pendingCount = $receivedReports->where('accepted', false)->count();

        return view('reports.index', compact('receivers', 'uploadedReports', 'receivedReports', 'pendingCount'));
    }

    public function accept(Report $report)
    {
        $report->update([
            'accepted' => true,
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'file_url' => asset('storage/reports/' . basename($report->file_path)),
        ]);
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportName' => 'required|string|max:255',
            'reportDate' => 'required|date',
            'effectiveMonth' => 'required|integer|between:1,12',
            'effectiveWeek' => 'required|integer|between:1,5',
            'send_to_user_id' => 'required|exists:users,id',
            'remark' => 'nullable|string',
            'pdfFile' => 'required',
        ]);

        $titleSlug = Str::slug($validated['reportName']); 
        $datePart = Carbon::parse($validated['reportDate'])->format('Y-m-d');
        $uploader = Str::slug(auth()->user()->name);     
        $extension = $request->file('pdfFile')->getClientOriginalExtension();
        $fileName = "{$titleSlug}-{$datePart}-{$uploader}.{$extension}";

        $path = $request->file('pdfFile')->storeAs('reports', $fileName, 'public');

        Report::create([
            'report_title' => $validated['reportName'],
            'report_date' => $validated['reportDate'],
            'report_month' => $validated['effectiveMonth'],
            'report_week' => $validated['effectiveWeek'],
            'file_path' => $path,             
            'user_id' => $validated['send_to_user_id'],     
            'uploaded_by' => auth()->id(),
            'remark' => $validated['remark'] ?? null,
            'accepted' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Report submitted successfully.');
    }

    public function requests()
    {
        return view('reports.requests');
    }

    public function view(Report $report, $filename)
    {
        if ($filename !== basename($report->file_path)) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $report->file_path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mimeType = File::mimeType($fullPath);

        $disposition = $mimeType === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . basename($fullPath) . '"',
        ]);
    }
}
