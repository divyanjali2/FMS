<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\GarageReportController;
use App\Http\Controllers\FleetDecisionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DefectCategoryController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\FaultController;
use App\Http\Controllers\AccountantController;
use App\Http\Controllers\GMController;
use App\Http\Controllers\FleetPostCheckController;
use App\Http\Controllers\FleetVehicleReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MDReviewController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\VehicleAttributeController;
use App\Http\Controllers\InventoryTypeController;
use App\Http\Controllers\VehicleBookingController;
use App\Http\Controllers\VehicleFreezeController;
use App\Http\Controllers\PaymentCoordinatorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ChaufferController;
use App\Http\Controllers\TransportServiceController;
use App\Http\Controllers\VehicleAnalyticsController;
use App\Http\Controllers\VehicleDetailsController;





Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::resource('users', UserController::class);

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Vehicles routes
    Route::resource('vehicles', VehicleController::class)->only([
        'index', 'create', 'store','edit', 'update', 'destroy','disable'
    ]);

    Route::resource('inspections', InspectionController::class);

    Route::get('inspections/create/{vehicle?}', [InspectionController::class, 'create'])->name('inspection.create');
    Route::post('inspections', [InspectionController::class, 'store'])->name('inspections.store');
    Route::patch('inspections/{inspection}/send-to-garage', [InspectionController::class,'updateStatus'])->name('inspections.sendToGarage');
    Route::post('inspections/{inspection}/send-to-garage', [InspectionController::class, 'sendToGarage'])->name('inspections.sendToGarage');

    Route::post('/emergency/{rentalId}/save-faults', [InspectionController::class, 'saveEmergencyFaults'])
        ->name('emergency.saveFaults');

    Route::prefix('garage-reports')->group(function () {
        Route::get('/', [GarageReportController::class,'index'])->name('garage_reports.index');
        Route::get('/{inspection}/create', [GarageReportController::class,'create'])->name('garage_reports.create');
        Route::post('/{inspection}', [GarageReportController::class,'store'])->name('garage_reports.store');
        Route::get('/inspection/{inspection}', [GarageReportController::class,'show'])->name('garage_reports.show');
        Route::get('/{garageReport}/edit', [GarageReportController::class,'edit'])->name('garage_reports.edit');
        Route::put('/{garageReport}', [GarageReportController::class,'update'])->name('garage_reports.update');
        Route::delete('/{garageReport}', [GarageReportController::class,'destroy'])->name('garage_reports.destroy');
        Route::post('/{garageReport}/assign_inventory', [GarageReportController::class, 'assignInventory'])->name('garage_reports.assign_inventory');
    });

    Route::post('fleet-post-checks/{inspection}/complete-all', [GarageReportController::class, 'completeAllFleetPostChecks'])->name('fleetPostChecks.completeAll');

    Route::prefix('fleet/decisions')->name('fleet-decisions.')->group(function () {
        Route::get('/', [FleetDecisionController::class, 'index'])->name('index');
        Route::get('/{garageReport}', [FleetDecisionController::class, 'show'])->name('show');
        Route::get('/{garageReport}/view', [FleetDecisionController::class, 'view'])->name('view');
        Route::post('/{garageReport?}/save', [FleetDecisionController::class, 'store'])->name('store');
        Route::post('/{garageReport}/send-back', [FleetDecisionController::class, 'sendBackToGarage'])->name('sendBack');
        Route::post('/payment/{cashier}', [FleetDecisionController::class, 'storePayment'])->name('fleet-decisions.payment');
        Route::get('/payment/{cashier}', [FleetDecisionController::class, 'payment'])->name('payment');
        Route::post('/payment/{cashier}', [FleetDecisionController::class, 'storePayment'])->name('storePayment');
        Route::post('/save-owner-repair', [FleetDecisionController::class, 'store'])->name('store.ownerRepair');
        Route::get('/payment/{cashier}/process', [FleetDecisionController::class, 'paymentPage'])->name('paymentPage');
    });

    Route::resource('inventories', InventoryController::class);
    Route::post('/inventories/restock', [InventoryController::class, 'restock'])->name('inventories.restock');

    Route::post('garage_reports/{garageReport}/assign_inventory', [GarageReportController::class, 'assignInventory'])->name('garage_reports.edit_inventory_issue');

    Route::resource('procurement', ProcurementController::class);

    Route::resource('suppliers', SupplierController::class);

    Route::prefix('procurements')->group(function () {
        Route::get('/', [ProcurementController::class, 'index'])->name('procurements.index');
        Route::get('/{id}/edit', [ProcurementController::class, 'edit'])->name('procurements.edit');
        Route::post('/{id}', [ProcurementController::class, 'update'])->name('procurements.update');
        Route::get('/grn/download/{inspection}/{supplier}', [ProcurementController::class, 'downloadGRN'])->name('procurements.grn.download');
    });

    Route::patch('/procurements/{id}/cancel-po', [ProcurementController::class, 'cancelPO'])->name('procurements.cancelPO');
    Route::patch('/procurements/{id}/recreate-po', [ProcurementController::class, 'recreatePO'])->name('procurements.recreatePO');

    Route::post('/fm-work-decisions/{inspection}/approve', [FleetDecisionController::class, 'approve'])->name('fm-work-decisions.approve');
    Route::post('/fleet-decisions/{report}/owner-repair', [FleetDecisionController::class, 'ownerRepair'])
    ->name('fleet-decisions.owner-repair');

    Route::resource('defect_categories', DefectCategoryController::class);

    Route::resource('faults', FaultController::class);
    Route::resource('issues', IssueController::class);


    Route::prefix('accountant')->group(function () {
        Route::get('/', [AccountantController::class, 'index'])->name('accountant.index');
        Route::get('/review/{inspection}', [AccountantController::class, 'show'])->name('accountant.show');
        Route::get('/po/{inspection}', [AccountantController::class, 'purchaseOrder'])->name('accountant.purchaseOrder');
        Route::post('/send-to-procurement/{inspection}', [AccountantController::class, 'sendToProcurement'])->name('accountant.sendToProcurement');
        Route::post('/procurements/{inspection}/grn', [ProcurementController::class, 'storeGRN'])->name('procurements.storeGRN');
        Route::get('/procurements/{inspection}/grn', [ProcurementController::class, 'viewGRN'])->name('procurements.viewGRN');
        Route::post('/procurements/{inspection}/store-grn', [ProcurementController::class, 'storeGRN'])->name('procurements.storeGRN');
        Route::get('/procurements/{inspection}/view-grn', [ProcurementController::class, 'viewGRN'])->name('procurements.viewGRN');
        Route::patch('/accountant/grn/{inspectionId}/approve', [AccountantController::class, 'approveGRN'])->name('accountant.approveGRN');
        Route::get('accountant/purchase-order/{supplierId}/download', [AccountantController::class, 'downloadPO'])->name('accountant.downloadPO');
    });

    Route::prefix('gm')->group(function () {
        Route::get('/', [GMController::class, 'index'])->name('gm.index');
        Route::get('/review/{inspectionId}', [GMController::class, 'show'])->name('gm.show');
        Route::post('/gm/decision/{inspectionId}', [GMController::class, 'reviewDecision'])->name('gm.reviewDecision');
        Route::post('/gm/work-started/multiple/{inspection}', [GMController::class, 'workStartedMultiple'])->name('gm.workStartedMultiple');
        Route::post('gm/dispatch/{inspection}/approve', [GMController::class, 'approveDispatch'])->name('gm.dispatch.approve');
        Route::post('/gm/md-decision/{inspection}', [GMController::class, 'mdDecision'])->name('gm.mdDecision');
        Route::get('/gm-reviews/inquiries', [GMController::class, 'inquiries'])->name('gm.inquiries');
        Route::post('gm/inquiry/{dispatch}', [GMController::class, 'markReceived'])->name('gm.inquiry.receive');
        Route::post('/installments/{installment}/approve', [GMController::class, 'approveInstallment'])->name('installments.approve');
    });


    Route::prefix('fleet-post-checks')->name('fleet_post_checks.')->group(function () {
        Route::get('/', [FleetPostCheckController::class, 'index'])->name('index');
        Route::get('/{inspection}', [FleetPostCheckController::class, 'show'])->name('show');
        Route::post('/{inspection}', [FleetPostCheckController::class, 'store'])->name('store');
    });

    Route::prefix('fleet-vehicle-release')->name('fleet-vehicle-release.')->group(function () {
        Route::get('/', [FleetVehicleReleaseController::class, 'index'])->name('index');
        Route::get('/{postCheck}', [FleetVehicleReleaseController::class, 'show'])->name('show');
        Route::post('/{postCheck}', [FleetVehicleReleaseController::class, 'store'])->name('store');
    });

    Route::post('/vehicles/{vehicle}/disable', [VehicleController::class, 'disable'])
    ->name('vehicles.disable');

    Route::get('/md/reviews', [MDReviewController::class, 'index'])->name('md.reviews.index');
    Route::post('/md/reviews/{review}/decision', [MDReviewController::class, 'decision'])->name('md.reviews.decision');
    Route::post('/md-reviews/store-multiple/{inspection}', [MDReviewController::class, 'storeMultiple'])->name('md.reviews.storeMultiple');
    Route::get('/vehicle-status', [VehicleStatusController::class, 'index'])->name('vehicle-status.index');
    Route::get('/vehicle-status/gm/{id}', [VehicleStatusController::class, 'showGMWorkStatus'])->name('vehicle-status.gm.show');

    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/create/{vehicle}', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
    Route::patch('/vehicle-bookings/{rental}/arrived', [RentalController::class, 'markArrived'])->name('rentals.markArrived');
    Route::delete('/rentals/{rental}', [RentalController::class, 'destroy'])->name('rentals.destroy');
    Route::post('/owner-repairs/update-status', [RentalController::class, 'updateOwnerRepairStatus'])
    ->name('owner-repairs.updateStatus');
    Route::patch('rentals/{id}/save-emergency-dates', [RentalController::class, 'saveEmerDates'])->name('rentals.saveEmerDates');
    Route::patch('rentals/{id}/complete-emergency', [RentalController::class, 'completeEmergency'])->name('rentals.completeEmergency');
    Route::patch('/rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');
    Route::get('/vehicle-bookings/{rental}/alternative-vehicles', [RentalController::class, 'getAlternativeVehicles']);

    Route::patch('/vehicle-bookings/{id}/assign-alternative', [RentalController::class, 'assignAlternativeVehicle'])->name('rentals.assignAlternativeVehicle');
    Route::patch('/vehicle-bookings/{rental}/extend-departure', [RentalController::class, 'extendDeparture'])->name('rentals.extendDeparture');

    Route::get('/rentals/{rental}/available-vehicles', [RentalController::class, 'availableVehicles']);
    Route::post('/rentals/{rental}/change-vehicle', [RentalController::class, 'changeVehicle']);
    Route::get('/rentals/{booking}/available-vehicles', [RentalController::class, 'availableVehicles']);
    Route::post('/rentals/{rental}/change-vehicle', [RentalController::class, 'changeVehicle']);
    Route::post('/rentals/{id}/cancel', [RentalController::class, 'cancel']);
    Route::post('/rentals/{id}/remove', [RentalController::class, 'remove']);
    Route::post('/rentals/{id}/delete', [RentalController::class, 'delete']);
    Route::get('/rentals/{rental}/related-rentals', [RentalController::class, 'getVehicleRentedBookings']);
    Route::get('/rentals/{rental}/related-rentals-tour', [RentalController::class, 'getVehicleTourBookings']);

    Route::post('/check-booking-number', [RentalController::class, 'checkBookingNumber'])->name('rentals.checkBookingNumber');

    Route::post('/vehicles/{vehicle}/unfreeze', [RentalController::class, 'unfreezeVehicle'])
    ->name('vehicles.unfreeze');
    Route::post('/rentals/{id}/mark-arrived', [RentalController::class, 'markOnTour'])
    ->name('rentals.markOnTour');

    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/', [CashierController::class, 'index'])->name('index');
        Route::get('/create', [CashierController::class, 'create'])->name('create');
        Route::post('/', [CashierController::class, 'store'])->name('store');
        Route::get('/{cashier}/edit', [CashierController::class, 'edit'])->name('edit');
        Route::put('/{cashier}', [CashierController::class, 'update'])->name('update');
        Route::delete('/{cashier}', [CashierController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/send-to-fm', [CashierController::class, 'sendToFM'])->name('sendToFM');
        Route::patch('payments/approve/{cashier}', [CashierController::class, 'approvePayment'])->name('payment.approve');
        Route::get('cashier/bill/{id}', [CashierController::class, 'viewBill'])->name('viewBill');
        Route::get('/cashier/{id}/download', [CashierController::class, 'downloadBill'])->name('downloadBill');
    });

    Route::prefix('payment-coordinator')->name('payment-coordinator.')->group(function () {
        Route::get('/', [PaymentCoordinatorController::class, 'index'])->name('index');
        Route::post('/{cashier}', [PaymentCoordinatorController::class, 'store'])->name('store');
    });

    Route::get('/vehicle-status/history/{vehicle}', [VehicleStatusController::class, 'vehicleHistory']);

    Route::resource('vehicle-attributes', VehicleAttributeController::class);

    Route::resource('inventory-types', InventoryTypeController::class);


    Route::get('/vehicle-bookings', [VehicleBookingController::class, 'index'])->name('vehicle.bookings');
    Route::get('/vehicles/{typeId}/booking-grid', [VehicleBookingController::class, 'bookingGrid']);

    Route::post('/vehicle-freezes', [VehicleFreezeController::class, 'store'])->name('vehicle-freezes.store');
    Route::delete('/vehicle-freezes/{id}', [VehicleFreezeController::class, 'destroy'])
    ->name('vehicle-freezes.destroy');
    Route::post('/vehicle-freeze/extend', [VehicleFreezeController::class, 'extend'])->name('vehicle-freeze.extend');


    Route::patch('/vehicle-bookings/{rental}/mark-on-tour', [VehicleBookingController::class,'markOnTour'])->name('vehicle-bookings.markOnTour');

    Route::get('/vehicle-bookings/{rental}/change-vehicles', [VehicleBookingController::class, 'changeVehicleList'])->name('vehicle.change.list');
    Route::patch('/vehicle-bookings/{rental}/change-vehicle', [VehicleBookingController::class, 'changeVehicle'])->name('vehicle.change.update');

    Route::get('/vehicles/search', [VehicleBookingController::class, 'search'])->name('vehicles.search');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportsController::class, 'create'])->name('reports.create');
    Route::get('/reports/requests', [ReportsController::class, 'requests'])->name('reports.requests');
    Route::post('/reports', [ReportsController::class, 'store'])->name('reports.store');
    Route::post('/reports/{report}/accept', [ReportsController::class, 'accept'])->name('reports.accept');

    // web.php
    Route::get('reports/{report}/{filename}',[ReportsController::class, 'view'])->name('reports.view');

    Route::resource('schedule', ScheduleController::class)->only([
        'index', 'create', 'store','edit', 'update', 'destroy','disable'
    ]);

    Route::get('/chauffers', [ChaufferController::class, 'index'])->name('chauffers.index');
    Route::post('/chauffers', [ChaufferController::class, 'store'])->name('chauffers.store');
    Route::put('/chauffers/{chauffer}', [ChaufferController::class, 'update'])->name('chauffers.update');
    Route::delete('/chauffers/{chauffer}', [ChaufferController::class, 'destroy'])->name('chauffers.destroy');

    Route::post('/transport-services', [TransportServiceController::class, 'store'])->name('transport-services.store');
    Route::put('/transport-services/{transportService}', [TransportServiceController::class, 'update'])->name('transport-services.update');
    Route::delete('/transport-services/{transportService}', [TransportServiceController::class, 'destroy'])->name('transport-services.destroy');


    Route::get('/vehicle-analytics', [VehicleAnalyticsController::class, 'index'])
        ->name('vehicle-analytics.index');

        Route::get('/vehicle-details', [VehicleDetailsController::class, 'index'])
    ->name('vehicle-details.index');

Route::post('/vehicle-details/{vehicle}', [VehicleDetailsController::class, 'update'])
    ->name('vehicle-details.update');

Route::get('/vehicle-details/export', [VehicleDetailsController::class, 'export'])
    ->name('vehicle-details.export');



Route::get('/transport-services/available-vehicles', [ScheduleController::class, 'availableVehicles'])
    ->name('transport-services.available-vehicles');
});

require __DIR__.'/auth.php';
