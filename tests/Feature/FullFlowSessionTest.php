<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class FullFlowSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_session_survives_filament_user_creation_over_http(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true, 'password' => 'admin-password']);

        // Log in via the real Breeze login route.
        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'admin-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($admin);

        // Create a user through the Filament resource (Livewire request).
        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'new-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Auth::check(), 'Admin should still be authenticated.');
        $this->assertSame($admin->getKey(), Auth::id(), 'Session user_id must remain the admin.');
    }

    public function test_public_registration_does_not_steal_an_admin_session(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        // An authenticated admin hitting the guest-only register route is redirected away.
        $this->actingAs($admin)
            ->get('/register')
            ->assertRedirect();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_public_registration_does_not_authenticate_the_new_user(): void
    {
        // Creating a user must never authenticate that user — they log in manually.
        $response = $this->post('/register', [
            'name' => 'Brand New',
            'email' => 'brand-new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'brand-new@example.com']);
    }
}
