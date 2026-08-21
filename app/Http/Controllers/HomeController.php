<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Package;
use App\Models\Service;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('pages.home', [
            'services' => Service::published()->whereNull('parent_id')->orderBy('sort_order')->orderBy('title')->get(),
            'destinations' => Destination::published()->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->published()])
                ->orderBy('sort_order')->orderBy('title')->get(),
            'packages' => Package::published()->orderBy('sort_order')->orderBy('title')->limit(4)->get(),
            'galleryImages' => GalleryImage::orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'stats' => [
                ['value' => 8000, 'suffix' => 'm+', 'label' => "Reach the world's highest peaks"],
                ['value' => 365, 'suffix' => ' DAYS', 'label' => 'Adventure in Nepal, all year round'],
                ['value' => 100, 'suffix' => '% LOCAL', 'label' => 'Authentic Himalayan experiences'],
                ['value' => 1, 'suffix' => ' LIFETIME', 'label' => "Memories you'll never forget"],
            ],
        ]);
    }
}
