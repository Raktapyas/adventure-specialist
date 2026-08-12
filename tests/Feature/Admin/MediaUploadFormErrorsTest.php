<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class MediaUploadFormErrorsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_create_page_returns_200_without_errors(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/media/create')
            ->assertOk();
    }

    public function test_create_page_renders_nested_multi_file_validation_errors_without_type_error(): void
    {
        $this->actingAs($this->admin());

        // Build the exact nested error bag a failed multi-file upload produces:
        // one entry per file under media.0, media.1, each an array of messages.
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag([
            'media.0' => ['The media.0 field must be an image.'],
            'media.1' => ['The media.1 field must not be greater than 5120 kilobytes.'],
        ]));

        // Regression: rendering the upload form with this bag must not throw
        // a TypeError from x-input-error receiving nested arrays.
        $html = view('admin.media.create')->with('errors', $errors)->render();

        // The user sees each individual validation error on the form.
        $this->assertStringContainsString('The media.0 field must be an image.', $html);
        $this->assertStringContainsString('The media.1 field must not be greater than 5120 kilobytes.', $html);
    }

    public function test_invalid_multi_file_upload_redirects_with_errors_not_500(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/media/create')
            ->post('/admin/media', [
                'media' => [
                    UploadedFile::fake()->create('good.txt', 10),   // not an image
                    UploadedFile::fake()->create('big.png', 6000),  // exceeds 5 MB
                ],
            ])
            ->assertSessionHasErrors(['media.0', 'media.1'])
            ->assertStatus(302)
            ->assertRedirect('/admin/media/create');
    }

    public function test_invalid_upload_returns_validation_redirect_not_500(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/media', [
                'media' => [
                    UploadedFile::fake()->create('not-an-image.txt', 10),
                ],
            ])
            ->assertSessionHasErrors(['media.0'])
            ->assertStatus(302)
            ->assertRedirect();
    }
}
