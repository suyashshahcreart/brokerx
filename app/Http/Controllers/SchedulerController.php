<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Scheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SchedulerController extends Controller
{

    // HELPER MENTHODS
    /**
     * Get hex color from Bootstrap class
     */
    private function getColorFromClass($class)
    {
        return match ($class) {
            'bg-primary' => '#3b76e1',
            'bg-success' => '#22c55e',
            'bg-info' => '#0dcaf0',
            'bg-danger' => '#ef4444',
            default => '#6c757d'
        };
    }

    /**
     * Get appointments as JSON for calendar
     */
    public function getAppointmentsJson()
    {
        $schedulerId = Session::get('scheduler_id');

        if (!$schedulerId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $appointments = Appointment::all();

        // Format appointments for FullCalendar
        $events = $appointments->map(function ($appointment) {
            // Determine background color based on status
            $bgClass = match ($appointment->status) {
                'pending' => 'bg-primary',
                'confirmed' => 'bg-success',
                'completed' => 'bg-info',
                'cancelled' => 'bg-danger',
                default => 'bg-secondary'
            };

            return [
                'id' => $appointment->id,
                'title' => $appointment->address . ', ' . $appointment->city,
                'start' => $appointment->start_time,
                'end' => $appointment->end_time,
                'date' => $appointment->date,
                'classNames' => [$bgClass], // FullCalendar expects an array
                'backgroundColor' => $this->getColorFromClass($bgClass),
                'borderColor' => $this->getColorFromClass($bgClass),
                'extendedProps' => [
                    'status' => $appointment->status,
                    'address' => $appointment->address,
                    'city' => $appointment->city,
                    'state' => $appointment->state,
                    'country' => $appointment->country,
                    'pin_code' => $appointment->pin_code,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get logged-in scheduler from session
        $schedulerId = Session::get('scheduler_id');
        $loggedInScheduler = null;

        if ($schedulerId) {
            $loggedInScheduler = Scheduler::find($schedulerId);
        }

        // If no scheduler is logged in, redirect to login
        if (!$loggedInScheduler) {
            return redirect()->route('schedulers.login')->with('error', 'Please login first.');
        }

        $schedulers = Scheduler::orderBy('created_at', 'desc')->paginate(20);
        return view('sheduler.index', compact('schedulers', 'loggedInScheduler'));
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
            'mobile' => 'nullable|string|max:50|unique:schedulers,mobile',
        ]);

        // require at least one contact
        if (empty($data['email']) && empty($data['mobile'])) {
            return back()->withErrors(['mobile' => 'Please provide a mobile number or email and verify it before registering.'])->withInput();
        }

        // Check verification marker in cache (set by verifyOtp when called for registration)
        $verified = false;
        $verifiedKind = $request->input('verified_kind');
        $verifiedValue = $request->input('verified_value');

        if ($request->boolean('verified') && $verifiedValue) {
            $regKey = 'otp:verified:registration:' . md5($verifiedValue);
            if (Cache::pull($regKey)) {
                $verified = true;
            }
        }

        // Fallback: try to find any verification for provided contacts
        if (!$verified) {
            if (!empty($data['email'])) {
                $regKey = 'otp:verified:registration:' . md5($data['email']);
                if (Cache::pull($regKey)) {
                    $verified = true;
                    $verifiedKind = 'email';
                    $verifiedValue = $data['email'];
                }
            }
        }
        if (!$verified) {
            if (!empty($data['mobile'])) {
                $regKey = 'otp:verified:registration:' . md5($data['mobile']);
                if (Cache::pull($regKey)) {
                    $verified = true;
                    $verifiedKind = 'mobile';
                    $verifiedValue = $data['mobile'];
                }
            }
        }

        if (!$verified) {
            return back()->withErrors(['mobile' => 'Contact not verified or verification expired. Please verify and try again.'])->withInput();
        }

        $scheduler = Scheduler::create($data);

        // set verified timestamp
        if ($verifiedKind === 'email' || (!empty($data['email']) && $verifiedValue === $data['email'])) {
            $scheduler->email_verified_at = now();
        }
        if ($verifiedKind === 'mobile' || (!empty($data['mobile']) && $verifiedValue === $data['mobile'])) {
            $scheduler->mobile_verified_at = now();
        }
        $scheduler->save();

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

        if (!$scheduler) {
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
            Cache::put($cacheKey, (string) $code, now()->addMinutes(5));

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
        Cache::put($cacheKey, (string) $code, now()->addMinutes(5));

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

            if (!$code) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Code required'], 422);
                }
                return back()->withErrors(['code' => 'Code required'])->withInput();
            }

            $cacheKey = "otp:email:{$identifier}";
            $stored = Cache::get($cacheKey);

            if (!$stored) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Code expired. Please request a new one.'], 422);
                }
                return back()->withErrors(['code' => 'Code expired. Please request a new one.'])->withInput();
            }

            if ((string) $stored !== (string) $code) {
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
                }
                return back()->withErrors(['code' => 'Invalid code'])->withInput();
            }

            Cache::forget($cacheKey);

            if ($isJson) {
                // mark the identifier as verified for registration (short TTL)
                $regKey = "otp:verified:registration:" . md5($identifier);
                Cache::put($regKey, true, now()->addSeconds(600));
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

        if (!$code) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Code required'], 422);
            }
            return back()->withErrors(['code' => 'Code required'])->withInput();
        }

        $cacheKey = "otp:mobile:{$mobile}";
        $stored = Cache::get($cacheKey);

        if (!$stored) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Code expired. Please request a new one.'], 422);
            }
            return back()->withErrors(['code' => 'Code expired. Please request a new one.'])->withInput();
        }

        if ((string) $stored !== (string) $code) {
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
            }
            return back()->withErrors(['code' => 'Invalid code'])->withInput();
        }

        Cache::forget($cacheKey);

        if ($isJson) {
            // mark the identifier as verified for registration (short TTL)
            $regKey = "otp:verified:registration:" . md5($mobile);
            Cache::put($regKey, true, now()->addSeconds(600));
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
        // Get appointments with related data
        $appointments = Appointment::where('scheduler_id', $scheduler->id)
            ->with(['assignedTo', 'assignedBy', 'completedBy'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
            
        // Calculate statistics
        $totalAppointments = $appointments->count();
        $pendingAppointments = $appointments->where('status', 'pending')->count();
        $confirmedAppointments = $appointments->where('status', 'confirmed')->count();
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
        
        return view('sheduler.details', compact(
            'scheduler',
            'appointments',
            'totalAppointments',
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments'
        ));
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

    /**
     * Update appointment (scheduler can only update time and location)
     */
    public function updateAppointment(Request $request, $id)
    {
        $schedulerId = Session::get('scheduler_id');

        if (!$schedulerId) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        // Find appointment
        $appointment = Appointment::where('id', $id)
            ->where('scheduler_id', $schedulerId)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found or you do not have permission to update it'
            ], 404);
        }

        // Validate request - only time and location fields
        $validated = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
        ]);

        // Update appointment - only time and location
        $appointment->update([
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'country' => $validated['country'],
            'pin_code' => $validated['pin_code'],
            'updated_by' => $schedulerId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment time and location updated successfully',
            'appointment' => $appointment
        ]);
    }
}
