<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class MediaUploadFormErrorsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_list_page_renders_without_errors(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->assertOk();
    }

    public function test_invalid_multi_file_upload_returns_errors_not_500(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: [
                'media' => [
                    UploadedFile::fake()->create('good.txt', 10),   // not an image
                    UploadedFile::fake()->create('big.png', 6000),  // exceeds 5 MB
                ],
            ])
            ->assertHasActionErrors(['media']);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_invalid_upload_returns_errors_not_500(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListMedia::class)
            ->callAction('upload', data: [
                'media' => [
                    UploadedFile::fake()->create('not-an-image.txt', 10),
                ],
            ])
            ->assertHasActionErrors(['media']);

        $this->assertDatabaseCount('media', 0);
    }
}
