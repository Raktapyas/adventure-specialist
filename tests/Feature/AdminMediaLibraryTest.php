<?php

namespace Tests\Feature;

use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    private function png(string $name = 'image.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    private function jpeg(string $name = 'image.jpg'): UploadedFile
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/media')->assertRedirect('/admin/login');
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

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->assertSee('summit.jpg')
            ->assertSee('gallery.png');
    }

    public function test_library_grid_shows_name_and_size_metadata(): void
    {
        $media = Media::factory()->create(['name' => 'summit.jpg', 'size' => 2048]);

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->assertSee('summit.jpg')
            ->assertSee($media->humanSize());
    }

    public function test_historical_media_hidden_by_global_scopes_still_renders_in_library(): void
    {
        $globalScopes = Media::getAllGlobalScopes();

        try {
            Media::addGlobalScope(
                'hide_historical_media_for_regression',
                fn (Builder $query) => $query->where('is_legacy', false),
            );

            Media::factory()->create([
                'name' => 'historical.jpg',
                'path' => '/assets/images/historical.jpg',
                'is_legacy' => true,
            ]);

            Livewire::actingAs($this->admin())
                ->test(ListMedia::class)
                ->assertSee('historical.jpg')
                ->assertSee('/assets/images/historical.jpg');
        } finally {
            Media::setAllGlobalScopes($globalScopes);
        }
    }

    public function test_admin_can_filter_by_source(): void
    {
        Media::factory()->uploaded()->create(['name' => 'fresh.png']);

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->filterTable('is_legacy', true)
            ->assertDontSee('fresh.png');
    }

    public function test_admin_can_upload_images(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ListMedia::class)
            ->callAction('upload', data: [
                'media' => [$this->png()],
                'alt_text' => 'A test image',
            ]);

        $media = Media::firstOrFail();

        $this->assertFalse($media->is_legacy);
        $this->assertSame('png', $media->extension);
        $this->assertSame('A test image', $media->alt_text);
        $this->assertSame($admin->id, $media->created_by);

        Storage::disk('public')->assertExists($media->storage_path);
    }

    public function test_admin_can_upload_jpeg_extension_files_like_jpg(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$this->jpeg('photo.jpeg')]]);

        $media = Media::firstOrFail();

        $this->assertFalse($media->is_legacy);
        $this->assertSame('jpeg', $media->extension);
        $this->assertSame('image/jpeg', $media->mime_type);

        Storage::disk('public')->assertExists($media->storage_path);
        $this->assertStringStartsWith('/storage/', $media->path);
    }

    public function test_jpg_and_jpeg_extensions_are_both_accepted_for_jpeg_contents(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$this->jpeg('photo.jpg'), $this->jpeg('photo.jpeg')]]);

        $this->assertSame(2, Media::count());
        $this->assertSame(['jpg', 'jpeg'], Media::orderBy('id')->pluck('extension')->all());
    }

    public function test_jpeg_extension_with_non_jpeg_contents_is_rejected(): void
    {
        $png = $this->png('mismatch.jpeg');

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$png]])
            ->assertHasErrors(['media']);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_rejects_non_images(): void
    {
        $bad = UploadedFile::fake()->createWithContent('shell.png', '<?php echo "nope"; ?>');

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$bad]])
            ->assertHasErrors(['media']);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->createWithContent(
            'vector.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"></svg>'
        );

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$svg]])
            ->assertHasErrors(['media']);

        $this->assertDatabaseCount('media', 0);
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

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callTableAction('delete', $media)
            ->assertNotified();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_legacy_media_can_be_force_deleted_and_file_kept(): void
    {
        Storage::fake('public');
        $media = Media::factory()->create(['path' => '/assets/images/kept.jpg']);

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callTableAction('delete', $media, data: ['force' => true]);

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

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callTableAction('delete', $media, data: ['force' => true]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_usages', ['media_id' => $media->id]);
        Storage::disk('public')->assertMissing($media->storage_path);
    }

    public function test_upload_stores_host_relative_path(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$this->png()]]);

        $media = Media::firstOrFail();

        $this->assertStringStartsWith('/storage/', $media->path);
        $this->assertStringNotContainsString('://', $media->path);
    }

    public function test_uploaded_thumbnail_renders_host_relative_path(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$this->png()]]);

        $media = Media::firstOrFail();

        Livewire::actingAs($admin)
            ->test(ListMedia::class)
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

        $label = str_replace('App\\Models\\', '', $page->getMorphClass()).'#'.$page->id.' · cover_image';

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callTableAction('delete', $media)
            ->assertNotified(
                Notification::make()
                    ->danger()
                    ->title('Cannot delete')
                    ->body('This image is in use by 1 item(s): '.$label.'. Reassign or remove those references first, or force-delete.')
            );

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->storage_path);
    }

    public function test_unused_uploaded_media_can_be_deleted(): void
    {
        Storage::fake('public');
        $media = Media::factory()->uploaded()->create();

        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callTableAction('delete', $media);

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

            Livewire::actingAs($this->admin())
                ->test(ListMedia::class)
                ->callTableAction('delete', $media, data: ['force' => true]);

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

        Livewire::actingAs($this->admin())
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm([
                'title' => $page->title,
                'slug' => $page->slug,
                'cover_image' => $media->path,
                'content' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
            'field' => 'cover_image',
        ]);
    }
}
