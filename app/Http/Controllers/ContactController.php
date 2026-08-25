<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Inquiry::create($validated);

        // Laravel strips trailing slashes from generated URLs, so the browser
        // always bounces /contact -> /contact/ — which consumes a one-hop
        // session flash before the page renders. A query flag survives every
        // hop, so the pop-up triggers reliably.
        return redirect()->to('/contact/?submitted=1');
    }
}
