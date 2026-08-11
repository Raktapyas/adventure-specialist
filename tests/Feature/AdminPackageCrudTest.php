<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/packages')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/packages')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_package(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/packages', [
                'title' => 'Everest Base Camp',
                'slug' => 'everest-base-camp',
                'duration_days' => 14,
                'content' => '<p>Body</p>',
            ])
            ->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseHas('packages', [
            'slug' => 'everest-base-camp',
            'duration_days' => 14,
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Package::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin())
            ->post('/admin/packages', ['title' => 'Dup', 'slug' => 'taken'])
            ->assertSessionHasErrors('slug');
    }

    public function test_package_slug_can_be_changed_on_update(): void
    {
        $package = Package::factory()->create(['slug' => 'original']);

        $this->actingAs($this->admin())
            ->put("/admin/packages/{$package->id}", [
                'title' => 'Renamed',
                'slug' => 'renamed',
            ])
            ->assertRedirect(route('admin.packages.edit', $package));

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

        $this->actingAs($this->admin())
            ->put("/admin/packages/{$package->id}", ['title' => 'Updated', 'slug' => $package->slug])
            ->assertRedirect(route('admin.packages.edit', $package));

        $this->assertDatabaseHas('packages', ['id' => $package->id, 'title' => 'Updated']);
    }

    public function test_admin_can_delete_a_package(): void
    {
        $package = Package::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/packages/{$package->id}")
            ->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }
}
