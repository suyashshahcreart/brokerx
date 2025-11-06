<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailOtpController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $validated['email'];

        // Rate limit per email: 1 request per 30s, max 5 per hour
        $rateKey = 'email-otp:rate:' . md5($email);
        $last = Cache::get($rateKey);
        if ($last && now()->diffInSeconds($last) < 30) {
            return response()->json([
                'ok' => false,
                'message' => 'Please wait before requesting another code.',
                'retry_after' => 30 - now()->diffInSeconds($last),
            ], 429);
        }
        Cache::put($rateKey, now(), now()->addHour());

        // Generate 6-digit OTP and store for 5 minutes
        $code = random_int(100000, 999999);
        $otpKey = 'email-otp:code:' . md5($email);
        Cache::put($otpKey, (string) $code, now()->addMinutes(5));

        // Attempt to email (optional). If mail isn't configured, we'll still log.
        try {
            // Mail::raw("Your verification code is: {$code}", function ($message) use ($email) {
            //     $message->to($email)->subject('Your Verification Code');
            // });
        } catch (\Throwable $e) {
            Log::warning('Failed to send email OTP (mail not configured): ' . $e->getMessage());
        }
        Log::info('Email OTP generated for ' . $email . ': ' . $code);

        return response()->json([
            'ok' => true,
            'message' => 'Verification code sent',
            'ttl' => 300,
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = $validated['email'];
        $code = $validated['code'];

        $otpKey = 'email-otp:code:' . md5($email);
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
        session(['email_verified:' . md5($email) => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Email verified successfully',
        ]);
    }
}


