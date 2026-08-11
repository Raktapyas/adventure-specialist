<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesKernelRequests;
use Tests\TestCase;

class CanonicalUrlBaselineTest extends TestCase
{
    use MakesKernelRequests;
    use RefreshDatabase;

    public function test_all_84_canonical_urls_return_200_after_seeding(): void
    {
        $this->seed();

        $fixture = $this->fixture();

        $this->assertCount(84, $fixture);

        foreach ($fixture as $url) {
            $this->assertSame(200, $this->finalStatus($url), "Expected {$url} to resolve to 200.");
        }

        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_fixture_matches_the_current_seeded_url_set(): void
    {
        $this->seed();

        $computed = Page::all()->map(fn (Page $p) => $p->getPath())
            ->merge(Service::all()->map(fn (Service $s) => $s->getPath()))
            ->merge(Destination::all()->map(fn (Destination $d) => $d->getPath()))
            ->merge(Package::all()->map(fn (Package $p) => $p->getPath()))
            ->merge(['/', '/gallery/', '/contact/', '/ast-services/', '/destination/', '/special-package/'])
            ->unique()
            ->sort()
            ->values()
            ->all();

        $fixture = $this->fixture();
        sort($fixture);

        $this->assertSame($computed, $fixture);
    }

    private function fixture(): array
    {
        return json_decode(
            file_get_contents(base_path('tests/fixtures/canonical-urls.json')),
            true
        );
    }
}
