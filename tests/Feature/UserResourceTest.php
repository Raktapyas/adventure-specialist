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
}
