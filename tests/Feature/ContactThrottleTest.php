<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_submits_successfully(): void
    {
        $this->post('/contact', [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'message' => 'Please send me the trekking brochure.',
        ])->assertRedirect();

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_successful_submission_redirects_to_the_canonical_contact_route_with_flash(): void
    {
        $this->from('/contact')
            ->post('/contact', [
                'name' => 'Jane Traveller',
                'email' => 'jane@example.com',
                'message' => 'Please send me the trekking brochure.',
            ])
            ->assertRedirect(route('contact.index'))
            ->assertSessionHas('success', 'Thank you for your message. We will get back to you as soon as possible.');
    }

    public function test_successful_submission_ignores_a_spoofed_referer(): void
    {
        $this->withHeader('Referer', 'https://evil.example.net/phish')
            ->post('/contact', [
                'name' => 'Jane Traveller',
                'email' => 'jane@example.com',
                'message' => 'Please send me the trekking brochure.',
            ])
            ->assertRedirect(route('contact.index'))
            ->assertSessionHas('success');
    }

    public function test_contact_form_throttles_after_six_attempts(): void
    {
        $payload = [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'message' => 'Please send me the trekking brochure.',
        ];

        for ($i = 0; $i < 6; $i++) {
            $this->post('/contact', $payload)->assertRedirect();
        }

        // The 7th attempt within the minute window is throttled.
        $this->post('/contact', $payload)->assertStatus(429);

        $this->assertSame(6, Inquiry::count());
    }
}
