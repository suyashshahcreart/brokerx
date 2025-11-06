<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    protected $ttlSeconds = 300; // 5 minutes

    public function sendMobile(Request $request)
    {
        $v = Validator::make($request->all(), [
            'mobile' => ['required', 'regex:/^\+?\d{8,20}$/'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
        }

        $mobile = $request->input('mobile');
        $code = random_int(100000, 999999);

        Cache::put("otp:mobile:{$mobile}", $code, $this->ttlSeconds);

        // Log the code so it can be used during development/testing
        Log::info("OTP sent to mobile {$mobile}: {$code}");

        // TODO: Integrate with SMS gateway here.

        return response()->json(['ok' => true, 'message' => 'OTP sent']);
    }

    public function verifyMobile(Request $request)
    {
        $v = Validator::make($request->all(), [
            'mobile' => ['required', 'regex:/^\+?\d{8,20}$/'],
            'code' => ['required', 'digits:6'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
        }

        $mobile = $request->input('mobile');
        $code = $request->input('code');

        $cacheKey = "otp:mobile:{$mobile}";
        $stored = Cache::get($cacheKey);
        if (!$stored) {
            return response()->json(['ok' => false, 'message' => 'OTP expired or not found'], 422);
        }

        if ((string)$stored !== (string)$code) {
            return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
        }

    // on success remove the OTP and set a short-lived verified flag
    Cache::forget($cacheKey);
    Cache::put("otp:verified:mobile:{$mobile}", true, 300);

    return response()->json(['ok' => true, 'message' => 'OTP verified']);
    }

    public function sendEmail(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
        }

        $email = $request->input('email');
        $code = random_int(100000, 999999);

        Cache::put("otp:email:{$email}", $code, $this->ttlSeconds);

        // Try to send email; if mail isn't configured this will be logged.
        try {
            Mail::raw("Your verification code is: {$code}", function ($message) use ($email) {
                $message->to($email)->subject('Your verification code');
            });
        } catch (\Exception $ex) {
            Log::error('Failed to send OTP email: ' . $ex->getMessage());
            // still return ok so devs can use log to get the code; adjust as necessary
        }

        Log::info("OTP sent to email {$email}: {$code}");

        return response()->json(['ok' => true, 'message' => 'OTP sent']);
    }

    public function verifyEmail(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
        }

        $email = $request->input('email');
        $code = $request->input('code');

        $cacheKey = "otp:email:{$email}";
        $stored = Cache::get($cacheKey);
        if (!$stored) {
            return response()->json(['ok' => false, 'message' => 'OTP expired or not found'], 422);
        }

        if ((string)$stored !== (string)$code) {
            return response()->json(['ok' => false, 'message' => 'Invalid code'], 422);
        }

    Cache::forget($cacheKey);
    Cache::put("otp:verified:email:{$email}", true, 300);

    return response()->json(['ok' => true, 'message' => 'OTP verified']);
    }
}
