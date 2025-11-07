<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class BrokerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Brockers = Broker::all();
        return View('borkers.list',compact( 'Brockers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user already has a broker profile
        if (Auth::user()->broker) {
            return redirect()->route('root')->with('info', 'You already have a broker account.');
        }
        return view('broker.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'license_number' => 'required|unique:brokers',
            'company_name' => 'nullable|string|max:255',
            'position_title' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pin_code' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'years_of_experience' => 'nullable|integer|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'bio' => 'nullable|string|max:1000',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
        ]);

        $user = Auth::user();

        // Prevent duplicate creation
        if ($user->broker) {
            return redirect()->route('dashboard')->with('info', 'You already have a broker profile.');
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('brokers/profiles', 'public');
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('brokers/covers', 'public');
        }

        // Set default commission rate
        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        $user->broker()->create($validated);

        return redirect()->route('dashboard')->with('success', 'Broker account created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Broker $broker)
    {
        $broker->load('user');
        return view('brokers.show', compact('broker'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Broker $broker)
    {
        // Authorization: only the broker owner can edit
        if (Auth::id() !== $broker->user_id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        return view('brokers.edit', compact('broker'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Broker $broker)
    {
        // Authorization: only the broker owner can update
        if (Auth::id() !== $broker->user_id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'license_number' => 'required|unique:brokers,license_number,' . $broker->id,
            'company_name' => 'nullable|string|max:255',
            'position_title' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pin_code' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'years_of_experience' => 'nullable|integer|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'bio' => 'nullable|string|max:1000',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
            'working_status' => 'nullable|boolean',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($broker->profile_image && \Storage::disk('public')->exists($broker->profile_image)) {
                \Storage::disk('public')->delete($broker->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('brokers/profiles', 'public');
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($broker->cover_image && \Storage::disk('public')->exists($broker->cover_image)) {
                \Storage::disk('public')->delete($broker->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('brokers/covers', 'public');
        }

        $broker->update($validated);

        return redirect()->route('broker.show', $broker)->with('success', 'Broker profile updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Broker $broker)
    {
        // Authorization: only the broker owner or admin can delete
        if (Auth::id() !== $broker->user_id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Delete associated images
        if ($broker->profile_image && \Storage::disk('public')->exists($broker->profile_image)) {
            \Storage::disk('public')->delete($broker->profile_image);
        }

        if ($broker->cover_image && \Storage::disk('public')->exists($broker->cover_image)) {
            \Storage::disk('public')->delete($broker->cover_image);
        }

        $broker->delete();

        return redirect()->route('dashboard')->with('success', 'Broker account deleted successfully!');
    }
}
