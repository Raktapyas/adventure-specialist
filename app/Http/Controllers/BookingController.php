<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => sprintf('Booking: %s — %s', $validated['trip'], $validated['subject']),
            'message' => sprintf('Country: %s%s', $validated['country'], filled($validated['message'] ?? null) ? "\n\n".$validated['message'] : ''),
        ]);

        // Return to the trip page the form was submitted from. The path keeps
        // its canonical trailing slash; a query flag survives the hop so the
        // success pop-up triggers reliably (same approach as the contact form).
        $previous = parse_url(url()->previous() ?? '', PHP_URL_PATH) ?: '/';

        return redirect()->to($previous.'?submitted=1');
    }
}
