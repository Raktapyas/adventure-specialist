<?php

namespace Tests\Feature;

use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class DestinationShowTabsTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    /**
     * Render the canonical destination page through the HTTP kernel so the
     * trailing-slash URL is not trimmed by the test client.
     */
    private function destinationContent(Destination $destination): string
    {
        $request = Request::create($destination->getPath(), 'GET');
        $request->headers->set('host', 'localhost');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        return $response->getContent();
    }

    public function test_populated_destination_renders_all_tabs(): void
    {
        $destination = Destination::factory()->create([
            'content' => '<p>Overview body</p>',
            'itinerary' => '<p>Itinerary body</p>',
            'includes' => '<ul><li>Meals</li></ul>',
            'excludes' => '<ul><li>Flights</li></ul>',
        ]);

        $content = $this->destinationContent($destination);

        $this->assertStringContainsString('Trip Overview', $content);
        $this->assertStringContainsString('Detail Itinerary', $content);
        $this->assertStringContainsString('Includes', $content);
        $this->assertStringContainsString('Excludes', $content);
        $this->assertStringContainsString('Book Now', $content);
        $this->assertStringContainsString('Overview body', $content);
        $this->assertStringContainsString('Itinerary body', $content);
        $this->assertStringContainsString('Meals', $content);
        $this->assertStringContainsString('Flights', $content);
        $this->assertStringContainsString('action="'.route('booking.store').'"', $content);
    }

    public function test_empty_tabs_are_hidden_from_the_tab_bar(): void
    {
        $destination = Destination::factory()->create([
            'content' => '<p>Overview body</p>',
            'itinerary' => null,
            'includes' => null,
            'excludes' => null,
        ]);

        $content = $this->destinationContent($destination);

        $this->assertStringContainsString('Trip Overview', $content);
        $this->assertStringContainsString('Book Now', $content);
        $this->assertStringNotContainsString('Detail Itinerary', $content);
        $this->assertStringNotContainsString('>Includes<', $content);
        $this->assertStringNotContainsString('>Excludes<', $content);
    }

    public function test_destination_without_content_shows_fallback_text(): void
    {
        $destination = Destination::factory()->create([
            'content' => null,
            'itinerary' => null,
            'includes' => null,
            'excludes' => null,
        ]);

        $content = $this->destinationContent($destination);

        $this->assertStringContainsString('Detailed information is being finalized', $content);
    }

    public function test_book_now_tab_renders_inline_booking_form(): void
    {
        $destination = Destination::factory()->create(['title' => 'Gokyo Ri Trek']);

        $content = $this->destinationContent($destination);

        $this->assertStringContainsString('Book your trip', $content);
        $this->assertStringContainsString('action="'.route('booking.store').'"', $content);
        $this->assertStringContainsString('value="Gokyo Ri Trek" readonly', $content);
        $this->assertStringContainsString('name="country"', $content);
    }
}
