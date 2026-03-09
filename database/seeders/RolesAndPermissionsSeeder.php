<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            ['name'=>'manage_users'],
            ['name'=>'manage_roles'],
            ['name'=>'manage_permissions'],
            ['name'=>'manage_vehicles'],
            ['name'=>'manage_inspection-reports'],
            ['name'=>'manage_garage-reports'],
            ['name'=>'manage_procurements'],
            ['name'=>'manage_fleet-manager'],
            ['name'=>'manage_accountant'],
            ['name'=>'manage_general-manager'],
            ['name'=>'manage_managing_director'],
            ['name'=>'manage_payment_cordinator'],
            ['name'=>'manage_dashboard'],
            ['name'=>'manage_vehicle_list'],
            ['name'=>'manage_dashboard'],
            ['name'=>'manage_rent_a_car'],
            ['name'=>'manage_vehicle_details'],
            ['name'=>'view_reports'], 
            ['name' => 'view_transits'],
        ];

        foreach($perms as $p) {
            Permission::firstOrCreate(['name'=>$p['name']], $p);
        }

        // Roles
        $admin = Role::firstOrCreate(['name'=>'admin']);
        $procurement = Role::firstOrCreate(['name'=>'procurement-officer']);
        $fleetOfficer = Role::firstOrCreate(['name'=>'fleet-officer']);
        $fleetManager = Role::firstOrCreate(['name'=>'fleet-manager']);
        $garageTeam = Role::firstOrCreate(['name'=>'garage-team']);
        $accountant = Role::firstOrCreate(['name'=>'accountant']);
        $generalManager = Role::firstOrCreate(['name'=>'general-manager']);
        $managingDirector = Role::firstOrCreate(['name'=>'managing-director']);
        $paymentCordiantor = Role::firstOrCreate(['name'=>'payment_cordinator']);
        $rentACar = Role::firstOrCreate(['name'=>'rent_a_car']);

        // Admin gets all permissions
        $admin->syncPermissions(Permission::all());

        $rentAndFinance = Role::firstOrCreate(['name' => 'rent-and-finance']);

        $rentAndFinance->syncPermissions([
            'manage_accountant',
        ]);

        $reportsViewer = Role::firstOrCreate(['name' => 'reports-viewer']);

        $reportsViewer->syncPermissions([
            'view_reports'
        ]);

        $transitViewer = Role::firstOrCreate(['name' => 'transit-viewer']);

        $transitViewer->syncPermissions([
            'view_transits'
        ]);

        // Other roles get specific permissions
        $procurement->syncPermissions(['manage_procurements']);
        $fleetOfficer->syncPermissions(['manage_vehicles', 'manage_inspection-reports','manage_vehicle_list','manage_dashboard']);
        $garageTeam->syncPermissions(['manage_garage-reports']);
        $fleetManager->syncPermissions(['manage_fleet-manager','manage_dashboard']);
        $accountant->syncPermissions(['manage_accountant']);
        $generalManager->syncPermissions(['manage_general-manager','manage_dashboard','manage_vehicle_list','manage_dashboard','manage_vehicle_details','view_reports']);
        $managingDirector->syncPermissions(['manage_managing_director','manage_dashboard','manage_vehicle_list','manage_dashboard','manage_vehicle_details','view_reports']);
        $paymentCordiantor->syncPermissions(['manage_payment_cordinator']);
        $rentACar->syncPermissions(['manage_rent_a_car']);

        // Assign admin role to first user if exists
        $user = User::where('email', 'admin@example.com')->first();
        if ($user) {
            $user->assignRole($admin);
        }
    }
}
