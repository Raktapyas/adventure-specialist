<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSessionReproTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stays_logged_in_after_creating_a_user(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'new-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Auth::check(), 'Admin should still be authenticated after creating a user.');
        $this->assertSame($admin->getKey(), Auth::id(), 'Session should still belong to the admin.');
    }

    public function test_admin_stays_logged_in_after_editing_another_user_without_touching_password(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $user = User::factory()->create(['password' => 'existing-password']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => 'Renamed User',
                'email' => $user->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Auth::check(), 'Admin should still be authenticated after editing a user.');
        $this->assertSame($admin->getKey(), Auth::id(), 'Session should still belong to the admin.');
        $this->assertTrue(
            Hash::check('existing-password', $user->refresh()->password),
            'Edited user password should remain usable.'
        );
    }

    public function test_admin_stays_logged_in_after_editing_another_users_password(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $user = User::factory()->create(['password' => 'existing-password']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'brand-new-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Auth::check(), 'Admin should still be authenticated after changing another user password.');
        $this->assertSame($admin->getKey(), Auth::id(), 'Session should still belong to the admin.');
        $this->assertTrue(
            Hash::check('brand-new-password', $user->refresh()->password),
            'Edited user should be able to log in with the new password.'
        );
    }
}
