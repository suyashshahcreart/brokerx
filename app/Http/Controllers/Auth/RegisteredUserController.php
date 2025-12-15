<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Broker;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
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

        // Use database transaction to ensure both user and broker are created together
        DB::beginTransaction();

        try {
            // Create user with verified mobile
            $user = User::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'mobile' => $mobile,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'mobile_verified_at' => now(),
            ]);
            // Create broker profile for the user
            $broker = Broker::create([
                'user_id' => $user->id,
                'phone_number' => $mobile,
                'license_number' => null,
                'status' => 'pending',
                'working_status' => false,
                'joined_at' => now(),
            ]);

            // Clear OTP verification cache
            Cache::forget($cacheKey);

            event(new Registered($user));

            Auth::login($user);

            DB::commit();

            // redirect to edit page with broker id
            return redirect()->route('broker.edit', $broker->id)
                ->with('success', 'Broker created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}