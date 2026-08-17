<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_admin_users_are_redirected_to_the_dashboard_after_login(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_non_admin_login_ignores_an_admin_intended_url(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => route('filament.admin.pages.dashboard')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('home', absolute: false));
    }

    public function test_non_admin_login_respects_a_public_intended_url(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => route('gallery')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('gallery', absolute: false));
    }

    public function test_authenticated_non_admin_visiting_guest_pages_goes_home_not_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect(route('home', absolute: false));
        $this->actingAs($user)->get('/register')->assertRedirect(route('home', absolute: false));
    }

    public function test_authenticated_admin_visiting_guest_pages_goes_to_the_dashboard(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $this->actingAs($admin)->get('/login')->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
