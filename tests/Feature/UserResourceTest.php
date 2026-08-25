<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_admin_can_create_and_edit_users_without_overwriting_a_blank_password(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $user = User::factory()->create(['password' => 'existing-password']);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('email')
            ->assertTableColumnExists('role');

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'new-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(UserResource::getUrl('index'));

        $createdUser = User::query()->where('email', 'new-user@example.com')->firstOrFail();

        $this->assertSame('New User', $createdUser->name);
        $this->assertTrue(Hash::check('new-password', $createdUser->password));

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => 'Renamed User',
                'email' => $user->email,
                'password' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(UserResource::getUrl('index'));

        $this->assertTrue(Hash::check('existing-password', $user->refresh()->password));
    }

    public function test_master_admin_can_assign_a_panel_role_when_creating_a_user(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $role = Role::findOrCreate('sub-admin', 'web');

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Staff Manager',
                'email' => 'staff-manager@example.com',
                'password' => 'secret123',
                'roles' => $role->getKey(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(UserResource::getUrl('index'));

        $createdUser = User::query()->where('email', 'staff-manager@example.com')->firstOrFail();

        $this->assertTrue($createdUser->hasRole('sub-admin'));
        $this->assertTrue($createdUser->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_master_admin_can_change_a_users_panel_role_on_edit(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $user = User::factory()->create();
        $subAdmin = Role::findOrCreate('sub-admin', 'web');
        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $user->assignRole($subAdmin);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $superAdmin->getKey(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->refresh()->hasRole('super_admin'));
        $this->assertFalse($user->hasRole('sub-admin'));
    }

    public function test_user_created_without_a_role_cannot_access_the_panel(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'No Panel',
                'email' => 'no-panel@example.com',
                'password' => 'secret123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdUser = User::query()->where('email', 'no-panel@example.com')->firstOrFail();

        $this->assertFalse($createdUser->canAccessPanel(filament()->getPanel('admin')));
    }
}
