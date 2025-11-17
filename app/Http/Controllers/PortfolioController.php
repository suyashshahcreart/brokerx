<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::with(['booking', 'creator'])
            ->where('created_by', Auth::id())
            ->latest()
            ->paginate(12);

        return view('frontend.portfolios.index', compact('portfolios'));
    }

    public function create()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->orderBy('booking_date', 'desc')
            ->get(['id', 'property_type_id', 'property_sub_type_id', 'booking_date']);

        return view('frontend.portfolios.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'link' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['created_by'] = Auth::id();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('portfolio_photos', 'public');
        }

        Portfolio::create($validated);

        return redirect()->route('portfolios.index')->with('success', 'Portfolio created successfully.');
    }

    public function show(Portfolio $portfolio)
    {
        // Check if user owns this portfolio
        if ($portfolio->created_by !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $portfolio->load(['booking', 'creator', 'updater']);
        return view('frontend.portfolios.show', compact('portfolio'));
    }

    public function edit(Portfolio $portfolio)
    {
        // Check if user owns this portfolio
        if ($portfolio->created_by !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $bookings = Booking::where('user_id', Auth::id())
            ->orderBy('booking_date', 'desc')
            ->get(['id', 'property_type_id', 'property_sub_type_id', 'booking_date']);

        return view('frontend.portfolios.edit', compact('portfolio', 'bookings'));
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        // Check if user owns this portfolio
        if ($portfolio->created_by !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'link' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['updated_by'] = Auth::id();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('portfolio_photos', 'public');
        }

        $portfolio->update($validated);

        return redirect()->route('portfolios.index')->with('success', 'Portfolio updated successfully.');
    }

    public function destroy(Portfolio $portfolio)
    {
        // Check if user owns this portfolio
        if ($portfolio->created_by !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $portfolio->delete();
        return redirect()->route('portfolios.index')->with('success', 'Portfolio deleted successfully.');
    }
}
