<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/services')->assertRedirect('/admin/login');
        $this->get('/admin/services/create')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/services')->assertForbidden();
        $this->actingAs($user)->get('/admin/services/create')->assertForbidden();
    }

    public function test_admin_can_create_a_service(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->fillForm([
                'title' => 'Trekking',
                'slug' => 'trekking',
                'content' => '<p>Body</p>',
                'sort_order' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(ServiceResource::getUrl('index'));

        $this->assertDatabaseHas('services', ['slug' => 'trekking', 'parent_id' => null]);
    }

    public function test_service_icon_can_be_saved(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->fillForm([
                'title' => 'Rafting',
                'slug' => 'rafting',
                'icon' => 'heroicon-o-bolt',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(ServiceResource::getUrl('index'));

        $this->assertDatabaseHas('services', ['slug' => 'rafting', 'icon' => 'heroicon-o-bolt']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Service::factory()->create(['slug' => 'taken']);

        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->fillForm(['title' => 'Dup', 'slug' => 'taken'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        $this->assertDatabaseCount('services', 1);
    }

    public function test_slug_auto_generates_from_title(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->set('data.title', 'Mountain Biking')
            ->assertFormSet(['slug' => 'mountain-biking']);
    }

    public function test_manual_slug_is_preserved_when_title_changes(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->set('data.title', 'Mountain Biking')
            ->set('data.slug', 'mountain-biking-2026')
            ->set('data.title', 'Trail Riding')
            ->assertFormSet(['slug' => 'mountain-biking-2026']);
    }

    public function test_service_parent_can_be_changed_on_update(): void
    {
        $root = Service::factory()->create();
        $child = Service::factory()->create(['parent_id' => $root->id]);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $child->getKey()])
            ->fillForm([
                'title' => 'Renamed Child',
                'slug' => $child->slug,
                'parent_id' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ServiceResource::getUrl('index'));

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

        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->fillForm([
                'title' => 'Too Deep',
                'slug' => 'too-deep',
                'parent_id' => $child->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseMissing('services', ['slug' => 'too-deep']);
    }

    public function test_admin_can_edit_a_service(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->fillForm(['title' => 'Updated', 'slug' => $service->slug])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Updated']);
    }

    public function test_service_with_children_cannot_be_deleted(): void
    {
        $root = Service::factory()->create();
        Service::factory()->create(['parent_id' => $root->id]);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $root->getKey()])
            ->callAction('delete');

        $this->assertDatabaseHas('services', ['id' => $root->id]);
    }

    public function test_admin_can_delete_a_leaf_service(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
