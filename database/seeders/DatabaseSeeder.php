<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LookupSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // Users to create with their roles
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'password' => '123456789', 'role' => 'admin'],
            ['name' => 'Procurement Officer', 'email' => 'procurement@example.com', 'password' => '123456789', 'role' => 'procurement-officer'],
            ['name' => 'Fleet Officer', 'email' => 'fleet_officer@example.com', 'password' => '123456789', 'role' => 'fleet-officer'],
            ['name' => 'Fleet Manager', 'email' => 'fleet_manager@example.com', 'password' => '123456789', 'role' => 'fleet-manager'],
            ['name' => 'Garage Team', 'email' => 'garage_team@example.com', 'password' => '123456789', 'role' => 'garage-team'],
            ['name' => 'Accountant', 'email' => 'accountant@example.com', 'password' => '123456789', 'role' => 'accountant'],
            ['name' => 'General Manager', 'email' => 'gm@example.com', 'password' => '123456789', 'role' => 'general-manager'],
            ['name' => 'Managing Director', 'email' => 'md@example.com', 'password' => '123456789', 'role' => 'managing-director'],
            ['name' => 'Payment Cordinator', 'email' => 'payment_cordinator@example.com', 'password' => '123456789', 'role' => 'payment_cordinator'],
            ['name' => 'Rent a Car Team', 'email' => 'rent_team@example.com', 'password' => '123456789', 'role' => 'rent_a_car'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make($data['password'])]
            );

            if ($data['role']) {
                $role = Role::where('name', $data['role'])->first();
                $role && $user->assignRole($role);
            }
        }
    }
}
