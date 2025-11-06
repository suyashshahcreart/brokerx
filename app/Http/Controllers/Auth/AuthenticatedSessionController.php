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

        // If frontend indicated OTP verification, allow passwordless sign in
        if ($request->input('otp_verified') == '1') {
            $identifier = (string) $request->input('email');
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            if ($isEmail) {
                $user = User::where('email', $identifier)->first();
                $cacheKey = "otp:verified:email:{$identifier}";
            } else {
                $user = User::where('mobile', $identifier)->first();
                $cacheKey = "otp:verified:mobile:{$identifier}";
            }

            if ($user && Cache::get($cacheKey)) {
                // consume the verified flag and log the user in
                Cache::forget($cacheKey);
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended(RouteServiceProvider::HOME);
            }

            return redirect()->back()->withErrors(['email' => 'OTP not verified or expired.']);
        }

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
