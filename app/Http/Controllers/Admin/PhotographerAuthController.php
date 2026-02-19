<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PhotographerAuthController extends Controller
{
    /**
     * Display the photographer registration view.
     *
     * @return \Illuminate\View\View
     */
    public function createRegister()
    {
        return view('auth.photographer-signup');
    }

    /**
     * Handle an incoming photographer registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRegister(Request $request)
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'min:8', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        $mobile = $validated['mobile'];

        // Check if mobile OTP was verified (flag set by OTP verification endpoint)
        $cacheKey = "otp:verified:registration:mobile:{$mobile}";
        $isMobileVerified = Cache::get($cacheKey);

        if (!$isMobileVerified) {
            return back()->withErrors(['mobile' => 'Please verify your mobile number before registering.'])->withInput();
        }

        DB::beginTransaction();

        try {
            // Create user with verified mobile
            $user = User::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'mobile' => $mobile,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'mobile_verified_at' => now(),
            ]);

            // Assign photographer role
            $photographerRole = Role::firstOrCreate(['name' => 'photographer']);
            $user->assignRole($photographerRole);

            // Clear OTP verification cache
            Cache::forget($cacheKey);

            event(new Registered($user));

            Auth::login($user);

            DB::commit();

            return redirect()->route('photographer.jobs.index')
                ->with('success', 'Photographer account created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    /**
     * Display the photographer login view.
     *
     * @return \Illuminate\View\View
     */
    public function createLogin()
    {
        return view('auth.photographer-login');
    }

    /**
     * Handle an incoming photographer authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLogin(Request $request)
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
                // Verify user has photographer role
                if (!$user->hasRole('photographer')) {
                    return back()->withErrors(['email' => 'You are not authorized as a photographer.'])->withInput();
                }

                // consume OTP and log in
                Cache::forget($cacheKey);
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->route('photographer.jobs.index');
            }

            return back()->withErrors(['email' => 'Invalid or expired OTP.'])->withInput();
        }

        // Default password-based authentication
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $isEmail = filter_var($request->email, FILTER_VALIDATE_EMAIL);
        $user = $isEmail 
            ? User::where('email', $request->email)->first()
            : User::where('mobile', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->withInput();
        }

        // Verify user has photographer role
        if (!$user->hasRole('photographer')) {
            return back()->withErrors(['email' => 'You are not authorized as a photographer.'])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('photographer.jobs.index');
    }

    /**
     * Destroy an authenticated photographer session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.photographer.login');
    }
}
