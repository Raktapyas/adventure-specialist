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
                ['value' => 2013, 'suffix' => '', 'label' => 'Established Year'],
                ['value' => 1200, 'suffix' => '+', 'label' => 'Total Trekking'],
                ['value' => 1600, 'suffix' => '+', 'label' => 'Happy Trekkers'],
            ],
        ]);
    }
}
