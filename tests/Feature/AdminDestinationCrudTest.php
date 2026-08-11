<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDestinationCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/destinations')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/destinations')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_destination(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/destinations', [
                'title' => 'Tibet',
                'slug' => 'tibet',
                'content' => '<p>Body</p>',
            ])
            ->assertRedirect(route('admin.destinations.index'));

        $this->assertDatabaseHas('destinations', ['slug' => 'tibet', 'parent_id' => null]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Destination::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin())
            ->post('/admin/destinations', ['title' => 'Dup', 'slug' => 'taken'])
            ->assertSessionHasErrors('slug');
    }

    public function test_destination_nesting_limited_to_three_levels(): void
    {
        $l1 = Destination::factory()->create();
        $l2 = Destination::factory()->create(['parent_id' => $l1->id]);
        $l3 = Destination::factory()->create(['parent_id' => $l2->id]);

        $this->actingAs($this->admin())
            ->post('/admin/destinations', [
                'title' => 'Too Deep',
                'slug' => 'too-deep',
                'parent_id' => $l3->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('destinations', ['slug' => 'too-deep']);
    }

    public function test_destination_parent_cannot_be_changed_on_update(): void
    {
        $root = Destination::factory()->create();
        $child = Destination::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($this->admin())
            ->put("/admin/destinations/{$child->id}", [
                'title' => 'Renamed Child',
                'parent_id' => null,
            ]);

        $this->assertDatabaseHas('destinations', ['id' => $child->id, 'parent_id' => $root->id]);
    }

    public function test_admin_can_delete_a_leaf_destination(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/destinations/{$destination->id}")
            ->assertRedirect(route('admin.destinations.index'));

        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }
}
