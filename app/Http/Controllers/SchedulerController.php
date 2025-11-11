<?php

namespace App\Http\Controllers;

use App\Models\Scheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SchedulerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedulers = Scheduler::orderBy('created_at', 'desc')->paginate(20);
        return view('sheduler.list', compact('schedulers'));
    }

    /**
     * Show registration form for schedulers.
     */
    public function showRegister()
    {
        return view('sheduler.register');
    }

    /**
     * Handle scheduler registration (mobile-focused).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:schedulers,email',
            'mobile' => 'required|string|max:50|unique:schedulers,mobile',
        ]);

        $scheduler = Scheduler::create($data);

        // Log in via session (simple mobile-based session auth)
        Session::put('scheduler_id', $scheduler->id);

        return redirect()->route('schedulers.index')->with('success', 'Registered and logged in.');
    }

    /**
     * Show login form (mobile only).
     */
    public function showLogin()
    {
        return view('sheduler.login');
    }

    /**
     * Attempt login by mobile number.
     */
    public function login(Request $request)
    {
        // Backwards compatible: allow direct mobile login if no otp_code provided
        $data = $request->validate([
            'mobile' => 'required|string|max:50',
            'otp_code' => 'nullable|digits:6',
        ]);

        if (isset($data['otp_code']) && $data['otp_code']) {
            // verify OTP via same logic as verifyOtp
            $key = 'scheduler:otp:' . md5($data['mobile']);
            $stored = Cache::get($key);
            if (!$stored || (string) $stored !== (string) $data['otp_code']) {
                return back()->withErrors(['otp_code' => 'Invalid or expired code'])->withInput();
            }
            Cache::forget($key);

            $scheduler = Scheduler::firstOrCreate(
                ['mobile' => $data['mobile']],
                ['firstname' => $request->input('firstname', 'Scheduler'), 'email' => $request->input('email')]
            );
            $scheduler->mobile_verified_at = now();
            $scheduler->save();

            Session::put('scheduler_id', $scheduler->id);
            return redirect()->route('schedulers.index')->with('success', 'Logged in successfully.');
        }

        // If no otp_code, fallback to simple mobile lookup
        $scheduler = Scheduler::where('mobile', $data['mobile'])->first();

        if (! $scheduler) {
            return back()->withErrors(['mobile' => 'No scheduler found with this mobile number'])->withInput();
        }

        Session::put('scheduler_id', $scheduler->id);

        return redirect()->route('schedulers.index')->with('success', 'Logged in successfully.');
    }

    /**
     * Send OTP to given mobile (public for schedulers).
     */
    public function sendOtp(Request $request)
    {
        // Resolve identifier: prefer explicit identifier, then mobile, then email
        $input = trim((string) $request->input('identifier', $request->input('mobile', $request->input('email'))));

        if ($input === '') {
            return response()->json(['ok' => false, 'message' => 'No identifier provided'], 422);
        }

        // If it's an email
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $request->merge(['email' => $input]);
            $request->validate(['email' => ['required', 'email']]);

            $code = random_int(100000, 999999);
            $cacheKey = "otp:email:{$input}";
            Cache::put($cacheKey, (string)$code, now()->addMinutes(5));

            // send email (best-effort)
            try {
                Mail::raw("Your verification code is: {$code}", function ($message) use ($input) {
                    $message->to($input)->subject('Your verification code');
                });
            } catch (\Exception $ex) {
                Log::error('Failed to send scheduler OTP email: ' . $ex->getMessage());
            }

            Log::info("Scheduler OTP for email {$input}: {$code}");
            return response()->json(['ok' => true, 'message' => 'OTP sent to email', 'ttl' => 300]);
        }

        // Otherwise treat as mobile
        $mobile = $input;
        $request->merge(['mobile' => $mobile]);
        $request->validate(['mobile' => ['required', 'regex:/^\+?\d{8,20}$/']]);

        $code = random_int(100000, 999999);
        $cacheKey = "otp:mobile:{$mobile}";
        Cache::put($cacheKey, (string)$code, now()->addMinutes(5));

        // Replace this with real SMS gateway in production
        Log::info("Scheduler OTP for mobile {$mobile}: {$code}");
        return response()->json(['ok' => true, 'message' => 'OTP sent to mobile', 'ttl' => 300]);
    }

    /**
     * Verify OTP and login (or create) scheduler.
     */
    public function verifyOtp(Request $request)
    {
        // Accept either email or mobile; support both "code" and "otp_code" param names
        $identifier = trim((string) $request->input('identifier', $request->input('mobile', $request->input('email', ''))));
        $code = $request->input('code', $request->input('otp_code'));
        $isJson = $request->boolean('for_registration') || $request->ajax() || $request->wantsJson();

        if ($identifier === '') {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'No identifier provided'], 422);
            }
            return back()->withErrors(['identifier' => 'No identifier provided'])->withInput();
        }

        // Determine if identifier is an email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $v = $request->validate([
                'identifier' => ['required', 'email'],
                'code' => ['nullable', 'digits:6'],
                'otp_code' => ['nullable', 'digits:6'],
            ]);

            if (! $code) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Code required'], 422);
                }
                return back()->withErrors(['code' => 'Code required'])->withInput();
            }

            $cacheKey = "otp:email:{$identifier}";
            $stored = Cache::get($cacheKey);

            if (! $stored) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Code expired. Please request a new one.'], 422);
                }
                return back()->withErrors(['code' => 'Code expired. Please request a new one.'])->withInput();
            }

            if ((string)$stored !== (string)$code) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
                }
                return back()->withErrors(['code' => 'Invalid code'])->withInput();
            }

            Cache::forget($cacheKey);

            if ($isJson) {
                return response()->json(['ok' => true, 'message' => 'Email verified']);
            }

            // create or login scheduler by email
            $scheduler = Scheduler::firstOrCreate(
                ['email' => $identifier],
                ['firstname' => $request->input('firstname', 'Scheduler'), 'mobile' => $request->input('mobile')]
            );
            $scheduler->email_verified_at = now();
            $scheduler->save();

            Session::put('scheduler_id', $scheduler->id);
            return redirect()->route('schedulers.index')->with('success', 'Logged in successfully via OTP.');
        }

        // Otherwise treat as mobile
        $mobile = $identifier;
        $v = $request->validate([
            'identifier' => ['required', 'regex:/^\+?\d{8,20}$/'],
            'code' => ['nullable', 'digits:6'],
            'otp_code' => ['nullable', 'digits:6'],
        ]);

        if (! $code) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Code required'], 422);
            }
            return back()->withErrors(['code' => 'Code required'])->withInput();
        }

        $cacheKey = "otp:mobile:{$mobile}";
        $stored = Cache::get($cacheKey);

        if (! $stored) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Code expired. Please request a new one.'], 422);
            }
            return back()->withErrors(['code' => 'Code expired. Please request a new one.'])->withInput();
        }

        if ((string)$stored !== (string)$code) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
            }
            return back()->withErrors(['code' => 'Invalid code'])->withInput();
        }

        Cache::forget($cacheKey);

        if ($isJson) {
            return response()->json(['ok' => true, 'message' => 'Mobile verified']);
        }

        // create or login scheduler by mobile
        $scheduler = Scheduler::firstOrCreate(
            ['mobile' => $mobile],
            ['firstname' => $request->input('firstname', 'Scheduler'), 'email' => $request->input('email')]
        );
        $scheduler->mobile_verified_at = now();
        $scheduler->save();

        Session::put('scheduler_id', $scheduler->id);
        return redirect()->route('schedulers.index')->with('success', 'Logged in successfully via OTP.');
    }

    /**
     * Logout scheduler (clear session key).
     */
    public function logout()
    {
        Session::forget('scheduler_id');
        return redirect()->route('schedulers.login')->with('success', 'Logged out.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sheduler.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:schedulers,email',
            'mobile' => 'required|string|max:50',
        ]);

        $scheduler = Scheduler::create($data);

        return redirect()->route('schedulers.index')->with('success', 'Scheduler created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Scheduler $scheduler)
    {
        return view('sheduler.details', compact('scheduler'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scheduler $scheduler)
    {
        return view('sheduler.add', compact('scheduler'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scheduler $scheduler)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:schedulers,email,' . $scheduler->id,
            'mobile' => 'required|string|max:50',
        ]);

        $scheduler->update($data);

        return redirect()->route('schedulers.index')->with('success', 'Scheduler updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scheduler $scheduler)
    {
        $scheduler->delete();
        return redirect()->route('schedulers.index')->with('success', 'Scheduler deleted.');
    }
}
