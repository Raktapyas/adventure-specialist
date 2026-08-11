<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/inquiries')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/inquiries')
            ->assertForbidden();
    }

    public function test_admin_can_list_inquiries(): void
    {
        Inquiry::factory()->create(['name' => 'Jane Traveller']);

        $this->actingAs($this->admin())
            ->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('Jane Traveller');
    }

    public function test_admin_can_view_an_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create(['message' => 'Please send details.']);

        $this->actingAs($this->admin())
            ->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertSee('Please send details.');
    }

    public function test_admin_can_delete_an_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/inquiries/{$inquiry->id}")
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }
}
