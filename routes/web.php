<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

/*
|--------------------------------------------------------------------------
| Canonical routes (original site structure, trailing slash enforced)
|--------------------------------------------------------------------------
*/

Route::middleware('canonical')->group(function () {
    // About
    Route::get('/about-us', [PageController::class, 'show'])->defaults('slug', 'about')->name('pages.index');
    Route::get('/about-us/{slug}', [PageController::class, 'show'])->name('pages.show');

    // Services
    Route::get('/ast-services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/ast-services/{slug}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/ast-services/{parent}/{child}', [ServiceController::class, 'showByPath'])->name('services.show.nested');

    // Destinations (non-Nepal)
    Route::get('/destination', [DestinationController::class, 'index'])->name('destinations.index');
    Route::get('/destination/{slug}', [DestinationController::class, 'show'])->name('destinations.show');
    Route::get('/destination/{parent}/{child}', [DestinationController::class, 'showByPath'])->name('destinations.show.nested');
    Route::get('/destination/{parent}/{child}/{grandchild}', [DestinationController::class, 'showByPath'])->name('destinations.show.deep');

    // Nepal subtree (canonical root)
    Route::get('/nepal', [DestinationController::class, 'show'])->defaults('slug', 'nepal')->name('destinations.nepal');
    Route::get('/nepal/{child}', [DestinationController::class, 'showByPath'])->defaults('root', 'nepal')->name('destinations.nepal.child');
    Route::get('/nepal/{parent}/{child}', [DestinationController::class, 'showByPath'])->defaults('root', 'nepal')->name('destinations.nepal.nested');

    // Packages
    Route::get('/special-package', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/special-package/{slug}', [PackageController::class, 'show'])->name('packages.show');

    // Static
    Route::get('/gallery', GalleryController::class)->name('gallery');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
    Route::get('/contact/managing-director', [PageController::class, 'managingDirector'])->name('contact.managing-director');
});

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Legacy redirects (newer redesign URLs -> canonical original paths)
|--------------------------------------------------------------------------
*/

Route::get('/about', fn () => redirect()->away(Page::where('slug', 'about')->firstOrFail()->getPath(), 301));
Route::get('/about/{slug}', function (string $slug) {
    $page = Page::where('slug', $slug)->firstOrFail();

    return redirect()->away($page->getPath(), 301);
});

Route::get('/services', fn () => redirect()->away('/ast-services/', 301));
Route::get('/services/{path}', function (string $path) {
    $segments = explode('/', $path);
    $service = Service::resolvePath($segments)
        ?? Service::where('slug', last($segments))->first();

    abort_unless($service, 404);

    return redirect()->away($service->getPath(), 301);
})->where('path', '.*');

Route::get('/destinations', fn () => redirect()->away('/destination/', 301));
Route::get('/destinations/{path}', function (string $path) {
    $segments = explode('/', $path);
    $destination = Destination::resolvePath($segments)
        ?? Destination::where('slug', last($segments))->first();

    abort_unless($destination, 404);

    return redirect()->away($destination->getPath(), 301);
})->where('path', '.*');

Route::get('/packages', fn () => redirect()->away('/special-package/', 301));
Route::get('/packages/{slug}', function (string $slug) {
    $package = Package::where('slug', $slug)->firstOrFail();

    return redirect()->away($package->getPath(), 301);
});
