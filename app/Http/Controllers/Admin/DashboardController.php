<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function __invoke()
    {
        return view('admin.dashboard', [
            'counts' => [
                'pages' => Page::count(),
                'services' => Service::count(),
                'destinations' => Destination::count(),
                'packages' => Package::count(),
                'gallery' => GalleryImage::count(),
                'inquiries' => Inquiry::count(),
            ],
            'recentInquiries' => Inquiry::latest()->limit(5)->get(),
        ]);
    }
}
