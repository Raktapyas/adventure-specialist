<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsAuthenticatedUsers
{
    /**
     * Route non-admin users to the public site after login/registration.
     *
     * Admins keep the previous Breeze behaviour: honour the "intended" URL or
     * fall back to the dashboard. Non-admins only honour an intended URL when
     * it points at the public site; an intended admin path (e.g. a 403 for a
     * non-admin) is discarded so they always land somewhere safe.
     */
    protected function redirectUserAfterAuth(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user?->is_admin) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $intended = $request->session()->get('url.intended');
        $intendedPath = is_string($intended) ? parse_url($intended, PHP_URL_PATH) : null;

        if ($intendedPath !== null && ! str_starts_with($intendedPath, '/admin')) {
            return redirect()->intended(route('home', absolute: false));
        }

        return redirect(route('home', absolute: false));
    }
}
