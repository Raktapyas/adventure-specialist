<?php

namespace App\Http\Controllers\Concerns;

use App\Services\UrlHistoryService;
use Symfony\Component\HttpFoundation\Response;

trait RedirectsFromUrlHistory
{
    /**
     * Answer a canonical 301 to the current URL of the resource that owned the
     * requested path before an admin rename, or a 404 when no such history
     * exists (or its owner has been deleted).
     */
    protected function redirectFromHistory(): Response
    {
        $target = app(UrlHistoryService::class)->targetFor(request()->getPathInfo());

        if (! $target || ! method_exists($target, 'getPath')) {
            abort(404);
        }

        return redirect()->away($target->getPath(), 301);
    }
}
