<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrailingSlash
{
    /**
     * Redirect canonical application URLs to their trailing-slash form with a
     * single 301. The homepage, static assets, build files and the health
     * check route are left untouched.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! $request->isMethod('GET') || $path === '/' || str_ends_with($path, '/')) {
            return $next($request);
        }

        if ($this->isAsset($path)) {
            return $next($request);
        }

        $query = $request->getQueryString();

        return redirect()->away($path.'/'.($query ? '?'.$query : ''), 301);
    }

    protected function isAsset(string $path): bool
    {
        $fileExtensions = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'webp', 'svg', 'ico',
            'xml', 'txt', 'json', 'map', 'woff', 'woff2', 'ttf', 'eot', 'gif',
        ];

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext !== '' && in_array($ext, $fileExtensions, true)) {
            return true;
        }

        return str_starts_with($path, '/build/')
            || str_starts_with($path, '/storage/')
            || $path === '/up'
            || $path === '/favicon.ico';
    }
}
