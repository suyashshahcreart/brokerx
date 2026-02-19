<?php

namespace App\Services;

use App\Models\PortfolioApiSession;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PortfolioApiService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Generate device fingerprint from request
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        return md5(
            $request->ip() . 
            $request->userAgent() . 
            $request->header('Accept-Language', '')
        );
    }

    /**
     * Generate 6-digit OTP
     */
    public function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get portfolio API settings
     */
    public function getSettings(): array
    {
        $mobileString = Setting::where('name', 'portfolio_api_mobile')->value('value') ?? '';
        
        // Parse comma-separated mobile numbers into array
        $mobiles = [];
        if (!empty($mobileString)) {
            $mobiles = array_filter(
                array_map('trim', explode(',', $mobileString)),
                function($mobile) {
                    return !empty($mobile);
                }
            );
            // Re-index array
            $mobiles = array_values($mobiles);
        }
        
        return [
            'mobiles' => $mobiles,
            'mobile' => !empty($mobiles) ? $mobiles[0] : '', // Keep for backward compatibility
            'token_validity_minutes' => (int) (Setting::where('name', 'portfolio_api_token_validity_minutes')->value('value') ?? 30),
            'enabled' => Setting::where('name', 'portfolio_api_enabled')->value('value') === '1',
        ];
    }

    /**
     * Send OTP to all configured mobile numbers
     */
    public function sendOtp(Request $request): array
    {
        $settings = $this->getSettings();

        if (!$settings['enabled']) {
            throw new \RuntimeException('Portfolio API is currently disabled');
        }

        if (empty($settings['mobiles'])) {
            throw new \RuntimeException('Portfolio API mobile numbers are not configured');
        }

        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $mobiles = $settings['mobiles'];
        $firstMobile = $mobiles[0]; // Store first mobile for session reference
        $otp = $this->generateOtp();

        // Check rate limiting (max 3 requests per 15 minutes per IP)
        $rateLimitKey = 'portfolio_api_otp_rate:' . $request->ip();
        $attempts = Cache::get($rateLimitKey, 0);
        
        if ($attempts >= 3) {
            throw new \RuntimeException('Too many OTP requests. Please try again after 15 minutes.');
        }

        // Increment rate limit counter
        Cache::put($rateLimitKey, $attempts + 1, now()->addMinutes(15));

        // Find or create session
        $session = PortfolioApiSession::findByDeviceFingerprint($deviceFingerprint);
        
        if (!$session) {
            $session = PortfolioApiSession::create([
                'device_fingerprint' => $deviceFingerprint,
                'ip_address' => $request->ip(),
                'mobile_number' => $firstMobile, // Store first mobile for reference
                'otp_code' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
                'is_active' => true,
            ]);
        } else {
            // Update existing session
            $session->update([
                'mobile_number' => $firstMobile, // Update to first mobile
                'otp_code' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
                'verified_at' => null,
                'access_token' => null,
                'token_expires_at' => null,
            ]);
        }

        // Send OTP to all configured mobile numbers
        $successCount = 0;
        $failCount = 0;
        $successMobiles = [];
        $failedMobiles = [];

        foreach ($mobiles as $mobile) {
            try {
                // Mobile numbers already include country code with + prefix
                // Remove + for SMS service (it expects format like 919876543210)
                $mobileForSms = ltrim($mobile, '+');
                
                $this->smsService->send(
                    $mobileForSms,
                    'login_otp', // Using existing template
                    ['OTP' => $otp],
                    [
                        'type' => 'manual',
                        'reference_type' => PortfolioApiSession::class,
                        'reference_id' => $session->id,
                        'notes' => 'Portfolio API OTP'
                    ]
                );

                $successCount++;
                $successMobiles[] = $mobile;
            } catch (\Exception $e) {
                $failCount++;
                $failedMobiles[] = $mobile;
                Log::warning('Failed to send Portfolio API OTP to mobile', [
                    'session_id' => $session->id,
                    'mobile' => $mobile,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log summary
        Log::info('Portfolio API OTP sending completed', [
            'session_id' => $session->id,
            'total_mobiles' => count($mobiles),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'success_mobiles' => $successMobiles,
            'failed_mobiles' => $failedMobiles,
            'otp' => $otp, // Log OTP for development/testing
        ]);

        // If all SMS failed, still return success but log warning
        // (OTP is still generated and can be verified, SMS failure is not critical)
        if ($successCount === 0) {
            Log::warning('Portfolio API OTP: All SMS sending failed', [
                'session_id' => $session->id,
                'mobiles' => $mobiles,
            ]);
        }

        return [
            'success' => true,
            'message' => $successCount > 0 
                ? "OTP sent successfully to {$successCount} mobile number(s)" 
                : 'OTP generated but SMS sending failed. Please check logs.',
            'session_id' => $session->id,
            'sent_to_count' => $successCount,
            'total_count' => count($mobiles),
        ];
    }

    /**
     * Verify OTP and generate access token
     */
    public function verifyOtp(Request $request, string $otp): array
    {
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        
        // Log for debugging
        Log::info('Portfolio API verifyOtp attempt', [
            'device_fingerprint' => $deviceFingerprint,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'otp_length' => strlen($otp),
        ]);
        
        // Find session by device fingerprint and OTP
        $session = PortfolioApiSession::where('device_fingerprint', $deviceFingerprint)
            ->where('otp_code', $otp)
            ->where('is_active', true)
            ->first();

        // If not found by device fingerprint, try to find by IP and OTP (fallback)
        if (!$session) {
            Log::warning('Portfolio API: Session not found by device fingerprint, trying IP fallback', [
                'device_fingerprint' => $deviceFingerprint,
                'ip' => $request->ip(),
            ]);
            
            // Try finding by IP address and OTP (more lenient matching)
            $session = PortfolioApiSession::where('ip_address', $request->ip())
                ->where('otp_code', $otp)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$session) {
            // Log available sessions for debugging
            $availableSessions = PortfolioApiSession::where('ip_address', $request->ip())
                ->where('is_active', true)
                ->whereNotNull('otp_code')
                ->where('otp_expires_at', '>', Carbon::now())
                ->get(['id', 'device_fingerprint', 'otp_code', 'created_at']);
            
            Log::warning('Portfolio API: Invalid OTP - no matching session found', [
                'device_fingerprint' => $deviceFingerprint,
                'ip' => $request->ip(),
                'available_sessions_count' => $availableSessions->count(),
            ]);
            
            throw new \RuntimeException('Invalid OTP. Please request a new OTP.');
        }

        if ($session->isOtpExpired()) {
            throw new \RuntimeException('OTP has expired. Please request a new one.');
        }

        $settings = $this->getSettings();
        $tokenValidityMinutes = $settings['token_validity_minutes'];

        // Generate access token
        $accessToken = $session->generateAccessToken();
        $tokenExpiresAt = Carbon::now()->addMinutes($tokenValidityMinutes);

        // Update session
        $session->update([
            'verified_at' => Carbon::now(),
            'access_token' => $accessToken,
            'token_expires_at' => $tokenExpiresAt,
            'otp_code' => null, // Clear OTP after verification
            'otp_expires_at' => null,
        ]);

        return [
            'success' => true,
            'access_token' => $accessToken,
            'expires_at' => $tokenExpiresAt->toDateTimeString(),
            'expires_in' => $tokenValidityMinutes * 60, // seconds
        ];
    }
}
