<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class PublicMediaRenderingTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    /**
     * Canonical routes end in a slash; the test client trims it, so go
     * straight through the kernel like MakesKernelRequests does.
     */
    private function canonicalContent(string $uri): string
    {
        $request = Request::create($uri, 'GET');
        $request->headers->set('host', 'localhost');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode(), "Expected {$uri} to resolve to 200.");

        return $response->getContent();
    }

    public function test_gallery_page_renders_videos_and_images_appropriately(): void
    {
        $video = Media::factory()->video()->create(['path' => '/assets/images/flight.mp4']);
        GalleryImage::factory()->create(['image_url' => $video->path, 'caption' => 'Cinematic flight', 'sort_order' => 1]);
        GalleryImage::factory()->create(['image_url' => '/assets/images/still.jpg', 'caption' => 'Still peak', 'sort_order' => 2]);

        $content = $this->canonicalContent('/gallery/');

        // The video item renders as a looping inline video…
        $this->assertStringContainsString('<video', $content);
        $this->assertStringContainsString('autoplay muted loop playsinline', $content);
        $this->assertStringContainsString('/assets/images/flight.mp4', $content);

        // …while the legacy image (no Media row) still renders as an img.
        $this->assertStringContainsString('<img', $content);
        $this->assertStringContainsString('/assets/images/still.jpg', $content);
    }

    public function test_homepage_renders_image_slides_as_img_elements(): void
    {
        HeroSlide::factory()->create(['title' => 'Image slide']);

        $content = $this->get('/')->getContent();

        $this->assertStringContainsString('<img', $content);
        $this->assertStringNotContainsString('<video', $content);
    }

    public function test_homepage_fluid_grid_pads_with_images_not_videos(): void
    {
        $video = Media::factory()->video()->create(['path' => '/assets/images/flight.mp4']);
        GalleryImage::factory()->create(['image_url' => $video->path, 'sort_order' => 1]);
        GalleryImage::factory()->create(['image_url' => '/assets/images/still.jpg', 'sort_order' => 2]);

        $content = $this->get('/')->getContent();

        // Six tiles are rendered from two items; the four padded tiles must
        // reuse the IMAGE, so the autoplaying clip appears exactly once.
        $this->assertSame(1, substr_count($content, '<video'));
    }

    public function test_homepage_fluid_grid_renders_videos_when_present(): void
    {
        $video = Media::factory()->video()->create(['path' => '/assets/images/flight.mp4']);
        GalleryImage::factory()->create(['image_url' => $video->path, 'sort_order' => 1]);

        $content = $this->get('/')->getContent();

        $this->assertStringContainsString('<video', $content);
        $this->assertStringContainsString('playsinline', $content);
    }
}
