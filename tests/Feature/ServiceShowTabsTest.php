<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class ServiceShowTabsTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    /**
     * Render the canonical service page through the HTTP kernel so the
     * trailing-slash URL is not trimmed by the test client.
     */
    private function serviceContent(Service $service): string
    {
        $request = Request::create($service->getPath(), 'GET');
        $request->headers->set('host', 'localhost');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        return $response->getContent();
    }

    public function test_populated_service_renders_all_tabs(): void
    {
        $service = Service::factory()->create([
            'content' => '<p>Overview body</p>',
            'itinerary' => '<p>Itinerary body</p>',
            'includes' => '<ul><li>Guide</li></ul>',
            'excludes' => '<ul><li>Tips</li></ul>',
        ]);

        $content = $this->serviceContent($service);

        $this->assertStringContainsString('Trip Overview', $content);
        $this->assertStringContainsString('Detail Itinerary', $content);
        $this->assertStringContainsString('Includes', $content);
        $this->assertStringContainsString('Excludes', $content);
        $this->assertStringContainsString('Book Now', $content);
        $this->assertStringContainsString('Overview body', $content);
        $this->assertStringContainsString('Itinerary body', $content);
        $this->assertStringContainsString('Guide', $content);
        $this->assertStringContainsString('Tips', $content);
    }

    public function test_empty_tabs_are_hidden_from_the_tab_bar(): void
    {
        $service = Service::factory()->create([
            'content' => '<p>Overview body</p>',
            'itinerary' => null,
            'includes' => null,
            'excludes' => null,
        ]);

        $content = $this->serviceContent($service);

        $this->assertStringContainsString('Trip Overview', $content);
        $this->assertStringContainsString('Book Now', $content);
        $this->assertStringNotContainsString('Detail Itinerary', $content);
        $this->assertStringNotContainsString('>Includes<', $content);
        $this->assertStringNotContainsString('>Excludes<', $content);
    }

    public function test_service_without_content_shows_fallback_text(): void
    {
        $service = Service::factory()->create([
            'content' => null,
            'itinerary' => null,
            'includes' => null,
            'excludes' => null,
        ]);

        $content = $this->serviceContent($service);

        $this->assertStringContainsString('Detailed information is being finalized', $content);
    }

    public function test_book_now_tab_renders_inline_booking_form(): void
    {
        $service = Service::factory()->create(['title' => 'Mountain Flight']);

        $content = $this->serviceContent($service);

        $this->assertStringContainsString('Book your trip', $content);
        $this->assertStringContainsString('action="'.route('booking.store').'"', $content);
        $this->assertStringContainsString('value="Mountain Flight" readonly', $content);
        $this->assertStringContainsString('name="country"', $content);
    }
}
