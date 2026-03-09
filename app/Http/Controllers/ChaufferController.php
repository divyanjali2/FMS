<?php

namespace App\Http\Controllers;

use App\Models\Chauffer;
use App\Services\ErpApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChaufferController extends Controller
{
    public function store(Request $request, ErpApi $erp)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        // If you don't want FMS save, remove next line.
        $c = Chauffer::create($data);

        try {
            $erp->upsertChauffer($data);
        } catch (\Throwable $e) {
            Log::error('ERP sync failed (chauffer store)', ['err' => $e->getMessage()]);
            return back()->with('error', 'Chauffer sync to Admin-ERP failed.');
        }

        return back()->with('success', 'Chauffer saved in Admin-ERP.');
    }

    public function update(Request $request, Chauffer $chauffer, ErpApi $erp)
    {
        $oldPhone = $chauffer->phone_number;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        // If you don't want FMS update, remove next line.
        $chauffer->update($data);

        try {
            $erp->upsertChauffer($data);

            // if phone changed, delete old one in ERP
            if ($oldPhone !== $data['phone_number']) {
                $erp->deleteChauffer($oldPhone);
            }
        } catch (\Throwable $e) {
            \Log::error('ERP sync failed (chauffer update)', ['err' => $e->getMessage()]);
            return back()->with('error', 'Chauffer sync update to Admin-ERP failed.');
        }

        return back()->with('success', 'Chauffer updated in Admin-ERP.');
    }

    public function destroy(Chauffer $chauffer, ErpApi $erp)
    {
        $phone = $chauffer->phone_number;

        // If you don't want FMS delete, remove next line.
        $chauffer->delete();

        try {
            $erp->deleteChauffer($phone);
        } catch (\Throwable $e) {
            \Log::error('ERP sync failed (chauffer delete)', ['err' => $e->getMessage()]);
            return back()->with('error', 'Chauffer delete sync to Admin-ERP failed.');
        }

        return back()->with('success', 'Chauffer deleted in Admin-ERP.');
    }
}