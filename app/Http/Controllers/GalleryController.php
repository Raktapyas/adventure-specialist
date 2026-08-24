<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;

class GalleryController extends Controller
{
    public function __invoke()
    {
        return view('pages.gallery', [
            'images' => GalleryImage::orderBy('sort_order')->orderBy('id')->limit(15)->get(),
        ]);
    }
}
