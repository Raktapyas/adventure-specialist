<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Services\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaReferenceNormalizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_page_cover_image_absolute_url_is_normalized_and_usage_tracked(): void
    {
        $media = Media::factory()->create(['path' => '/storage/media/2026/08/pic.jpg', 'is_legacy' => false]);
        $page = Page::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", [
                'title' => $page->title,
                'slug' => $page->slug,
                'cover_image' => 'http://localhost/storage/media/2026/08/pic.jpg',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'cover_image' => '/storage/media/2026/08/pic.jpg']);

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);
    }

    public function test_gallery_image_url_absolute_url_is_normalized(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/gallery', [
                'image_url' => 'https://localhost/assets/images/himalaya.jpg',
                'caption' => 'Snow peaks',
            ])
            ->assertRedirect(route('admin.gallery.index'));

        $this->assertDatabaseHas('gallery_images', ['image_url' => '/assets/images/himalaya.jpg']);
    }

    public function test_usage_sync_matches_an_absolute_url_to_its_host_relative_media_row(): void
    {
        $media = Media::factory()->create(['path' => '/storage/media/pic.jpg']);
        $page = Page::factory()->create();

        app(MediaUsageService::class)->sync($page, 'cover_image', 'https://cdn.example.com/storage/media/pic.jpg');

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);
    }

    public function test_content_usage_scan_links_absolute_image_src_urls(): void
    {
        $media = Media::factory()->create(['path' => '/storage/media/inline.jpg', 'is_legacy' => false]);
        $page = Page::factory()->create([
            'content' => '<p><img src="http://localhost/storage/media/inline.jpg" alt="Inline"></p>',
        ]);

        app(MediaUsageService::class)->syncContent($page, $page->content);

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);
    }

    public function test_page_cover_image_with_path_traversal_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pages', [
                'title' => 'Bad',
                'slug' => 'bad-page',
                'cover_image' => '/assets/../secret.jpg',
            ])
            ->assertSessionHasErrors('cover_image');

        $this->assertDatabaseMissing('pages', ['slug' => 'bad-page']);
    }

    public function test_page_cover_image_with_protocol_relative_url_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pages', [
                'title' => 'Bad',
                'slug' => 'bad-page',
                'cover_image' => '//evil.example.com/pic.jpg',
            ])
            ->assertSessionHasErrors('cover_image');

        $this->assertDatabaseMissing('pages', ['slug' => 'bad-page']);
    }

    public function test_gallery_image_url_must_be_a_host_relative_path(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/gallery', [
                'image_url' => 'C:\\windows\\not-an-image.jpg',
            ])
            ->assertSessionHasErrors('image_url');

        $this->assertDatabaseCount('gallery_images', 0);
    }

    public function test_legacy_host_relative_cover_image_still_works(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", [
                'title' => $page->title,
                'slug' => $page->slug,
                'cover_image' => '/assets/images/pages/15.jpg',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'cover_image' => '/assets/images/pages/15.jpg']);
    }
}
