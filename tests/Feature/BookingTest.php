<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'trip' => 'Gokyo Trek',
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'country' => 'Japan',
            'subject' => '12 Days',
            'message' => 'Two travellers.',
        ], $overrides);
    }

    public function test_booking_form_submits_successfully(): void
    {
        $this->post('/booking', $this->payload())->assertRedirect();

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'subject' => 'Booking: Gokyo Trek — 12 Days',
            'message' => "Country: Japan\n\nTwo travellers.",
        ]);
    }

    public function test_message_is_optional(): void
    {
        $this->post('/booking', $this->payload(['message' => null]))->assertRedirect();

        $this->assertDatabaseHas('inquiries', [
            'subject' => 'Booking: Gokyo Trek — 12 Days',
            'message' => 'Country: Japan',
        ]);
    }

    public function test_successful_submission_redirects_back_to_the_trip_page_with_submitted_flag(): void
    {
        // The redirect Location drops the trailing slash (same as the contact
        // form); the canonical middleware then 301s the browser to the slash
        // URL, preserving the query flag.
        $this->from('/destination/gokyo-trek/')
            ->post('/booking', $this->payload())
            ->assertRedirect('http://localhost/destination/gokyo-trek?submitted=1');
    }

    public function test_validation_failure_creates_no_inquiry(): void
    {
        $this->post('/booking', $this->payload(['country' => null]))
            ->assertRedirect()
            ->assertSessionHasErrors(['country']);

        $this->post('/booking', $this->payload(['trip' => null]))
            ->assertRedirect()
            ->assertSessionHasErrors(['trip']);

        $this->assertSame(0, Inquiry::count());
    }

    public function test_booking_form_throttles_after_six_attempts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/booking', $this->payload())->assertRedirect();
        }

        // The 7th attempt within the minute window is throttled.
        $this->post('/booking', $this->payload())->assertStatus(429);

        $this->assertSame(6, Inquiry::count());
    }
}
