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
            'services' => Service::whereNull('parent_id')->orderBy('sort_order')->get(),
            'destinations' => Destination::whereNull('parent_id')->orderBy('sort_order')->get(),
            'packages' => Package::orderBy('sort_order')->limit(4)->get(),
            'galleryImages' => GalleryImage::orderBy('sort_order')->limit(6)->get(),
            'stats' => [
                ['value' => 5, 'suffix' => '', 'label' => 'Countries served'],
                ['value' => Service::count(), 'suffix' => '+', 'label' => 'Adventure services'],
                ['value' => Destination::count(), 'suffix' => '+', 'label' => 'Destinations & programs'],
                ['value' => Package::count(), 'suffix' => '', 'label' => 'Signature packages'],
            ],
        ]);
    }
}
