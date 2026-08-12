<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAboutNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_about_us_child_appears_in_public_navigation(): void
    {
        $about = Page::factory()->create(['slug' => 'about', 'title' => 'About Us']);
        Page::factory()->create([
            'parent_id' => $about->id,
            'slug' => 'Alo-sir',
            'title' => 'Alo',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Alo')
            ->assertSee('/about-us/Alo-sir/');
    }

    public function test_unpublished_about_us_child_is_hidden_from_navigation(): void
    {
        $about = Page::factory()->create(['slug' => 'about', 'title' => 'About Us']);
        Page::factory()->create([
            'parent_id' => $about->id,
            'slug' => 'draft',
            'title' => 'Draft Page',
            'is_published' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Draft Page');
    }

    public function test_top_level_page_is_not_listed_under_about_us(): void
    {
        Page::factory()->create(['slug' => 'about', 'title' => 'About Us']);
        Page::factory()->create(['slug' => 'standalone', 'title' => 'Standalone']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Standalone');
    }

    public function test_migration_links_alo_under_about_us(): void
    {
        $about = Page::factory()->create(['slug' => 'about']);
        $alo = Page::factory()->create(['slug' => 'Alo-sir', 'parent_id' => null]);

        $migration = require database_path('migrations/2026_08_12_010000_link_alo_page_to_about_us.php');
        $migration->up();

        $this->assertDatabaseHas('pages', ['id' => $alo->id, 'parent_id' => $about->id]);
    }

    public function test_migration_is_idempotent_and_ignores_pages_with_parents(): void
    {
        $about = Page::factory()->create(['slug' => 'about']);
        $alo = Page::factory()->create(['slug' => 'Alo-sir', 'parent_id' => null]);
        $other = Page::factory()->create(['slug' => 'other', 'parent_id' => $about->id]);

        $migration = require database_path('migrations/2026_08_12_010000_link_alo_page_to_about_us.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseHas('pages', ['id' => $alo->id, 'parent_id' => $about->id]);
        $this->assertDatabaseHas('pages', ['id' => $other->id, 'parent_id' => $about->id]);
        $this->assertDatabaseCount('pages', 3);
    }
}
