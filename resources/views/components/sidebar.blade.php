<!-- Sidebar component -->
<div class="bg-white shadow-lg h-100">
    <div class="d-flex flex-column h-100">
        <!-- Sidebar header -->
        <div class="d-flex align-items-center justify-content-center bg-gray-800 text-white p-3" style="background-color: #820000">
            <h2 class="h5 mb-0 fw-bold">FLEET MANAGEMENT</h2>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow-1 py-4 overflow-auto">
            <div class="px-3">

                <!-- Vehicles Section -->
                <h3 class="text-uppercase fs-6 text-muted mb-3 fw-bold">Vehicles</h3>
                @php
                    $isVehicleBookings = request()->routeIs('vehicle.bookings'); 
                    $disabledTooltip = 'Disabled while viewing vehicle bookings';

                    $sidebarLinks = [
                        ['name' => 'Dashboard', 'icon' => 'dashboard.svg', 'route' => 'dashboard', 'permission' => null],
                        ['name' => 'Reports', 'icon' => 'reports.svg', 'route' => 'reports.index', 'permission' => 'view_reports'],                        
                        ['name' => 'Vehicle Details', 'icon' => 'details.svg', 'route' => 'vehicle-details.index', 'permission' => 'manage_vehicle_details'],
                        ['name' => 'Vehicle Analytics', 'icon' => 'analytics.svg', 'route' => 'vehicle-analytics.index', 'permission' => 'manage_accountant'],
                        ['name' => 'Transfers', 'icon' => 'transit.svg', 'route' => 'schedule.index', 'permission' => 'view_transits'],                        
                        ['name' => 'Vehicle List', 'icon' => 'vehicle-status.svg', 'route' => 'vehicle-status.index', 'permission' => 'manage_vehicle_list'],
                        ['name' => 'Add Vehicles', 'icon' => 'vehicles.svg', 'route' => 'vehicles.index', 'permission' => 'manage_vehicles'],
                        ['name' => 'Vehicle Utilization', 'icon' => 'calender.svg', 'route' => 'vehicle.bookings', 'permission' => null],
                        ['name' => 'Payment Coordinator', 'icon' => 'payment-cordinator.svg', 'route' => 'cashier.index', 'permission' => 'manage_payment_cordinator'],
                        ['name' => 'Vehicle Inspection', 'icon' => 'inspection-report.svg', 'route' => 'inspections.index', 'permission' => 'manage_inspection-reports'],
                        ['name' => 'Garage Inspection Report', 'icon' => 'garage-report.svg', 'route' => 'garage_reports.index', 'permission' => 'manage_garage-reports'],
                        ['name' => 'Fleet Manager Inspection', 'icon' => 'fleet-manager.svg', 'route' => 'fleet-decisions.index', 'permission' => 'manage_fleet-manager'],
                        ['name' => 'Inventories', 'icon' => 'inventory.svg', 'route' => 'inventories.index', 'permission' => 'manage_procurements'],
                        ['name' => 'Procurement', 'icon' => 'procurement.svg', 'route' => 'procurement.index', 'permission' => 'manage_procurements'],
                        ['name' => 'Accountant', 'icon' => 'accounting.svg', 'route' => 'accountant.index', 'permission' => 'manage_accountant'],
                        ['name' => 'General Manager', 'icon' => 'general-manager.svg', 'route' => 'gm.index', 'permission' => 'manage_general-manager'],
                        ['name' => 'Post Repair Check', 'icon' => 'post-check.svg', 'route' => 'fleet_post_checks.index', 'permission' => 'manage_inspection-reports'],
                        ['name' => 'Managing Director', 'icon' => 'boss.svg', 'route' => 'md.reviews.index', 'permission' => 'manage_managing_director'],
                        ['name' => 'Suppliers', 'icon' => 'supplier.svg', 'route' => 'suppliers.index', 'permission' => 'manage_procurements'],
                        ['name' => 'Inventory Type', 'icon' => 'type.svg', 'route' => 'inventory-types.index', 'permission' => 'manage_procurements'],
                        ['name' => 'Issue Categories', 'icon' => 'issues.svg', 'route' => 'defect_categories.index', 'permission' => 'manage_inspection-reports'],
                        ['name' => 'Fleet Faults', 'icon' => 'cancel.svg', 'route' => 'faults.index', 'permission' => 'manage_inspection-reports'],
                        ['name' => 'Garage Faults', 'icon' => 'car-repair.svg', 'route' => 'issues.index', 'permission' => 'manage_inspection-reports'],
                        ['name' => 'Vehicle Attributes', 'icon' => 'attributes.svg', 'route' => 'vehicle-attributes.index', 'permission' => 'manage_vehicles'],
                        ['name' => 'Users', 'icon' => 'users.svg', 'route' => 'users.index', 'permission' => 'manage_users'],
                        ['name' => 'Permissions', 'icon' => 'permissions.svg', 'route' => 'permissions.index', 'permission' => 'manage_permissions'],
                    ];

                @endphp

                @foreach($sidebarLinks as $link)
                    @php
                        $noPermission = $link['permission'] && !auth()->user()->hasPermission($link['permission']);
                        
                        $isDisabled = $noPermission;

                        $tooltip = $isDisabled 
                            ? ($noPermission ? 'You do not have permission to access this' : $disabledTooltip) 
                            : '';
                    @endphp

                    <a href="{{ $isDisabled ? 'javascript:void(0)' : route($link['route']) }}"
                    class="text-decoration-none flex items-center px-3 py-2 text-sm font-medium rounded-md
                            {{ $isDisabled 
                                    ? 'text-gray-400 cursor-not-allowed opacity-50' 
                                    : (request()->routeIs($link['route']) 
                                        ? 'bg-gray-100 text-gray-900' 
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900') }}"
                    title="{{ $tooltip }}">
                        <img src="{{ asset('assets/'.$link['icon']) }}" class="mr-3 h-8 w-8" alt="{{ $link['name'] }} Icon">
                        <span>{{ $link['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        <!-- User profile -->
        <div class="flex items-center px-4 py-3 border-t border-gray-200">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-700 mb-0">{{ Auth::user()->name }}</p>
                <a href="{{ $isDisabled ? 'javascript:void(0)' : route('profile.edit') }}" class="text-decoration-none text-xs font-medium text-gray-500 hover:text-gray-700 {{ $isDisabled 
                                ? 'text-gray-400 cursor-not-allowed opacity-50' 
                                : (request()->routeIs('profile.index') 
                                    ? 'bg-gray-100 text-gray-900' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900') }}" title="{{ $disabledTooltip }}"> View Profile</a>
            </div>
        </div>
    </div>
</div>
