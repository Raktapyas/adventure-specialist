<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.inquiries.index', [
            'inquiries' => Inquiry::latest()->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')
            ->with('status', 'Inquiry deleted.');
    }
}
