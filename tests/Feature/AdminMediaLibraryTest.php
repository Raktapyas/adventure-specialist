<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function png(string $name = 'image.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/media')->assertRedirect('/login');
        $this->get('/admin/media/create')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/media')
            ->assertForbidden();
    }

    public function test_admin_can_view_the_library(): void
    {
        Media::factory()->create(['name' => 'summit.jpg', 'path' => '/assets/images/summit.jpg']);
        Media::factory()->uploaded()->create(['name' => 'gallery.png']);

        $this->actingAs($this->admin())
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('summit.jpg')
            ->assertSee('gallery.png');
    }

    public function test_admin_can_filter_by_source(): void
    {
        Media::factory()->uploaded()->create(['name' => 'fresh.png']);

        $this->actingAs($this->admin())
            ->get('/admin/media?source=legacy')
            ->assertOk()
            ->assertDontSee('fresh.png');
    }

    public function test_admin_can_upload_images(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/media', [
                'media' => [$this->png()],
                'alt_text' => 'A test image',
            ])
            ->assertRedirect(route('admin.media.index'));

        $media = Media::firstOrFail();

        $this->assertFalse($media->is_legacy);
        $this->assertSame('png', $media->extension);
        $this->assertSame('A test image', $media->alt_text);
        $this->assertSame($admin->id, $media->created_by);

        Storage::disk('public')->assertExists($media->storage_path);
    }

    public function test_upload_rejects_non_images(): void
    {
        $bad = UploadedFile::fake()->createWithContent('shell.png', '<?php echo "nope"; ?>');

        $this->actingAs($this->admin())
            ->post('/admin/media', ['media' => [$bad]])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->createWithContent(
            'vector.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"></svg>'
        );

        $this->actingAs($this->admin())
            ->post('/admin/media', ['media' => [$svg]])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('media', 0);
    }

    public function test_picker_data_returns_json(): void
    {
        Media::factory()->create(['name' => 'peak.jpg', 'path' => '/assets/images/peak.jpg']);

        $this->actingAs($this->admin())
            ->getJson(route('admin.media.picker-data'))
            ->assertOk()
            ->assertJsonPath('items.0.name', 'peak.jpg')
            ->assertJsonStructure(['items' => [['id', 'name', 'url', 'is_legacy', 'usages']], 'has_more', 'next_page']);
    }

    public function test_legacy_media_in_use_cannot_be_deleted(): void
    {
        $media = Media::factory()->create(['path' => '/assets/images/inuse.jpg']);
        $page = Page::factory()->create();

        MediaUsage::create([
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/media/{$media->id}")
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_legacy_media_can_be_force_deleted_and_file_kept(): void
    {
        Storage::fake('public');
        $media = Media::factory()->create(['path' => '/assets/images/kept.jpg']);

        $this->actingAs($this->admin())
            ->delete("/admin/media/{$media->id}?force=1")
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_uploaded_media_delete_removes_file_and_usage(): void
    {
        Storage::fake('public');
        $media = Media::factory()->uploaded()->create();
        $page = Page::factory()->create();

        MediaUsage::create([
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/media/{$media->id}?force=1")
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_usages', ['media_id' => $media->id]);
        Storage::disk('public')->assertMissing($media->storage_path);
    }

    public function test_upload_stores_host_relative_path(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/media', ['media' => [$this->png()]])
            ->assertRedirect(route('admin.media.index'));

        $media = Media::firstOrFail();

        $this->assertStringStartsWith('/storage/', $media->path);
        $this->assertStringNotContainsString('://', $media->path);
    }

    public function test_uploaded_thumbnail_renders_host_relative_path(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/media', ['media' => [$this->png()]])
            ->assertRedirect(route('admin.media.index'));

        $media = Media::firstOrFail();

        $this->actingAs($this->admin())
            ->get('/admin/media')
            ->assertOk()
            ->assertSee($media->path);
    }

    public function test_uploaded_media_in_use_cannot_be_deleted_and_shows_usage_label(): void
    {
        Storage::fake('public');
        $media = Media::factory()->uploaded()->create();
        Storage::disk('public')->put($media->storage_path, 'fake-image');
        $page = Page::factory()->create();

        MediaUsage::create([
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/media/{$media->id}")
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->storage_path);

        $label = str_replace('App\\Models\\', '', $page->getMorphClass()).'#'.$page->id.' · cover_image';
        $this->assertStringContainsString($label, session('error'));
    }

    public function test_unused_uploaded_media_can_be_deleted(): void
    {
        Storage::fake('public');
        $media = Media::factory()->uploaded()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/media/{$media->id}")
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->storage_path);
    }

    public function test_legacy_force_delete_never_touches_the_physical_file(): void
    {
        $file = public_path('assets/images/legacy-kept.jpg');
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, 'legacy');

        try {
            $media = Media::factory()->create(['path' => '/assets/images/legacy-kept.jpg']);

            $this->actingAs($this->admin())
                ->delete("/admin/media/{$media->id}?force=1")
                ->assertRedirect(route('admin.media.index'))
                ->assertSessionHas('status');

            $this->assertDatabaseMissing('media', ['id' => $media->id]);
            $this->assertFileExists($file);
        } finally {
            @unlink($file);
        }
    }

    public function test_page_update_tracks_media_usage(): void
    {
        $media = Media::factory()->uploaded()->create();
        $page = Page::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/pages/{$page->id}", [
                'title' => $page->title,
                'slug' => $page->slug,
                'cover_image' => $media->path,
                'content' => null,
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);
    }
}
