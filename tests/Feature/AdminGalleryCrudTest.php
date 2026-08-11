<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGalleryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/gallery')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/gallery')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_gallery_image(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/gallery', [
                'image_url' => '/assets/images/himalaya.jpg',
                'caption' => 'Snow peaks',
            ])
            ->assertRedirect(route('admin.gallery.index'));

        $this->assertDatabaseHas('gallery_images', ['image_url' => '/assets/images/himalaya.jpg']);
    }

    public function test_image_url_is_required(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/gallery', ['caption' => 'No image'])
            ->assertSessionHasErrors('image_url');
    }

    public function test_admin_can_edit_a_gallery_image(): void
    {
        $image = GalleryImage::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/gallery/{$image->id}", [
                'image_url' => '/assets/images/updated.jpg',
                'caption' => 'Updated',
            ])
            ->assertRedirect(route('admin.gallery.edit', $image));

        $this->assertDatabaseHas('gallery_images', ['id' => $image->id, 'image_url' => '/assets/images/updated.jpg']);
    }

    public function test_admin_can_delete_a_gallery_image(): void
    {
        $image = GalleryImage::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/gallery/{$image->id}")
            ->assertRedirect(route('admin.gallery.index'));

        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }
}
