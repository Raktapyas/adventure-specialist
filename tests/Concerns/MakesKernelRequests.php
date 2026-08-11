<?php

namespace Tests\Concerns;

use Illuminate\Http\Request;

trait MakesKernelRequests
{
    /**
     * Send a GET request straight through the HTTP kernel, bypassing the test
     * client's trailing-slash trimming so canonical (trailing-slash) URLs behave
     * exactly like they do on the real server.
     *
     * @return array{path: string, status: int, location: ?string}
     */
    protected function send(string $uri): array
    {
        $request = Request::create($uri, 'GET');
        $request->headers->set('host', 'localhost');

        $response = $this->app->handle($request);

        return [
            'path' => $uri,
            'status' => $response->getStatusCode(),
            'location' => $response->headers->get('location'),
        ];
    }

    /**
     * Follow a redirect chain (up to $max hops, aborting on loops) and return
     * every hop as [path, status, location].
     *
     * @return array<int, array{path: string, status: int, location: ?string}>
     */
    protected function walk(string $uri, int $max = 8): array
    {
        $chain = [];
        $current = $uri;
        $seen = [];

        for ($i = 0; $i < $max; $i++) {
            $hop = $this->send($current);
            $chain[] = $hop;

            if (isset($seen[$current])
                || $hop['status'] < 300
                || $hop['status'] >= 400
                || $hop['location'] === null) {
                break;
            }

            $seen[$current] = true;
            $current = $this->pathOf($hop['location']);
        }

        return $chain;
    }

    protected function finalStatus(string $uri, int $max = 8): int
    {
        $chain = $this->walk($uri, $max);

        return end($chain)['status'];
    }

    protected function pathOf(string $location): string
    {
        $parts = parse_url($location);

        return $parts['path'] ?? '/';
    }
}
