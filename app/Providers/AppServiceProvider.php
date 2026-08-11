<?php

namespace App\Providers;

use App\View\Composers\NavComposer;
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
    }
}
