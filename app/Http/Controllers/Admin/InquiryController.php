<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public const STATUSES = ['new', 'in_progress', 'resolved', 'archived'];

    /**
     * Display a paginated, filterable listing of inquiries.
     */
    public function index(Request $request): View
    {
        $query = Inquiry::query();

        if ($request->filled('status') && in_array($request->string('status')->toString(), self::STATUSES, true)) {
            $query->status($request->string('status')->toString());
        }

        if ($request->filled('read')) {
            $query->where('is_read', $request->boolean('read'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return view('admin.inquiries.index', [
            'inquiries' => $query->latest()->paginate(15)->withQueryString(),
            'statuses' => self::STATUSES,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'read' => $request->input('read'),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    /**
     * Display the specified inquiry and mark it as read.
     */
    public function show(Inquiry $inquiry): View
    {
        if (! $inquiry->is_read) {
            $inquiry->update(['is_read' => true]);
        }

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Toggle the read/unread state of an inquiry.
     */
    public function toggleRead(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->update(['is_read' => ! $inquiry->is_read]);

        return back()->with('status', 'Inquiry marked as '.($inquiry->is_read ? 'read' : 'unread').'.');
    }

    /**
     * Update the status of an inquiry.
     */
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return back()->with('status', 'Inquiry status updated to "'.$validated['status'].'".');
    }

    /**
     * Apply a bulk action to a set of inquiries.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:mark_read,mark_unread,delete,new,in_progress,resolved,archived'],
        ]);

        $inquiries = Inquiry::whereIn('id', $validated['ids'])->get();

        match ($validated['action']) {
            'mark_read' => $inquiries->each->update(['is_read' => true]),
            'mark_unread' => $inquiries->each->update(['is_read' => false]),
            'delete' => $inquiries->each->delete(),
            default => $inquiries->each->update(['status' => $validated['action']]),
        };

        $count = count($validated['ids']);

        return redirect()->route('admin.inquiries.index')
            ->with('status', "Updated {$count} inquiry".($count === 1 ? '' : 's').'.');
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
