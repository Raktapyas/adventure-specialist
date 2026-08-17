<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryImageResource;
use App\Filament\Resources\GalleryImageResource\Pages\CreateGalleryImage;
use App\Filament\Resources\GalleryImageResource\Pages\EditGalleryImage;
use App\Filament\Resources\GalleryImageResource\Pages\ListGalleryImages;
use App\Models\GalleryImage;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminGalleryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/gallery-images')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/gallery-images')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_gallery_image(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateGalleryImage::class)
            ->fillForm([
                'image_url' => '/assets/images/himalaya.jpg',
                'caption' => 'Snow peaks',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(GalleryImageResource::getUrl('index'));

        $this->assertDatabaseHas('gallery_images', ['image_url' => '/assets/images/himalaya.jpg']);
    }

    public function test_image_url_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateGalleryImage::class)
            ->fillForm(['caption' => 'No image'])
            ->call('create')
            ->assertHasFormErrors(['image_url']);

        $this->assertDatabaseCount('gallery_images', 0);
    }

    public function test_admin_can_edit_a_gallery_image(): void
    {
        $image = GalleryImage::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditGalleryImage::class, ['record' => $image->getKey()])
            ->fillForm([
                'image_url' => '/assets/images/updated.jpg',
                'caption' => 'Updated',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(GalleryImageResource::getUrl('index'));

        $this->assertDatabaseHas('gallery_images', ['id' => $image->id, 'image_url' => '/assets/images/updated.jpg']);
    }

    public function test_admin_can_delete_a_gallery_image(): void
    {
        $image = GalleryImage::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditGalleryImage::class, ['record' => $image->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }

    public function test_admin_can_view_gallery_images_with_thumbnails(): void
    {
        $image = GalleryImage::factory()->create([
            'image_url' => '/assets/images/himalaya.jpg',
            'caption' => 'Snow peaks',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListGalleryImages::class)
            ->assertSee('/assets/images/himalaya.jpg')
            ->assertSee('Snow peaks');
    }

    public function test_library_grid_shows_caption_and_sort_order(): void
    {
        GalleryImage::factory()->create([
            'caption' => 'Snow peaks',
            'sort_order' => 3,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListGalleryImages::class)
            ->assertSee('Snow peaks')
            ->assertSee('Sort order: 3');
    }

    public function test_gallery_image_select_saves_media_path(): void
    {
        $media = Media::factory()->create([
            'name' => 'summit.jpg',
            'path' => '/assets/images/summit.jpg',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CreateGalleryImage::class)
            ->fillForm([
                'image_url' => $media->path,
                'caption' => 'Snow peaks',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('gallery_images', [
            'image_url' => '/assets/images/summit.jpg',
        ]);
    }

    public function test_gallery_image_edit_shows_media_name_for_selected_path(): void
    {
        $media = Media::factory()->create([
            'name' => 'summit.jpg',
            'path' => '/assets/images/summit.jpg',
        ]);
        $image = GalleryImage::factory()->create([
            'image_url' => $media->path,
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditGalleryImage::class, ['record' => $image->getKey()])
            ->call('getFormSelectOptionLabel', 'data.image_url')
            ->assertReturned(
                '<div class="flex items-center gap-3">'
                .'<img src="'.url('/assets/images/summit.jpg').'" alt="summit.jpg" class="h-10 w-10 rounded object-cover">'
                .'<span>summit.jpg</span>'
                .'</div>'
            );
    }

    public function test_gallery_image_select_search_results_include_thumbnails(): void
    {
        $media = Media::factory()->create([
            'name' => 'summit.jpg',
            'path' => '/assets/images/summit.jpg',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CreateGalleryImage::class)
            ->call('getFormSelectSearchResults', 'data.image_url', 'summit')
            ->assertReturned([
                [
                    'label' => '<div class="flex items-center gap-3">'
                        .'<img src="'.url('/assets/images/summit.jpg').'" alt="summit.jpg" class="h-10 w-10 rounded object-cover">'
                        .'<span>summit.jpg</span>'
                        .'</div>',
                    'value' => '/assets/images/summit.jpg',
                    'disabled' => false,
                ],
            ]);
    }
}
