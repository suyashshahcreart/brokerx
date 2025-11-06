<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.signup');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'min:8', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $mobile = $validated['mobile'];
        $isMobileVerified = session('mobile_verified:' . md5($mobile), false) === true;

        if (!$isMobileVerified) {
            return back()->withErrors(['mobile' => 'Please verify your mobile number before registering.'])->withInput();
        }

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $mobile,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'mobile_verified_at' => $isMobileVerified ? now() : null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Clear OTP verification session flag
        session()->forget('mobile_verified:' . md5($mobile));

        return redirect(RouteServiceProvider::HOME);
    }
}
