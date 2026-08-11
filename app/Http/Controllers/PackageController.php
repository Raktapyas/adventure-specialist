<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsFromUrlHistory;
use App\Models\Package;

class PackageController extends Controller
{
    use RedirectsFromUrlHistory;

    public function index()
    {
        return view('packages.index', [
            'packages' => Package::published()->orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $package = Package::published()->where('slug', $slug)->first();

        if (! $package) {
            return $this->redirectFromHistory();
        }

        $canonical = $package->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        return view('packages.show', [
            'package' => $package,
        ]);
    }
}
