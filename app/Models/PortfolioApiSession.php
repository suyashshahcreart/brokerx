<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PortfolioApiSession extends Model
{
    protected $fillable = [
        'device_fingerprint',
        'ip_address',
        'mobile_number',
        'otp_code',
        'otp_expires_at',
        'verified_at',
        'access_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Generate a unique access token
     */
    public function generateAccessToken(): string
    {
        return Str::random(64);
    }

    /**
     * Check if OTP is expired
     */
    public function isOtpExpired(): bool
    {
        if (!$this->otp_expires_at) {
            return true;
        }
        return Carbon::now()->isAfter($this->otp_expires_at);
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }
        return Carbon::now()->isAfter($this->token_expires_at);
    }

    /**
     * Check if session is valid (verified and token not expired)
     */
    public function isValid(): bool
    {
        return $this->is_active 
            && $this->verified_at !== null 
            && $this->access_token !== null 
            && !$this->isTokenExpired();
    }

    /**
     * Find active session by device fingerprint
     */
    public static function findByDeviceFingerprint(string $fingerprint): ?self
    {
        return self::where('device_fingerprint', $fingerprint)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * Find session by access token
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('access_token', $token)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Scope to get valid sessions
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('verified_at')
            ->whereNotNull('access_token')
            ->where('token_expires_at', '>', Carbon::now());
    }
}
