<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:portfolio_view')->only(['index', 'show']);
        $this->middleware('permission:portfolio_create')->only(['create', 'store']);
        $this->middleware('permission:portfolio_edit')->only(['edit', 'update']);
        $this->middleware('permission:portfolio_delete')->only(['destroy']);
    }
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

        $portfolio = Portfolio::create($validated);

        activity('portfolios')
            ->performedOn($portfolio)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $portfolio->toArray()
            ])
            ->log('Portfolio created');

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

        $before = $portfolio->getOriginal();
        $portfolio->update($validated);
        $after = $portfolio->toArray();
        $changes = [];
        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $changes[$key] = [
                    'old' => $before[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        activity('portfolios')
            ->performedOn($portfolio)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Portfolio updated');

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio updated successfully.');
    }

    public function destroy(Portfolio $portfolio)
    {
        $before = $portfolio->toArray();
        $portfolioId = $portfolio->id;
        $portfolioType = get_class($portfolio);
        $portfolio->delete();

        Activity::create([
            'log_name' => 'portfolios',
            'description' => 'Portfolio deleted',
            'subject_type' => $portfolioType,
            'subject_id' => $portfolioId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $portfolioId,
            ]
        ]);
        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio deleted successfully.');
    }
}
