<?php

namespace Tests\Feature;

use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Pages\CreatePackage;
use App\Filament\Resources\PackageResource\Pages\EditPackage;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPackageCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/packages')->assertRedirect('/admin/login');
        $this->get('/admin/packages/create')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/packages')->assertForbidden();
        $this->actingAs($user)->get('/admin/packages/create')->assertForbidden();
    }

    public function test_admin_can_create_a_package(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePackage::class)
            ->fillForm([
                'title' => 'Everest Base Camp',
                'slug' => 'everest-base-camp',
                'duration_days' => 14,
                'content' => '<p>Body</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(PackageResource::getUrl('index'));

        $this->assertDatabaseHas('packages', [
            'slug' => 'everest-base-camp',
            'duration_days' => 14,
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Package::factory()->create(['slug' => 'taken']);

        Livewire::actingAs($this->admin())
            ->test(CreatePackage::class)
            ->fillForm(['title' => 'Dup', 'slug' => 'taken'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        $this->assertDatabaseCount('packages', 1);
    }

    public function test_slug_auto_generates_from_title(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePackage::class)
            ->set('data.title', 'Everest Base Camp Trek')
            ->assertFormSet(['slug' => 'everest-base-camp-trek']);
    }

    public function test_manual_slug_is_preserved_when_title_changes(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreatePackage::class)
            ->set('data.title', 'Everest Base Camp Trek')
            ->set('data.slug', 'everest-base-camp-2026')
            ->set('data.title', 'Everest Base Camp')
            ->assertFormSet(['slug' => 'everest-base-camp-2026']);
    }

    public function test_package_slug_can_be_changed_on_update(): void
    {
        $package = Package::factory()->create(['slug' => 'original']);

        Livewire::actingAs($this->admin())
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->fillForm(['title' => 'Renamed', 'slug' => 'renamed'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(PackageResource::getUrl('index'));

        $this->assertDatabaseHas('packages', ['id' => $package->id, 'slug' => 'renamed']);
        $this->assertDatabaseHas('redirects', [
            'old_path' => '/special-package/original/',
            'model_type' => 'package',
            'model_id' => $package->id,
        ]);
    }

    public function test_admin_can_edit_a_package(): void
    {
        $package = Package::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->fillForm(['title' => 'Updated', 'slug' => $package->slug])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('packages', ['id' => $package->id, 'title' => 'Updated']);
    }

    public function test_admin_can_delete_a_package(): void
    {
        $package = Package::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }
}
