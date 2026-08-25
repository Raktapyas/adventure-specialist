<?php

namespace Tests\Feature;

use App\Filament\Resources\DestinationResource;
use App\Filament\Resources\DestinationResource\Pages\CreateDestination;
use App\Filament\Resources\DestinationResource\Pages\EditDestination;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDestinationCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/destinations')->assertRedirect('/admin/login');
        $this->get('/admin/destinations/create')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/destinations')->assertForbidden();
        $this->actingAs($user)->get('/admin/destinations/create')->assertForbidden();
    }

    public function test_admin_can_create_a_destination(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->fillForm([
                'title' => 'Tibet',
                'slug' => 'tibet',
                'content' => '<p>Body</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(DestinationResource::getUrl('index'));

        $this->assertDatabaseHas('destinations', ['slug' => 'tibet', 'parent_id' => null]);
    }

    public function test_trip_tab_fields_persist_on_create(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->fillForm([
                'title' => 'Gokyo Trek',
                'slug' => 'gokyo-trek',
                'content' => '<p>Overview</p>',
                'itinerary' => '<p>Day 1</p>',
                'includes' => '<p>Meals</p>',
                'excludes' => '<p>Flights</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('destinations', [
            'slug' => 'gokyo-trek',
            'itinerary' => '<p>Day 1</p>',
            'includes' => '<p>Meals</p>',
            'excludes' => '<p>Flights</p>',
        ]);
    }

    public function test_trip_tab_fields_persist_on_update(): void
    {
        $destination = Destination::factory()->create([
            'itinerary' => '<p>Day 1</p>',
            'includes' => '<p>Meals</p>',
            'excludes' => '<p>Flights</p>',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $destination->getKey()])
            ->fillForm([
                'itinerary' => '<p>Day 1: Lukla</p>',
                'includes' => '<p>Permits</p>',
                'excludes' => '<p>Insurance</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'itinerary' => '<p>Day 1: Lukla</p>',
            'includes' => '<p>Permits</p>',
            'excludes' => '<p>Insurance</p>',
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Destination::factory()->create(['slug' => 'taken']);

        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->fillForm(['title' => 'Dup', 'slug' => 'taken'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        $this->assertDatabaseCount('destinations', 1);
    }

    public function test_slug_auto_generates_from_title(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->set('data.title', 'Annapurna Circuit')
            ->assertFormSet(['slug' => 'annapurna-circuit']);
    }

    public function test_manual_slug_is_preserved_when_title_changes(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->set('data.title', 'Annapurna Circuit')
            ->set('data.slug', 'annapurna-circuit-2026')
            ->set('data.title', 'Annapurna Base Camp')
            ->assertFormSet(['slug' => 'annapurna-circuit-2026']);
    }

    public function test_destination_nesting_limited_to_three_levels(): void
    {
        $l1 = Destination::factory()->create();
        $l2 = Destination::factory()->create(['parent_id' => $l1->id]);
        $l3 = Destination::factory()->create(['parent_id' => $l2->id]);

        Livewire::actingAs($this->admin())
            ->test(CreateDestination::class)
            ->fillForm([
                'title' => 'Too Deep',
                'slug' => 'too-deep',
                'parent_id' => $l3->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseMissing('destinations', ['slug' => 'too-deep']);
    }

    public function test_destination_parent_can_be_changed_on_update(): void
    {
        $root = Destination::factory()->create();
        $child = Destination::factory()->create(['parent_id' => $root->id]);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $child->getKey()])
            ->fillForm([
                'title' => 'Renamed Child',
                'slug' => $child->slug,
                'parent_id' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(DestinationResource::getUrl('index'));

        $this->assertDatabaseHas('destinations', ['id' => $child->id, 'parent_id' => null]);
        $this->assertDatabaseHas('redirects', [
            'old_path' => $root->getPath().$child->slug.'/',
            'model_type' => 'destination',
            'model_id' => $child->id,
        ]);
    }

    public function test_admin_can_delete_a_leaf_destination(): void
    {
        $destination = Destination::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $destination->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }
}
