<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class PublicServicesIndexTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    /**
     * Render the canonical services index through the HTTP kernel so the
     * trailing-slash URL is not trimmed by the test client.
     */
    private function indexContent(): string
    {
        $request = Request::create('/ast-services/', 'GET');
        $request->headers->set('host', 'localhost');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        return $response->getContent();
    }

    public function test_index_renders_centered_heading_and_dynamic_service_data(): void
    {
        Service::factory()->create([
            'title' => 'Mountain Flight',
            'slug' => 'mountain-flight',
            'excerpt' => 'A breath-taking panorama of the Himalaya.',
            'icon' => 'heroicon-o-paper-airplane',
            'is_published' => true,
        ]);

        $content = $this->indexContent();

        $this->assertStringContainsString('Explore our services', $content);
        $this->assertStringContainsString('mx-auto text-center', $content);
        $this->assertStringContainsString('Mountain Flight', $content);
        $this->assertStringContainsString('A breath-taking panorama of the Himalaya.', $content);
        $this->assertStringContainsString('3.269 3.125', $content);
    }

    public function test_index_shows_empty_state_without_hardcoded_fallback_cards(): void
    {
        $content = $this->indexContent();

        $this->assertStringContainsString('Services coming soon.', $content);
        $this->assertStringNotContainsString('flip-inner', $content);
    }
}
