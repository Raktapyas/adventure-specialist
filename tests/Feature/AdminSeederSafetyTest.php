<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post('/admin/pages', [
                'title' => 'HTML Page',
                'slug' => 'html-page',
                'content' => $html,
            ]);

        $this->assertDatabaseHas('pages', ['slug' => 'html-page', 'content' => $html]);
    }
}
