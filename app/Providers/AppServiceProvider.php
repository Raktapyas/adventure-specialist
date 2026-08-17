<?php

namespace App\Providers;

use App\View\Composers\NavComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One NavComposer instance per request so the nav queries run once
        // even when the composer fires for several of the registered views.
        $this->app->singleton(NavComposer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(NavComposer::VIEWS, NavComposer::class);

        // Only the master admin (user ID 1) gets a full Gate bypass.
        // Every other user must strictly obey Filament Shield permissions.
        Gate::before(function ($user) {
            if ($user === null) {
                return null;
            }

            return $user->getKey() === 1 ? true : null;
        });
    }
}
