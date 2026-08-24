<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\Package;
use App\Models\Service;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function __invoke()
    {
        $settings = SiteSetting::current();

        return view('pages.home', [
            'heroSlides' => HeroSlide::published()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (HeroSlide $slide): array => $slide->toSlide())
                ->values()
                ->all(),
            'services' => Service::published()->whereNull('parent_id')->orderBy('sort_order')->orderBy('title')->get(),
            'destinations' => Destination::published()->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->published()])
                ->orderBy('sort_order')->orderBy('title')->get(),
            'packages' => Package::published()->orderBy('sort_order')->orderBy('title')->limit(4)->get(),
            'galleryImages' => GalleryImage::orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'stats' => $settings->statsRows(),
            'cta' => $settings->ctaBlock(),
        ]);
    }
}
