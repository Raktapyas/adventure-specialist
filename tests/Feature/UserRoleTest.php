<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSubAdmin());
    }

    public function test_is_sub_admin_returns_true_for_sub_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'sub-admin']);

        $this->assertTrue($user->isSubAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_users_default_to_sub_admin_role(): void
    {
        $user = User::factory()->create()->refresh();

        $this->assertSame('sub-admin', $user->role);
        $this->assertTrue($user->isSubAdmin());
        $this->assertFalse($user->isAdmin());
    }
}
