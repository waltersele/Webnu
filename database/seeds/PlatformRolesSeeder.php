<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PlatformRolesSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'platform.access', 'guard_name' => 'web']);
        $salesPermission = Permission::firstOrCreate(['name' => 'sales.access', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $salesRole = Role::firstOrCreate(['name' => 'sales-rep', 'guard_name' => 'web']);
        $salesRole->givePermissionTo($salesPermission);

        foreach (config('platform.super_admin_emails', []) as $email) {
            $user = User::where('email', $email)->first();
            if ($user && ! $user->hasRole('super-admin')) {
                $user->assignRole('super-admin');
            }
        }
    }
}
