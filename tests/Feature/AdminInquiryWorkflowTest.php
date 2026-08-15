<?php

namespace Tests\Feature;

use App\Filament\Widgets\StatsOverview;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminInquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_viewing_an_inquiry_marks_it_as_read(): void
    {
        $inquiry = Inquiry::factory()->create(['is_read' => false]);

        $this->actingAs($this->admin())
            ->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'is_read' => true]);
    }

    public function test_admin_can_toggle_read_state(): void
    {
        $inquiry = Inquiry::factory()->create(['is_read' => false]);

        $this->actingAs($this->admin())
            ->patch("/admin/inquiries/{$inquiry->id}/toggle-read")
            ->assertRedirect();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'is_read' => true]);

        $this->actingAs($this->admin())
            ->patch("/admin/inquiries/{$inquiry->id}/toggle-read")
            ->assertRedirect();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'is_read' => false]);
    }

    public function test_admin_can_update_status(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        $this->actingAs($this->admin())
            ->patch("/admin/inquiries/{$inquiry->id}/status", ['status' => 'resolved'])
            ->assertRedirect();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'resolved']);
    }

    public function test_status_is_validated_against_the_allowed_list(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        $this->actingAs($this->admin())
            ->from("/admin/inquiries/{$inquiry->id}")
            ->patch("/admin/inquiries/{$inquiry->id}/status", ['status' => 'banana'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'new']);
    }

    public function test_index_filters_by_status(): void
    {
        Inquiry::factory()->create(['name' => 'Resolved Person', 'status' => 'resolved']);
        Inquiry::factory()->create(['name' => 'New Person', 'status' => 'new']);

        $this->actingAs($this->admin())
            ->get('/admin/inquiries?status=resolved')
            ->assertOk()
            ->assertSee('Resolved Person')
            ->assertDontSee('New Person');
    }

    public function test_index_filters_by_read_state(): void
    {
        Inquiry::factory()->create(['name' => 'Read Person', 'is_read' => true]);
        Inquiry::factory()->create(['name' => 'Unread Person', 'is_read' => false]);

        $this->actingAs($this->admin())
            ->get('/admin/inquiries?read=0')
            ->assertOk()
            ->assertSee('Unread Person')
            ->assertDontSee('Read Person');
    }

    public function test_index_searches_inquiries(): void
    {
        Inquiry::factory()->create(['name' => 'Alice Traveller', 'message' => 'Looking for a trek']);
        Inquiry::factory()->create(['name' => 'Bob', 'message' => 'Completely different']);

        $this->actingAs($this->admin())
            ->get('/admin/inquiries?search=trek')
            ->assertOk()
            ->assertSee('Alice Traveller')
            ->assertDontSee('Bob');
    }

    public function test_bulk_mark_read(): void
    {
        $first = Inquiry::factory()->create(['is_read' => false]);
        $second = Inquiry::factory()->create(['is_read' => false]);

        $this->actingAs($this->admin())
            ->post('/admin/inquiries/bulk', [
                'ids' => [$first->id, $second->id],
                'action' => 'mark_read',
            ])
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseHas('inquiries', ['id' => $first->id, 'is_read' => true]);
        $this->assertDatabaseHas('inquiries', ['id' => $second->id, 'is_read' => true]);
    }

    public function test_bulk_set_status(): void
    {
        $first = Inquiry::factory()->create(['status' => 'new']);
        $second = Inquiry::factory()->create(['status' => 'new']);

        $this->actingAs($this->admin())
            ->post('/admin/inquiries/bulk', [
                'ids' => [$first->id, $second->id],
                'action' => 'in_progress',
            ])
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseHas('inquiries', ['id' => $first->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('inquiries', ['id' => $second->id, 'status' => 'in_progress']);
    }

    public function test_bulk_delete(): void
    {
        $first = Inquiry::factory()->create();
        $second = Inquiry::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/inquiries/bulk', [
                'ids' => [$first->id, $second->id],
                'action' => 'delete',
            ])
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertDatabaseMissing('inquiries', ['id' => $first->id]);
        $this->assertDatabaseMissing('inquiries', ['id' => $second->id]);
    }

    public function test_bulk_action_is_validated(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/inquiries/bulk', [
                'ids' => [$inquiry->id],
                'action' => 'explode',
            ])
            ->assertSessionHasErrors('action');
    }

    public function test_dashboard_shows_unread_inquiry_count(): void
    {
        Inquiry::factory()->count(3)->create(['is_read' => false]);
        Inquiry::factory()->count(2)->create(['is_read' => true]);

        Livewire::test(StatsOverview::class)
            ->assertSee('Unread Inquiries')
            ->assertSee('3');
    }
}
