<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        // If OTP code provided, verify and log in without password
        if ($request->filled('otp_code')) {
            $identifier = (string) $request->input('email'); // identifier can be email or mobile
            $code = (string) $request->input('otp_code');

            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            if ($isEmail) {
                $user = User::where('email', $identifier)->first();
                $cacheKey = "otp:email:{$identifier}";
            } else {
                $user = User::where('mobile', $identifier)->first();
                $cacheKey = "otp:mobile:{$identifier}";
            }

            $stored = $cacheKey ? Cache::get($cacheKey) : null;
            if ($user && $stored && (string)$stored === $code) {
                // consume OTP and log in
                Cache::forget($cacheKey);
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended(RouteServiceProvider::HOME);
            }

            return back()->withErrors(['email' => 'Invalid or expired OTP.'])->withInput();
        }

        // Default password-based authentication
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/auth/logout');
    }
}
