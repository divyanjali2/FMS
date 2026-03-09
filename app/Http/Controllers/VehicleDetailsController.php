<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\StreamedResponse;

class VehicleDetailsController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::query()
            ->where('status', '!=', 'disabled')   
            ->orderBy('created_at', 'asc')
            ->get(['id', 'reg_no', 'emi_number', 'emi_date']);

        return view('vehicle-details.index', compact('vehicles'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'emi_number' => ['nullable', 'string', 'max:255'],
            'emi_date'   => ['nullable', 'date'],
        ]);

        $vehicle->update($data);

        return back()->with('success', 'Vehicle EMI details updated.');
    }


    public function export()
    {
        $vehicles = Vehicle::orderBy('reg_no')
            ->get(['reg_no','emi_number','emi_date']);

        $filename = "vehicle_details.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate",
            "Expires" => "0"
        ];

        $callback = function() use ($vehicles) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Vehicle No',
                'EMI Number',
                'Date'
            ]);

            foreach ($vehicles as $v) {
                fputcsv($file, [
                    $v->reg_no,
                    $v->emi_number,
                    $v->emi_date
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}