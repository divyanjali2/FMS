<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\Inspection;
use App\Models\Rental;
use App\Models\User;
use App\Models\Procurement;
use App\Models\Cashier;
use App\Models\AccountantReview;
use Spatie\Activitylog\Models\Activity;
use App\Models\Report;
use App\Models\VehicleFreeze;

class DashboardController extends Controller
{
    public function index()
    {

    $pendingReportsCount = Report::where('user_id', auth()->id())
        ->where('accepted', false)
        ->count();

        $today = Carbon::today();
        $oneMonth = $today->copy()->addMonth();
        $twoMonths = $today->copy()->addMonths(2);

        $loggedInToday = User::whereDate('last_login_at', Carbon::today())
            ->where('id', '!=', 1) 
            ->get();

        $recentActivity = Activity::with(['causer', 'subject'])
            ->where('causer_id', '!=', 1)
            ->latest()
            ->take(50)
            ->get();

        // Vehicles expiring within 1 month
        $expiringWithinOneMonth = Vehicle::where('status', '!=', 'disabled')
            ->whereDoesntHave('freezes') 
            ->where(function($query) use ($today, $oneMonth) {
                $query->whereBetween('insurance_expiry', [$today, $oneMonth])
                    ->orWhereBetween('emission_test_expiry', [$today, $oneMonth])
                    ->orWhereBetween('revenue_license_expiry', [$today, $oneMonth]);
            })
            ->get()
            ->sortBy(function($vehicle) {
                $dates = collect([
                    $vehicle->insurance_expiry,
                    $vehicle->emission_test_expiry,
                    $vehicle->revenue_license_expiry
                ])->filter();

                return $dates->map(fn($date) => Carbon::parse($date))->min();
            });

        // Vehicles expiring in 1–2 months
        $expiringInNextTwoMonths = Vehicle::where('status', '!=', 'disabled')
            ->whereDoesntHave('freezes') 
            ->where(function($query) use ($oneMonth, $twoMonths) {
                $query->whereBetween('insurance_expiry', [$oneMonth->copy()->addDay(), $twoMonths])
                    ->orWhereBetween('emission_test_expiry', [$oneMonth->copy()->addDay(), $twoMonths])
                    ->orWhereBetween('revenue_license_expiry', [$oneMonth->copy()->addDay(), $twoMonths]);
            })
            ->get();

        $sentToGarage = Inspection::with('vehicle')
            ->where('status', 'Sent to Garage')
            ->orderBy('created_at', 'desc')
            ->get();

        $procurements = Procurement::with('issueInventory.inventory')->latest()->take(20)->get();

        $cashiers = Cashier::with('vehicle')->latest()->take(20)->get();

        $rentals = Rental::with('vehicle')->latest()->take(20)->get();

        $accountantReviews = AccountantReview::with(['inspection', 'procurement'])->latest()->take(20)->get();

        return view('dashboard', compact('pendingReportsCount','recentActivity','expiringWithinOneMonth','expiringInNextTwoMonths','sentToGarage','procurements','cashiers','rentals','accountantReviews','loggedInToday'));    
    }
}
