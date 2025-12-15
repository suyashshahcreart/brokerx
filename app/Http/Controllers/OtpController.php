<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        $validated = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:13'],
        ]);

        $mobile = $validated['mobile'];

        // Rate limit per mobile: 1 request per 30s, max 5 per hour
        // $rateKey = 'otp:rate:' . md5($mobile);
        // $last = Cache::get($rateKey);
        // if ($last && now()->diffInSeconds($last) < 30) {
        //     return response()->json([
        //         'ok' => false,
        //         'message' => 'Please wait before requesting another code.',
        //         'retry_after' => 30 - now()->diffInSeconds($last),
        //     ], 429);
        // }
        // Cache::put($rateKey, now(), now()->addHour());

        // Generate 6-digit OTP and store for 5 minutes
        $code = random_int(100000, 999999);
        $otpKey = 'otp:code:' . md5($mobile);
        Cache::put($otpKey, (string) $code, now()->addMinutes(5));

        // TODO: Integrate SMS gateway (e.g., Twilio, Vonage) here
        Log::info('OTP generated for mobile ' . $mobile . ': ' . $code);

        return response()->json([
            'ok' => true,
            'message' => 'Verification code sent',
            'ttl' => 300,
        ]);
    }

    public function verify(Request $request)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        $validated = $request->validate([
            'mobile' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'digits:6'],
        ]);

        $mobile = $validated['mobile'];
        $code = $validated['code'];

        $otpKey = 'otp:code:' . md5($mobile);
        $stored = Cache::get($otpKey);

        if (!$stored) {
            return response()->json([
                'ok' => false,
                'message' => 'Code expired. Please request a new one.',
            ], 410);
        }

        if ((string) $stored !== (string) $code) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid code. Please try again.',
            ], 422);
        }

        Cache::forget($otpKey);
        
        // Update user's mobile_verified_at
        $user = auth()->user();
        if ($user && $user->mobile === $mobile) {
            $user->mobile_verified_at = now();
            $user->save();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Mobile number verified successfully',
        ]);
    }
}


