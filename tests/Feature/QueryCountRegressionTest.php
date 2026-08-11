<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class QueryCountRegressionTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    /**
     * Seed a representative content graph so query patterns are exercised
     * realistically rather than against an empty database.
     */
    private function seedContent(): void
    {
        $about = Page::factory()->create(['slug' => 'about', 'title' => 'About']);
        Page::factory()->count(5)->create(['parent_id' => $about->id]);
        Page::factory()->count(5)->create(['is_published' => false]);

        $trekking = Service::factory()->create(['slug' => 'trekking', 'title' => 'Trekking']);
        Service::factory()->count(5)->create(['parent_id' => $trekking->id]);
        Service::factory()->count(3)->create(['is_published' => false]);

        $nepal = Destination::factory()->create(['slug' => 'nepal', 'title' => 'Nepal']);
        Destination::factory()->count(5)->create(['parent_id' => $nepal->id]);
        Destination::factory()->count(3)->create(['is_published' => false]);

        Package::factory()->count(6)->create();
        Package::factory()->count(2)->create(['is_published' => false]);
    }

    public function test_home_page_issues_fewer_than_25_queries(): void
    {
        $this->seedContent();

        DB::enableQueryLog();

        $this->assertSame(200, $this->send('/')['status']);

        $this->assertLessThan(25, count(DB::getQueryLog()), 'Home page exceeded 25 queries');
    }

    public function test_services_index_issues_fewer_than_25_queries(): void
    {
        $this->seedContent();

        DB::enableQueryLog();

        $this->assertSame(200, $this->send('/ast-services/')['status']);

        $this->assertLessThan(25, count(DB::getQueryLog()), 'Services index exceeded 25 queries');
    }

    public function test_destinations_index_issues_fewer_than_25_queries(): void
    {
        $this->seedContent();

        DB::enableQueryLog();

        $this->assertSame(200, $this->send('/destination/')['status']);

        $this->assertLessThan(25, count(DB::getQueryLog()), 'Destinations index exceeded 25 queries');
    }

    public function test_nav_and_content_are_deterministically_ordered(): void
    {
        Service::factory()->create(['slug' => 'alpha', 'title' => 'Alpha', 'sort_order' => 1]);
        Service::factory()->create(['slug' => 'beta', 'title' => 'Beta', 'sort_order' => 1]);

        $request = Request::create('/ast-services/', 'GET');
        $request->headers->set('host', 'localhost');
        $response = $this->app->handle($request);
        $content = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(
            strpos($content, 'Alpha') < strpos($content, 'Beta'),
            'Tied sort_order should fall back to a deterministic title tiebreaker'
        );
    }
}
