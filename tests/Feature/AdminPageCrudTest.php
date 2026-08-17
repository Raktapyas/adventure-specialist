<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPageCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/pages')->assertRedirect('/admin/login');
        $this->get('/admin/pages/create')->assertRedirect('/admin/login');
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

        Livewire::actingAs($this->admin())
            ->test(ListPages::class)
            ->assertSee('Company History');
    }

    public function test_admin_can_create_a_page(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->fillForm([
                'title' => 'New Page',
                'slug' => 'new-page',
                'excerpt' => 'A short excerpt',
                'content' => '<p>Hello <strong>world</strong></p>',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(PageResource::getUrl('index'));

        $this->assertDatabaseHas('pages', [
            'slug' => 'new-page',
            'content' => '<p>Hello <strong>world</strong></p>',
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Page::factory()->create(['slug' => 'taken']);

        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->fillForm(['title' => 'Dup', 'slug' => 'taken'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        $this->assertDatabaseCount('pages', 1);
    }

    public function test_slug_auto_generates_from_title(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->set('data.title', 'About Our Company')
            ->assertFormSet(['slug' => 'about-our-company']);
    }

    public function test_manual_slug_is_preserved_when_title_changes(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->set('data.title', 'About Us')
            ->set('data.slug', 'about-us-2026')
            ->set('data.title', 'About Our Company')
            ->assertFormSet(['slug' => 'about-us-2026']);
    }

    public function test_admin_can_edit_a_page(): void
    {
        $page = Page::factory()->create(['title' => 'Old Title']);

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'New Title', 'slug' => $page->slug])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'New Title']);
    }

    public function test_page_slug_can_be_changed_on_update(): void
    {
        $page = Page::factory()->create(['slug' => 'original']);

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'Renamed', 'slug' => 'renamed'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(PageResource::getUrl('index'));

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

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => $page->title, 'parent_id' => $page->id])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);
    }

    public function test_page_can_be_created_directly_under_a_top_level_page(): void
    {
        $top = Page::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->fillForm([
                'title' => 'Child Page',
                'slug' => 'child-page',
                'parent_id' => $top->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'child-page', 'parent_id' => $top->id]);
    }

    public function test_page_cannot_be_created_under_a_child_page(): void
    {
        $top = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $top->id]);

        Livewire::actingAs($this->admin())
            ->test(CreatePage::class)
            ->fillForm([
                'title' => 'Grandchild',
                'slug' => 'grandchild',
                'parent_id' => $child->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseMissing('pages', ['slug' => 'grandchild']);
    }

    public function test_page_cannot_be_moved_under_a_child_page(): void
    {
        $top = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $top->id]);
        $page = Page::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm([
                'title' => $page->title,
                'slug' => $page->slug,
                'parent_id' => $child->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'parent_id' => null]);
    }

    public function test_page_cannot_be_moved_under_its_own_descendant(): void
    {
        $parent = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $parent->getKey()])
            ->fillForm(['title' => $parent->title, 'parent_id' => $child->id])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);
    }

    public function test_page_with_children_cannot_be_deleted(): void
    {
        $parent = Page::factory()->create();
        Page::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $parent->getKey()])
            ->callAction('delete');

        $this->assertDatabaseHas('pages', ['id' => $parent->id]);
    }

    public function test_admin_can_delete_a_leaf_page(): void
    {
        $page = Page::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
