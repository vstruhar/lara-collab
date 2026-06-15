<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $insertPermissions = fn ($role) => collect(PermissionService::$permissionsByRole[$role])
            ->flatten()
            ->map(function ($name) {
                $permission = DB::table('permissions')->where('name', $name)->first();

                return $permission
                    ? $permission->id
                    : DB::table('permissions')
                        ->insertGetId([
                            'name' => $name,
                            'guard_name' => 'web',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
            })
            ->toArray();

        $permissionIdsByRole = [
            'admin' => $insertPermissions('admin'),
            'manager' => $insertPermissions('manager'),
            'developer' => $insertPermissions('developer'),
            'designer' => $insertPermissions('designer'),
            'client' => $insertPermissions('client'),
        ];

        foreach ($permissionIdsByRole as $role => $permissionIds) {
            $role = Role::whereName($role)->first();

            collect($permissionIds)->each(function ($id) use ($role) {
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $id)
                    ->exists();

                if (! $exists) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $id,
                    ]);
                }
            });
        }

        Artisan::call('cache:clear');
    }
}
