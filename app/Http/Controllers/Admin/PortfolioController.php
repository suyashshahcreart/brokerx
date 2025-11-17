<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::with(['creator', 'updater', 'booking'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.portfolios.index', compact('portfolios'));
    }

    public function create()
    {
        $bookings = \App\Models\Booking::with(['user'])
            ->latest('booking_date')
            ->get(['id', 'user_id', 'booking_date']);

        return view('admin.portfolios.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $validated['created_by'] = $request->user()->id ?? null;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('portfolio_photos', 'public');
        }

        Portfolio::create($validated);

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio created successfully.');
    }

    public function show(Portfolio $portfolio)
    {
        $portfolio->load(['creator', 'updater', 'booking']);
        return view('admin.portfolios.show', compact('portfolio'));
    }

    public function edit(Portfolio $portfolio)
    {
        $bookings = \App\Models\Booking::with(['user'])
            ->latest('booking_date')
            ->get(['id', 'user_id', 'booking_date']);

        return view('admin.portfolios.edit', compact('portfolio', 'bookings'));
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $validated['updated_by'] = $request->user()->id ?? null;

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($portfolio->photo && \Storage::disk('public')->exists($portfolio->photo)) {
                \Storage::disk('public')->delete($portfolio->photo);
            }
            $validated['photo'] = $request->file('photo')->store('portfolio_photos', 'public');
        }

        $portfolio->update($validated);

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio updated successfully.');
    }

    public function destroy(Portfolio $portfolio)
    {
        $portfolio->delete();
        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio deleted successfully.');
    }
}
