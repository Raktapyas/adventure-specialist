<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Services\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MediaUsageScanTest extends TestCase
{
    use RefreshDatabase;

    private function media(string $path): Media
    {
        return Media::factory()->create(['path' => $path]);
    }

    public function test_scan_preserves_content_usage_the_scanner_cannot_detect(): void
    {
        $page = Page::factory()->create([
            'content' => '<div style="background:url(https://img.example.com/pic.jpg)"></div>',
        ]);
        $media = $this->media('/assets/images/pic.jpg');

        MediaUsage::create([
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);

        Artisan::call('media:scan-usage');

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);
    }

    public function test_scan_links_detectable_image_references(): void
    {
        $page = Page::factory()->create([
            'content' => '<p><img src="/assets/images/pic.jpg" alt="Pic"></p>',
        ]);
        $this->media('/assets/images/pic.jpg');

        Artisan::call('media:scan-usage');

        $this->assertDatabaseHas('media_usages', [
            'media_id' => Media::where('path', '/assets/images/pic.jpg')->firstOrFail()->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);

        $this->assertStringContainsString('1 references linked', Artisan::output());
    }

    public function test_repeated_scans_are_idempotent(): void
    {
        $page = Page::factory()->create([
            'content' => '<p><img src="/assets/images/pic.jpg"></p>',
        ]);
        $media = $this->media('/assets/images/pic.jpg');

        Artisan::call('media:scan-usage');
        Artisan::call('media:scan-usage');

        $this->assertSame(1, MediaUsage::where('media_id', $media->id)
            ->where('model_type', $page->getMorphClass())
            ->where('model_id', $page->id)
            ->where('field', 'content')
            ->count());
    }

    public function test_sync_content_still_rebuilds_usages_on_content_replacement(): void
    {
        $page = Page::factory()->create([
            'content' => '<p><img src="/assets/images/first.jpg"></p>',
        ]);
        $first = $this->media('/assets/images/first.jpg');
        $second = $this->media('/assets/images/second.jpg');

        app(MediaUsageService::class)->syncContent($page, $page->content);

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $first->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);

        app(MediaUsageService::class)->syncContent($page, '<p><img src="/assets/images/second.jpg"></p>');

        $this->assertDatabaseMissing('media_usages', [
            'media_id' => $first->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);
        $this->assertDatabaseHas('media_usages', [
            'media_id' => $second->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'content',
        ]);
    }
}
