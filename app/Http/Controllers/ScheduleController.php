<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TransportService;
use App\Models\Chauffer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ScheduleController extends Controller
{
    public function index()
    {
        // $chauffers = Chauffer::latest()->get();

        $now = Carbon::now();

        $vehicles = Vehicle::query()
            ->where('status', '!=', 'disabled')
            ->whereDoesntHave('freezes', function ($q) use ($now) {
                $q->where('start_date', '<=', $now)
                  ->where(function ($qq) use ($now) {
                      $qq->whereNull('end_date')->orWhere('end_date', '>=', $now);
                  });
            })
            ->orderBy('reg_no')
            ->get();

        $transportServices = TransportService::with(['vehicle','chauffer'])
            ->latest()
            ->get();

        $chauffers = [];

        try {
            $response = Http::timeout(10)->get('http://127.0.0.1:9000/api/chauffers');

            if ($response->successful()) {
                $chauffers = $response->json();
            }
        } catch (\Throwable $e) {
            $chauffers = [];
        }

        return view('schedule.index', compact('chauffers', 'vehicles', 'transportServices'));
    }
}
