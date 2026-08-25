<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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

    public function test_successful_submission_redirects_to_the_canonical_contact_route_with_submitted_flag(): void
    {
        $this->from('/contact')
            ->post('/contact', [
                'name' => 'Jane Traveller',
                'email' => 'jane@example.com',
                'message' => 'Please send me the trekking brochure.',
            ])
            ->assertRedirect('http://localhost/contact?submitted=1');
    }

    public function test_successful_submission_ignores_a_spoofed_referer(): void
    {
        $this->withHeader('Referer', 'https://evil.example.net/phish')
            ->post('/contact', [
                'name' => 'Jane Traveller',
                'email' => 'jane@example.com',
                'message' => 'Please send me the trekking brochure.',
            ])
            ->assertRedirect('http://localhost/contact?submitted=1');
    }

    public function test_successful_submission_shows_thank_you_popup(): void
    {
        $this->post('/contact', [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'message' => 'Please send me the trekking brochure.',
        ])->assertRedirect('http://localhost/contact?submitted=1');

        // Render the canonical contact page straight through the kernel with
        // the submitted flag (avoids the test client's trailing-slash
        // redirect loop).
        $request = Request::create('/contact?submitted=1', 'GET');

        $response = $this->app->handle($request);

        if ($response->isRedirect()) {
            $location = $response->headers->get('Location');
            $path = (parse_url($location, PHP_URL_PATH) ?: '/').'?'.(parse_url($location, PHP_URL_QUERY) ?? '');
            $response = $this->app->handle(Request::create($path, 'GET'));
        }

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('sweetalert2', $response->getContent());
        $this->assertStringContainsString('Thank you for reaching out.', $response->getContent());
    }

    public function test_contact_page_without_flag_shows_no_popup(): void
    {
        $request = Request::create('/contact/', 'GET');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString('Swal.fire', $response->getContent());
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
