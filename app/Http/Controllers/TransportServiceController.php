<?php

namespace App\Http\Controllers;

use App\Models\TransportService;
use App\Services\ErpApi;
use Illuminate\Http\Request;

class TransportServiceController extends Controller
{
    public function store(Request $request, ErpApi $erp)
     {
        $data = $request->validate([
            'type' => 'required|in:shuttle,transfers,office,personal',
            'vehicle_id' => 'required|exists:vehicles,id',
            'employee_id' => 'required|integer',
            'assigned_start_at' => 'required|date',
            'assigned_end_at' => 'nullable|date|after_or_equal:assigned_start_at',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'passenger_count' => 'required|integer|min:1',
            'trip_code' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        // Save in FMS
        $ts = TransportService::create([
            'type' => $data['type'],
            'vehicle_id' => $data['vehicle_id'],
            'assigned_start_at' => $data['assigned_start_at'],
            'assigned_end_at' => $data['assigned_end_at'] ?? null,
            'pickup_location' => $data['pickup_location'] ?? null,
            'dropoff_location' => $data['dropoff_location'],
            'passenger_count' => $data['passenger_count'],
            'trip_code' => $data['trip_code'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        $ts->load('vehicle');

        $chauffers = session('chauffers') ?? []; 
        $employee = collect($chauffers)->firstWhere('employee_id', $data['employee_id']);

        // Payload for Admin-ERP
        $payload = [
            'source_id' => $ts->id,
            'type' => $ts->type,
            'vehicle_no' => $ts->vehicle?->reg_no,
            'employee_id' => $data['employee_id'],
            'chauffer_phone' => $employee?->whatsapp_number,
            'chauffer_name' => $employee?->preferred_name,
            'assigned_start_at' => $ts->assigned_start_at,
            'assigned_end_at' => $ts->assigned_end_at,
            'pickup_location' => $ts->pickup_location,
            'dropoff_location' => $ts->dropoff_location,
            'passenger_count' => $ts->passenger_count,
            'note' => $ts->note,
        ];

        try {
            $erp->createTransport($payload);
        } catch (\Throwable $e) {
            \Log::error('Admin-ERP sync failed (transport store)', [
                'source_id' => $ts->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Saved in FMS but Admin-ERP sync failed.');
        }

        return back()->with('success', 'Transport service added + synced to Admin-ERP.');
    }

    public function update(Request $request, TransportService $transportService, ErpApi $erp)
    {
        $data = $request->validate([
            'type' => 'required|in:shuttle,transfers,office,personal',
            'vehicle_id' => 'required|exists:vehicles,id',
            'employee_id' => 'required|integer',
            'assigned_start_at' => 'required|date',
            'assigned_end_at' => 'nullable|date|after_or_equal:assigned_start_at',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'passenger_count' => 'required|integer|min:1',
            'trip_code' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $transportService->update([
            'type' => $data['type'],
            'vehicle_id' => $data['vehicle_id'],
            'assigned_start_at' => $data['assigned_start_at'],
            'assigned_end_at' => $data['assigned_end_at'] ?? null,
            'pickup_location' => $data['pickup_location'] ?? null,
            'dropoff_location' => $data['dropoff_location'],
            'passenger_count' => $data['passenger_count'],
            'trip_code' => $data['trip_code'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        $transportService->load('vehicle');

        $employee = collect($this->employees)->firstWhere('employee_id', $data['employee_id']);

        $payload = [
            'type' => $transportService->type,
            'vehicle_no' => $transportService->vehicle?->reg_no,
            'employee_id' => $data['employee_id'],
            'chauffer_phone' => $employee['whatsapp_number'] ?? null,
            'chauffer_name' => $employee['preferred_name'] ?? null,
            'assigned_start_at' => $transportService->assigned_start_at,
            'assigned_end_at' => $transportService->assigned_end_at,
            'pickup_location' => $transportService->pickup_location,
            'dropoff_location' => $transportService->dropoff_location,
            'passenger_count' => $transportService->passenger_count,
            'note' => $transportService->note,
        ];

        try {
            $erp->updateTransport($transportService->id, $payload);
        } catch (\Throwable $e) {
            \Log::error('Admin-ERP sync failed (transport update)', [
                'source_id' => $transportService->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Updated in FMS but Admin-ERP sync failed.');
        }

        return back()->with('success', 'Transport service updated + synced to Admin-ERP.');
    }

    public function destroy(TransportService $transportService, Request $request, ErpApi $erp)
    {
        $validated = $request->validate([
            'delete_note' => 'required|string|min:3',
        ]);

        $sourceId = $transportService->id;

        $transportService->update([
            'delete_note' => $validated['delete_note'],
            'deleted_by' => auth()->id(),
        ]);

        $transportService->delete();

        try {
            $erp->deleteTransport($sourceId);
        } catch (\Throwable $e) {
            \Log::error('Admin-ERP sync failed (transport delete)', [
                'source_id' => $sourceId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Deleted in FMS but Admin-ERP sync failed.');
        }

        return back()->with('success', 'Transport service deleted + synced to Admin-ERP.');
    }
}