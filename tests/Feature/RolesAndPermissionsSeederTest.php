<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_purges_roles_and_permissions_with_invalid_guards(): void
    {
        $broken = Role::create(['name' => 'Namuna', 'guard_name' => 'Staff Manager']);
        Permission::create(['name' => 'view_page', 'guard_name' => 'Staff Manager']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseMissing('roles', ['id' => $broken->id]);
        $this->assertSame(0, Permission::where('guard_name', '!=', 'web')->count());
    }

    public function test_sub_admin_role_gets_exactly_the_content_permission_set(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = Role::findByName('sub-admin', 'web');

        $this->assertSame(
            collect(RolesAndPermissionsSeeder::contentPermissions())->sort()->values()->all(),
            $role->permissions->pluck('name')->sort()->values()->all()
        );
    }

    public function test_sub_admin_permission_matrix_matches_the_staff_manager_spec(): void
    {
        // Create the user first: the seeder mirrors the legacy role column
        // onto the Spatie role at seed time.
        $staff = User::factory()->create(['id' => 2, 'role' => 'sub-admin']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $staff = $staff->fresh();

        // View-only sections: pages, services, destinations.
        foreach (['page', 'service', 'destination'] as $resource) {
            $this->assertTrue($staff->can("view_{$resource}"), "view_{$resource}");
            $this->assertFalse($staff->can("create_{$resource}"), "create_{$resource}");
            $this->assertFalse($staff->can("update_{$resource}"), "update_{$resource}");
            $this->assertFalse($staff->can("delete_{$resource}"), "delete_{$resource}");
        }

        // Packages: staff add special packages, but never touch existing ones.
        $this->assertTrue($staff->can('view_package'));
        $this->assertTrue($staff->can('create_package'));
        $this->assertFalse($staff->can('update_package'));
        $this->assertFalse($staff->can('delete_package'));

        // Media & gallery: upload and delete, no editing of rows.
        foreach (['media', 'gallery::image'] as $resource) {
            $this->assertTrue($staff->can("view_{$resource}"), "view_{$resource}");
            $this->assertTrue($staff->can("create_{$resource}"), "create_{$resource}");
            $this->assertTrue($staff->can("delete_{$resource}"), "delete_{$resource}");
            $this->assertFalse($staff->can("update_{$resource}"), "update_{$resource}");
        }

        // Inquiries: process leads only.
        $this->assertTrue($staff->can('view_inquiry'));
        $this->assertTrue($staff->can('update_inquiry'));
        $this->assertFalse($staff->can('create_inquiry'));
        $this->assertFalse($staff->can('delete_inquiry'));

        // Administration stays locked.
        foreach (['view_user', 'create_user', 'update_user', 'view_role', 'update_role'] as $denied) {
            $this->assertFalse($staff->can($denied), $denied);
        }
    }

    public function test_super_admin_role_holds_every_web_guard_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = Role::findByName('super_admin', 'web');

        $this->assertSame(
            Permission::where('guard_name', 'web')->count(),
            $superAdmin->permissions->count()
        );
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(1, Role::where('name', 'sub-admin')->count());
        $this->assertSame(1, Role::where('name', 'super_admin')->count());
        $this->assertSame(count(RolesAndPermissionsSeeder::contentPermissions()), Permission::where('guard_name', 'web')->count());
    }

    public function test_legacy_sub_admin_column_users_gain_panel_access_and_content_only_reach(): void
    {
        $staff = User::factory()->create(['id' => 2, 'role' => 'sub-admin']);

        $this->assertFalse($staff->canAccessPanel(filament()->getPanel('admin')));

        $this->seed(RolesAndPermissionsSeeder::class);

        // fresh() re-reads relations; refresh() can leave Spatie's cached
        // roles/permissions relations stale within the same request.
        $staff = $staff->fresh();

        $this->assertTrue($staff->hasRole('sub-admin'));
        $this->assertTrue($staff->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($staff);

        $this->assertTrue(PageResource::canAccess());
        $this->assertFalse(UserResource::canAccess());
    }
}
