<?php

namespace Tests\Feature;

use App\Filament\Resources\DestinationResource\Pages\EditDestination;
use App\Filament\Resources\PackageResource\Pages\EditPackage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use App\Services\UrlHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class UrlHistoryTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_a_renaming_a_page_keeps_the_old_url_working(): void
    {
        $page = Page::factory()->create(['slug' => 'history']);

        $this->assertSame(200, $this->finalStatus('/about-us/history/'));

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'History', 'slug' => 'our-history'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'our-history']);
        $this->assertDatabaseHas('redirects', [
            'old_path' => '/about-us/history/',
            'model_type' => 'page',
            'model_id' => $page->id,
        ]);

        $chain = $this->walk('/about-us/history/');

        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/about-us/our-history/', $chain[0]['location']);
        $this->assertSame(200, $this->finalStatus('/about-us/our-history/'));
    }

    public function test_a_renaming_a_service_keeps_the_old_url_working(): void
    {
        $service = Service::factory()->create(['slug' => 'rafting']);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->fillForm([
                'title' => $service->title,
                'slug' => 'rafting-expeditions',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chain = $this->walk('/ast-services/rafting/');

        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/ast-services/rafting-expeditions/', $chain[0]['location']);
        $this->assertSame(200, $this->finalStatus('/ast-services/rafting-expeditions/'));
    }

    public function test_a_renaming_a_destination_keeps_the_old_url_working(): void
    {
        $destination = Destination::factory()->create(['slug' => 'tibet']);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $destination->getKey()])
            ->fillForm([
                'title' => $destination->title,
                'slug' => 'tibet-region',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chain = $this->walk('/destination/tibet/');

        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/destination/tibet-region/', $chain[0]['location']);
        $this->assertSame(200, $this->finalStatus('/destination/tibet-region/'));
    }

    public function test_a_renaming_a_package_keeps_the_old_url_working(): void
    {
        $package = Package::factory()->create(['slug' => 'special-one']);

        Livewire::actingAs($this->admin())
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->fillForm([
                'title' => $package->title,
                'slug' => 'special-one-plus',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chain = $this->walk('/special-package/special-one/');

        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/special-package/special-one-plus/', $chain[0]['location']);
        $this->assertSame(200, $this->finalStatus('/special-package/special-one-plus/'));
    }

    public function test_b_multiple_renames_collapse_to_one_hop(): void
    {
        $page = Page::factory()->create(['slug' => 'stage-a']);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => $page->title, 'slug' => 'stage-b'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::actingAs($admin)
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => $page->title, 'slug' => 'stage-c'])
            ->call('save')
            ->assertHasNoFormErrors();

        foreach (['/about-us/stage-a/', '/about-us/stage-b/'] as $old) {
            $chain = $this->walk($old);

            $this->assertSame([301, 200], array_column($chain, 'status'), "{$old} should resolve in one hop.");
            $this->assertSame('/about-us/stage-c/', $chain[0]['location'], "{$old} must not chain through stage-b.");
        }

        $this->assertSame(200, $this->finalStatus('/about-us/stage-c/'));
        $this->assertDatabaseHas('redirects', ['old_path' => '/about-us/stage-a/', 'model_id' => $page->id]);
        $this->assertDatabaseHas('redirects', ['old_path' => '/about-us/stage-b/', 'model_id' => $page->id]);
    }

    public function test_c_reparenting_a_child_keeps_its_old_url_working(): void
    {
        $rootA = Service::factory()->create(['slug' => 'alpha']);
        $child = Service::factory()->create(['slug' => 'bravo', 'parent_id' => $rootA->id]);
        $rootC = Service::factory()->create(['slug' => 'charlie']);

        $this->assertSame(200, $this->finalStatus('/ast-services/alpha/bravo/'));

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $child->getKey()])
            ->fillForm([
                'title' => $child->title,
                'slug' => $child->slug,
                'parent_id' => $rootC->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', ['id' => $child->id, 'parent_id' => $rootC->id]);
        $this->assertDatabaseHas('redirects', [
            'old_path' => '/ast-services/alpha/bravo/',
            'model_type' => 'service',
            'model_id' => $child->id,
        ]);

        $chain = $this->walk('/ast-services/alpha/bravo/');

        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/ast-services/charlie/bravo/', $chain[0]['location']);
        $this->assertSame(200, $this->finalStatus('/ast-services/charlie/bravo/'));

        $this->assertSame(200, $this->finalStatus('/ast-services/alpha/'));
        $this->assertDatabaseMissing('redirects', ['old_path' => '/ast-services/alpha/']);
    }

    public function test_d_descendant_urls_follow_an_ancestor_slug_change(): void
    {
        $root = Service::factory()->create(['slug' => 'rafting']);
        Service::factory()->create(['slug' => 'arun', 'parent_id' => $root->id]);

        $this->assertSame(200, $this->finalStatus('/ast-services/rafting/arun/'));

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $root->getKey()])
            ->fillForm([
                'title' => $root->title,
                'slug' => 'rafting-expeditions',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $childChain = $this->walk('/ast-services/rafting/arun/');
        $this->assertSame([301, 200], array_column($childChain, 'status'));
        $this->assertSame('/ast-services/rafting-expeditions/arun/', $childChain[0]['location']);

        $rootChain = $this->walk('/ast-services/rafting/');
        $this->assertSame([301, 200], array_column($rootChain, 'status'));
        $this->assertSame('/ast-services/rafting-expeditions/', $rootChain[0]['location']);

        $this->assertSame(200, $this->finalStatus('/ast-services/rafting-expeditions/arun/'));
        $this->assertSame(200, $this->finalStatus('/ast-services/rafting-expeditions/'));
    }

    public function test_d_descendant_urls_follow_an_ancestor_reparent(): void
    {
        $rootA = Destination::factory()->create(['slug' => 'tibet']);
        $rootB = Destination::factory()->create(['slug' => 'transfers']);
        $child = Destination::factory()->create(['slug' => 'tibet-tour', 'parent_id' => $rootA->id]);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $rootA->getKey()])
            ->fillForm([
                'title' => $rootA->title,
                'slug' => $rootA->slug,
                'parent_id' => $rootB->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(200, $this->finalStatus('/destination/transfers/tibet/'));
        $this->assertSame(200, $this->finalStatus('/destination/transfers/tibet/tibet-tour/'));

        $chain = $this->walk('/destination/tibet/tibet-tour/');
        $this->assertSame([301, 200], array_column($chain, 'status'));
        $this->assertSame('/destination/transfers/tibet/tibet-tour/', $chain[0]['location']);
    }

    public function test_e_duplicate_slug_is_rejected_on_update(): void
    {
        $service = Service::factory()->create(['slug' => 'alpha']);
        $other = Service::factory()->create(['slug' => 'bravo']);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $other->getKey()])
            ->fillForm([
                'title' => $other->title,
                'slug' => 'alpha',
            ])
            ->call('save')
            ->assertHasFormErrors(['slug']);

        $this->assertDatabaseHas('services', ['id' => $other->id, 'slug' => 'bravo']);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_f_invalid_parent_is_rejected_on_update(): void
    {
        $destination = Destination::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $destination->getKey()])
            ->fillForm([
                'title' => $destination->title,
                'slug' => $destination->slug,
                'parent_id' => 999999,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('destinations', ['id' => $destination->id, 'parent_id' => null]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_g_service_cannot_be_moved_under_itself(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->fillForm([
                'title' => $service->title,
                'slug' => $service->slug,
                'parent_id' => $service->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'parent_id' => null]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_g_service_cannot_be_moved_under_its_own_descendant(): void
    {
        $parent = Service::factory()->create();
        $child = Service::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $parent->getKey()])
            ->fillForm([
                'title' => $parent->title,
                'slug' => $parent->slug,
                'parent_id' => $child->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('services', ['id' => $parent->id, 'parent_id' => null]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_g_destination_cannot_be_moved_under_its_own_descendant(): void
    {
        $parent = Destination::factory()->create();
        $child = Destination::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $parent->getKey()])
            ->fillForm([
                'title' => $parent->title,
                'slug' => $parent->slug,
                'parent_id' => $child->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('destinations', ['id' => $parent->id, 'parent_id' => null]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_h_service_depth_limit_is_enforced_on_update(): void
    {
        $root = Service::factory()->create();
        $child = Service::factory()->create(['parent_id' => $root->id]);
        $sibling = Service::factory()->create(['parent_id' => $root->id]);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $child->getKey()])
            ->fillForm([
                'title' => $child->title,
                'slug' => $child->slug,
                'parent_id' => $sibling->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('services', ['id' => $child->id, 'parent_id' => $root->id]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_h_destination_depth_limit_is_enforced_on_update(): void
    {
        $l1 = Destination::factory()->create();
        $l2 = Destination::factory()->create(['parent_id' => $l1->id]);
        $l3 = Destination::factory()->create(['parent_id' => $l2->id]);
        $otherL2 = Destination::factory()->create();
        $otherL3 = Destination::factory()->create(['parent_id' => $otherL2->id]);

        Livewire::actingAs($this->admin())
            ->test(EditDestination::class, ['record' => $otherL3->getKey()])
            ->fillForm([
                'title' => $otherL3->title,
                'slug' => $otherL3->slug,
                'parent_id' => $l3->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertDatabaseHas('destinations', ['id' => $otherL3->id, 'parent_id' => $otherL2->id]);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_i_old_url_resolution_terminates_without_looping(): void
    {
        $service = Service::factory()->create(['slug' => 'paragliding']);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->fillForm([
                'title' => $service->title,
                'slug' => 'paragliding-himalaya',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chain = $this->walk('/ast-services/paragliding/');
        $statuses = array_column($chain, 'status');
        $paths = array_column($chain, 'path');

        $this->assertSame([301, 200], $statuses);
        $this->assertCount(2, array_unique($paths), 'No path may repeat in the redirect chain.');
        $this->assertSame([], array_diff($paths, ['/ast-services/paragliding/', '/ast-services/paragliding-himalaya/']));
    }

    public function test_k_unknown_urls_still_404(): void
    {
        foreach ([
            '/about-us/never-existed/',
            '/ast-services/never-existed/',
            '/destination/never-existed/',
            '/nepal/never-existed/',
            '/special-package/never-existed/',
        ] as $url) {
            $this->assertSame(404, $this->finalStatus($url), "Expected {$url} to 404.");
        }
    }

    public function test_m_guests_cannot_edit_slugs(): void
    {
        $page = Page::factory()->create(['slug' => 'original']);

        $this->get("/admin/pages/{$page->id}/edit")->assertRedirect('/admin/login');
    }

    public function test_m_non_admins_cannot_edit_slugs(): void
    {
        $page = Page::factory()->create(['slug' => 'original']);

        $this->actingAs(User::factory()->create())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertForbidden();
    }

    public function test_n_deleting_a_resource_cleans_up_its_redirects(): void
    {
        $package = Package::factory()->create(['slug' => 'doomed']);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->fillForm([
                'title' => $package->title,
                'slug' => 'doomed-renamed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(200, $this->finalStatus('/special-package/doomed/'));

        Livewire::actingAs($admin)
            ->test(EditPackage::class, ['record' => $package->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('redirects', ['old_path' => '/special-package/doomed/']);
        $this->assertSame(404, $this->finalStatus('/special-package/doomed/'));
        $this->assertSame(404, $this->finalStatus('/special-package/doomed-renamed/'));
    }

    public function test_n_deleting_an_unrenamed_resource_leaves_no_redirects(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->callAction('delete');

        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_o_slashless_old_urls_terminate_via_trailing_slash_and_history(): void
    {
        $service = Service::factory()->create(['slug' => 'bungee']);

        Livewire::actingAs($this->admin())
            ->test(EditService::class, ['record' => $service->getKey()])
            ->fillForm([
                'title' => $service->title,
                'slug' => 'bungee-nepal',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chain = $this->walk('/ast-services/bungee');
        $statuses = array_column($chain, 'status');

        $this->assertSame([301, 301, 200], $statuses);
        $this->assertSame('/ast-services/bungee/', $chain[0]['location']);
        $this->assertSame('/ast-services/bungee-nepal/', $chain[1]['location']);

        $fresh = $this->walk('/ast-services/bungee-nepal');
        $this->assertSame([301, 200], array_column($fresh, 'status'));
    }

    public function test_preview_path_reflects_a_reparent_even_when_parent_is_cached(): void
    {
        $root = Destination::factory()->create(['slug' => 'za-preview-root']);
        $newParent = Destination::factory()->create(['slug' => 'za-preview-new']);

        $child = Destination::factory()->create(['slug' => 'za-preview-child', 'parent_id' => $root->id]);

        $child->getPath();

        $sameParent = UrlHistoryService::previewPath($child, 'za-preview-child', $root->id);
        $this->assertSame('/destination/za-preview-root/za-preview-child/', $sameParent);

        $reparented = UrlHistoryService::previewPath($child, 'za-preview-child', $newParent->id);
        $this->assertSame('/destination/za-preview-new/za-preview-child/', $reparented);

        $reparentedAndRenamed = UrlHistoryService::previewPath($child, 'za-preview-renamed', $newParent->id);
        $this->assertSame('/destination/za-preview-new/za-preview-renamed/', $reparentedAndRenamed);
    }

    public function test_seeders_create_no_redirect_rows(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('redirects', 0);
    }
}
