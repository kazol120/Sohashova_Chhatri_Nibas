<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ModelHasRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all(); // Fetch all users
        $superAdminRole = Role::where('name', 'super-admin')->first(); // Fetch the super-admin role
        $adminRole = Role::where('name', 'admin')->first(); // Fetch the admin role

        foreach ($users as $index => $user) {
            if ($index == 0) {
                // Assign the super-admin role to the first user
                $user->assignRole($superAdminRole);
            } elseif ($index == 1) {
                // Assign the admin role to the second user
                $user->assignRole($adminRole);
            }
        }
    }

}
