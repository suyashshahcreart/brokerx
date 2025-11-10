<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Update the authenticated user's information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        // Check if mobile or email changed - if so, reset verification status
        if ($user->mobile !== $validated['mobile']) {
            $validated['mobile_verified_at'] = null;
        }

        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return redirect()->back()->with('user_success', 'Personal information updated successfully!');
    }
}
