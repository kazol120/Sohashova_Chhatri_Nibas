<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'role-index',
            'role-create',
            'role-edit',
            'role-delete',

            'permission-index',
            'permission-create',
            'permission-edit',
            'permission-delete',

            'setting-index',
            'setting-create',
            'setting-edit',
            'setting-delete',

            'user-index',
            'user-create',
            'user-edit',
            'user-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
