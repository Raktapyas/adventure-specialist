<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_do_not_overwrite_admin_edits(): void
    {
        $page = Page::factory()->create(['slug' => 'about', 'title' => 'Edited By Admin']);
        $service = Service::factory()->create(['slug' => 'trekking', 'title' => 'Edited By Admin']);

        $this->seed();

        $this->assertDatabaseHas('pages', ['slug' => 'about', 'title' => 'Edited By Admin']);
        $this->assertDatabaseHas('services', ['slug' => 'trekking', 'title' => 'Edited By Admin']);
    }

    public function test_gallery_seed_is_keyed_on_image_url(): void
    {
        $image = GalleryImage::factory()->create(['image_url' => '/assets/images/hero.jpg', 'caption' => 'Admin Caption']);

        $this->seed();

        $this->assertDatabaseHas('gallery_images', ['image_url' => '/assets/images/hero.jpg', 'caption' => 'Admin Caption']);
    }

    public function test_raw_html_round_trips_through_crud(): void
    {
        $html = '<h2>Title</h2><p>Hello <strong>world</strong> &amp; friends</p>';

        Livewire::actingAs(User::factory()->create(['id' => 1, 'is_admin' => true]))
            ->test(CreatePage::class)
            ->fillForm([
                'title' => 'HTML Page',
                'slug' => 'html-page',
                'content' => $html,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'html-page', 'content' => $html]);
    }

    public function test_admin_seeder_assigns_super_admin_role(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertSame('admin', $admin->role);
    }

    public function test_seeders_are_idempotent_when_run_twice(): void
    {
        $this->seed();

        $countsBefore = [
            'pages' => Page::count(),
            'services' => Service::count(),
            'destinations' => Destination::count(),
            'packages' => Package::count(),
        ];

        $this->seed();

        $this->assertSame($countsBefore['pages'], Page::count());
        $this->assertSame($countsBefore['services'], Service::count());
        $this->assertSame($countsBefore['destinations'], Destination::count());
        $this->assertSame($countsBefore['packages'], Package::count());
    }

    public function test_seeded_records_are_published_by_default(): void
    {
        $this->seed();

        $this->assertSame(Page::count(), Page::published()->count());
        $this->assertSame(Service::count(), Service::published()->count());
        $this->assertSame(Destination::count(), Destination::published()->count());
        $this->assertSame(Package::count(), Package::published()->count());
    }
}
