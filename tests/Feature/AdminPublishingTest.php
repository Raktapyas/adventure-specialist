<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class AdminPublishingTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_unpublished_pages_return_404_publicly(): void
    {
        Page::factory()->create(['slug' => 'about', 'title' => 'About', 'is_published' => false]);

        $this->assertSame(404, $this->finalStatus('/about-us/'));
    }

    public function test_unpublished_services_return_404_publicly(): void
    {
        Service::factory()->create(['slug' => 'trekking', 'title' => 'ZebraUnpublishedTrek123', 'is_published' => false]);

        $this->assertSame(404, $this->finalStatus('/ast-services/trekking/'));

        $request = Request::create('/ast-services/', 'GET');
        $request->headers->set('host', 'localhost');
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString('ZebraUnpublishedTrek123', $response->getContent());
    }

    public function test_unpublished_destinations_return_404_publicly(): void
    {
        Destination::factory()->create(['slug' => 'nepal', 'title' => 'Nepal', 'is_published' => false]);

        $this->assertSame(404, $this->finalStatus('/nepal/'));
    }

    public function test_unpublished_packages_return_404_publicly(): void
    {
        Package::factory()->create(['slug' => 'everest', 'title' => 'Everest Trek', 'is_published' => false]);

        $this->assertSame(404, $this->finalStatus('/special-package/everest/'));
    }

    public function test_unpublished_items_are_hidden_from_the_home_page(): void
    {
        Service::factory()->create(['slug' => 'trekking', 'title' => 'Hidden Trek', 'is_published' => false]);
        Package::factory()->create(['slug' => 'everest', 'title' => 'Hidden Everest', 'is_published' => false]);

        $this->get('/')->assertOk()->assertDontSee('Hidden Trek')->assertDontSee('Hidden Everest');
    }

    public function test_admin_can_unpublish_and_republish_a_page(): void
    {
        $page = Page::factory()->create(['slug' => 'about', 'title' => 'About', 'is_published' => true]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'About', 'slug' => 'about', 'is_published' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'is_published' => false]);
        $this->assertSame(404, $this->finalStatus('/about-us/'));

        Livewire::actingAs($admin)
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'About', 'slug' => 'about', 'is_published' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'is_published' => true]);
        $this->assertSame(200, $this->finalStatus('/about-us/'));
    }

    public function test_redirect_to_an_unpublished_target_returns_404(): void
    {
        $page = Page::factory()->create(['slug' => 'our-history', 'title' => 'History', 'is_published' => false]);

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['title' => 'History', 'slug' => 'history-renamed', 'is_published' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        // The old path now resolves via history, but the owner is unpublished.
        $this->assertSame(404, $this->finalStatus('/about-us/our-history/'));
    }
}
