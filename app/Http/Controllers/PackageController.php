<?php

namespace App\Http\Controllers;

use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        return view('packages.index', [
            'packages' => Package::orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $package = Package::where('slug', $slug)->firstOrFail();

        $canonical = $package->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        return view('packages.show', [
            'package' => $package,
        ]);
    }
}
