<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * The exact permission set for the sub-admin (staff manager) role:
     * - Pages / Services / Destinations: view only,
     * - Packages: view + create (staff add special packages, admin curates),
     * - Media / Gallery: view + upload + delete,
     * - Inquiries: view + update (process leads; creation is the public form's
     *   job, deletion stays with super admin).
     * Nothing from Administration (users / roles / settings / hero slides).
     *
     * @return array<int, string>
     */
    public static function contentPermissions(): array
    {
        return [
            'view_page',
            'view_service',
            'view_destination',
            'view_package', 'create_package',
            'view_gallery::image', 'create_gallery::image', 'delete_gallery::image',
            'view_media', 'create_media', 'delete_media',
            'view_inquiry', 'update_inquiry',
        ];
    }

    /**
     * Idempotent role/permission bootstrap:
     * - purges roles & permissions created with invalid guards (they can never
     *   match web-guard users),
     * - seeds the content permission set and the sub-admin role,
     * - keeps super_admin holding every web-guard permission,
     * - mirrors the legacy role column onto real Spatie roles for users created
     *   before the panel-role selector existed.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Mass-delete invalid-guard rows: model deletes would resolve the
        // users() relation against a guard that does not exist in
        // config/auth.php and crash. Clean their pivots explicitly.
        $brokenRoleIds = Role::where('guard_name', '!=', 'web')->pluck('id');
        $brokenPermissionIds = Permission::where('guard_name', '!=', 'web')->pluck('id');

        DB::table('role_has_permissions')->whereIn('role_id', $brokenRoleIds)->delete();
        DB::table('model_has_roles')->whereIn('role_id', $brokenRoleIds)->delete();
        Role::where('guard_name', '!=', 'web')->delete();

        DB::table('role_has_permissions')->whereIn('permission_id', $brokenPermissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $brokenPermissionIds)->delete();
        Permission::where('guard_name', '!=', 'web')->delete();

        $permissions = collect(self::contentPermissions())
            ->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $subAdmin = Role::firstOrCreate(['name' => 'sub-admin', 'guard_name' => 'web']);
        $subAdmin->syncPermissions($permissions);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        User::query()->where('role', 'sub-admin')->get()
            ->each(fn (User $user) => $user->assignRole('sub-admin'));
    }
}
