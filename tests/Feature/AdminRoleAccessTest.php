<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryImageResource\Pages\ListGalleryImages;
use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\InquiryResource\Pages\ListInquiries;
use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Filament\Resources\PackageResource\Pages\ListPackages;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sub_admin_cannot_access_inquiry_and_page_resources(): void
    {
        $subAdmin = User::factory()->create(['id' => 2, 'role' => 'sub-admin']);

        $this->actingAs($subAdmin);

        $this->assertFalse(InquiryResource::canAccess());
        $this->assertFalse(PageResource::canAccess());
    }

    public function test_admin_role_can_access_inquiries_but_pages_are_shield_gated(): void
    {
        $admin = User::factory()->create(['id' => 2, 'role' => 'admin']);

        $this->actingAs($admin);

        $this->assertTrue(InquiryResource::canAccess());
        $this->assertFalse(PageResource::canAccess());
    }

    public function test_legacy_admin_with_sub_admin_role_keeps_inquiry_access_but_not_pages(): void
    {
        $legacyAdmin = User::factory()->create(['id' => 2, 'is_admin' => true, 'role' => 'sub-admin']);

        $this->actingAs($legacyAdmin);

        $this->assertTrue(InquiryResource::canAccess());
        $this->assertFalse(PageResource::canAccess());
    }

    public function test_sub_admin_is_forbidden_from_restricted_resources(): void
    {
        $subAdmin = User::factory()->create(['id' => 2, 'role' => 'sub-admin']);

        $this->actingAs($subAdmin)
            ->get('/admin/inquiries')
            ->assertForbidden();

        $this->actingAs($subAdmin)
            ->get('/admin/pages')
            ->assertForbidden();
    }

    public function test_legacy_admin_can_access_inquiries_but_pages_are_shield_gated(): void
    {
        $legacyAdmin = User::factory()->create(['id' => 2, 'is_admin' => true, 'role' => 'sub-admin']);

        $this->actingAs($legacyAdmin)
            ->get('/admin/inquiries')
            ->assertSuccessful();

        $this->actingAs($legacyAdmin)
            ->get('/admin/pages')
            ->assertForbidden();
    }

    public function test_user_with_view_page_permission_can_access_pages_resource(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $permission = Permission::findOrCreate('view_page', 'web');
        $user = User::factory()->create(['id' => 2]);
        $user->assignRole($role);
        $user->givePermissionTo($permission);

        $this->actingAs($user)
            ->get('/admin/pages')
            ->assertOk();
    }

    public function test_row_actions_are_hidden_without_the_matching_shield_permission(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $user = User::factory()->create(['id' => 2, 'is_admin' => true]);
        $user->assignRole($role);

        $resources = [
            ['view_media', Media::factory()->create(), ListMedia::class, ['delete']],
            ['view_gallery::image', GalleryImage::factory()->create(), ListGalleryImages::class, ['edit']],
            ['view_package', Package::factory()->create(), ListPackages::class, ['edit']],
            ['view_page', Page::factory()->create(), ListPages::class, ['edit']],
            ['view_service', Service::factory()->create(), ListServices::class, ['edit']],
            ['view_inquiry', Inquiry::factory()->create(), ListInquiries::class, ['edit', 'toggle_read', 'update_status']],
        ];

        foreach ($resources as [$permissionName, $record, $listPage, $actions]) {
            $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));

            $component = Livewire::actingAs($user)->test($listPage);

            if ($permissionName === 'view_inquiry') {
                $component->assertTableActionVisible('view', $record);
            }

            foreach ($actions as $action) {
                $component->assertTableActionHidden($action, $record);
            }
        }
    }

    public function test_user_with_sub_admin_role_can_access_panel(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $user = User::factory()->create(['id' => 2]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_with_super_admin_role_can_access_panel(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $user = User::factory()->create(['id' => 2]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_without_roles_cannot_access_panel(): void
    {
        $user = User::factory()->create(['id' => 2]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_sub_admin_role_cannot_access_roles_resource(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $user = User::factory()->create(['id' => 2]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin/shield/roles')
            ->assertForbidden();
    }

    public function test_super_admin_role_alone_cannot_access_roles_resource(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $user = User::factory()->create(['id' => 2]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin/shield/roles')
            ->assertForbidden();
    }

    public function test_main_admin_user_can_access_roles_resource(): void
    {
        $user = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/shield/roles')
            ->assertOk();
    }

    public function test_is_admin_user_with_sub_admin_role_cannot_access_roles_resource(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $user = User::factory()->create(['id' => 2, 'is_admin' => true]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin/shield/roles')
            ->assertForbidden();
    }

    public function test_sub_admin_staff_manager_is_blocked_from_roles_resource(): void
    {
        $role = Role::findOrCreate('sub-admin', 'web');
        $user = User::factory()->create(['id' => 2, 'is_admin' => true]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin/shield/roles')
            ->assertForbidden();
    }

    public function test_main_admin_can_create_role_and_redirects_to_index(): void
    {
        $user = User::factory()->create(['id' => 1, 'is_admin' => true]);

        Livewire::actingAs($user)
            ->test(CreateRole::class)
            ->fillForm([
                'name' => 'editor',
                'guard_name' => 'web',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(RoleResource::getUrl('index'));

        $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => 'web']);
    }

    public function test_main_admin_can_edit_role_and_redirects_to_index(): void
    {
        $user = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        Livewire::actingAs($user)
            ->test(EditRole::class, ['record' => $role->getKey()])
            ->fillForm([
                'name' => 'editor-renamed',
                'guard_name' => 'web',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(RoleResource::getUrl('index'));

        $this->assertDatabaseHas('roles', ['name' => 'editor-renamed', 'guard_name' => 'web']);
    }

    public function test_non_master_is_admin_user_strictly_obeys_shield_permissions(): void
    {
        $legacyAdmin = User::factory()->create(['id' => 2, 'is_admin' => true, 'role' => 'sub-admin']);

        $this->actingAs($legacyAdmin)
            ->get('/admin/destinations')
            ->assertForbidden();
    }

    public function test_master_admin_user_gets_full_bypass_on_shield_protected_resources(): void
    {
        $user = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/destinations')
            ->assertOk();
    }

    public function test_role_resource_is_excluded_from_shield_permission_generation(): void
    {
        $resources = FilamentShield::getResources();

        $this->assertArrayNotHasKey('role', $resources);
    }

    public function test_prune_role_permissions_command_deletes_role_permissions(): void
    {
        Permission::create(['name' => 'view_role', 'guard_name' => 'web']);
        Permission::create(['name' => 'view_page', 'guard_name' => 'web']);

        $this->artisan('permissions:prune-role')->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'view_role']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_page']);
    }
}
