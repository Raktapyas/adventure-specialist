<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/pages')->assertRedirect('/login');
        $this->get('/admin/pages/create')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/pages')->assertForbidden();
        $this->actingAs($user)->get('/admin/pages/create')->assertForbidden();
    }

    public function test_admin_can_list_pages(): void
    {
        Page::factory()->create(['title' => 'Company History']);

        $this->actingAs($this->admin())
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('Company History');
    }

    public function test_admin_can_create_a_page(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pages', [
                'title' => 'New Page',
                'slug' => 'new-page',
                'excerpt' => 'A short excerpt',
                'content' => '<p>Hello <strong>world</strong></p>',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', [
            'slug' => 'new-page',
            'content' => '<p>Hello <strong>world</strong></p>',
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Page::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin())
            ->post('/admin/pages', ['title' => 'Dup', 'slug' => 'taken'])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('pages', 1);
    }

    public function test_admin_can_edit_a_page(): void
    {
        $page = Page::factory()->create(['title' => 'Old Title']);

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", ['title' => 'New Title', 'slug' => $page->slug])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'New Title']);
    }

    public function test_page_slug_can_be_changed_on_update(): void
    {
        $page = Page::factory()->create(['slug' => 'original']);

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", [
                'title' => 'Renamed',
                'slug' => 'renamed',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'renamed']);
        $this->assertDatabaseHas('redirects', [
            'old_path' => '/about-us/original/',
            'model_type' => 'page',
            'model_id' => $page->id,
        ]);
    }

    public function test_page_cannot_be_its_own_parent(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", [
                'title' => $page->title,
                'parent_id' => $page->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_page_cannot_be_moved_under_its_own_descendant(): void
    {
        $parent = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$parent->id}", [
                'title' => $parent->title,
                'parent_id' => $child->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_page_with_children_cannot_be_deleted(): void
    {
        $parent = Page::factory()->create();
        Page::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/pages/{$parent->id}")
            ->assertRedirect(route('admin.pages.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pages', ['id' => $parent->id]);
    }

    public function test_admin_can_delete_a_leaf_page(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/pages/{$page->id}")
            ->assertRedirect(route('admin.pages.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
