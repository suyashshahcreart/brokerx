<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user profile page
     */
    public function index()
    {
        $user = auth()->user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        // Auto-generate full name from first name and last name
        $validated['name'] = trim($validated['firstname'] . ' ' . $validated['lastname']);

        // Store old values for activity log
        $oldValues = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];

        $user->update($validated);

        // Log profile update activity
        activity('profile')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'event' => 'updated',
                'before' => $oldValues,
                'after' => [
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'mobile' => $validated['mobile'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ])
            ->log('Profile updated');

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show change password page
     */
    public function showChangePassword()
    {
        return view('admin.profile.change-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log password change activity
        activity('profile')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'event' => 'password_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('Password changed');

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password changed successfully.');
    }
}

