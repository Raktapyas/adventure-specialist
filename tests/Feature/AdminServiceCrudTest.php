<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/services')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/services')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_service(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/services', [
                'title' => 'Trekking',
                'slug' => 'trekking',
                'content' => '<p>Body</p>',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', ['slug' => 'trekking', 'parent_id' => null]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Service::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin())
            ->post('/admin/services', ['title' => 'Dup', 'slug' => 'taken'])
            ->assertSessionHasErrors('slug');
    }

    public function test_service_parent_can_be_changed_on_update(): void
    {
        $root = Service::factory()->create();
        $child = Service::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($this->admin())
            ->put("/admin/services/{$child->id}", [
                'title' => 'Renamed Child',
                'slug' => $child->slug,
                'parent_id' => null,
            ])
            ->assertRedirect(route('admin.services.edit', $child));

        $this->assertDatabaseHas('services', ['id' => $child->id, 'parent_id' => null]);
        $this->assertDatabaseHas('redirects', [
            'old_path' => $root->getPath().$child->slug.'/',
            'model_type' => 'service',
            'model_id' => $child->id,
        ]);
    }

    public function test_service_nesting_limited_to_two_levels(): void
    {
        $root = Service::factory()->create();
        $child = Service::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($this->admin())
            ->post('/admin/services', [
                'title' => 'Too Deep',
                'slug' => 'too-deep',
                'parent_id' => $child->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('services', ['slug' => 'too-deep']);
    }

    public function test_admin_can_edit_a_service(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/services/{$service->id}", ['title' => 'Updated', 'slug' => $service->slug])
            ->assertRedirect(route('admin.services.edit', $service));

        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Updated']);
    }

    public function test_service_with_children_cannot_be_deleted(): void
    {
        $root = Service::factory()->create();
        Service::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/services/{$root->id}")
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('services', ['id' => $root->id]);
    }

    public function test_admin_can_delete_a_leaf_service(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/services/{$service->id}")
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
