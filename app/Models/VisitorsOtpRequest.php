<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorsOtpRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'visitors_otp';

    protected $fillable = [
        'customer_id',
        'booking_id',
        'tour_id',
        'visitors_name',
        'visitors_mobile',
        'visitors_email',
        'otp',
        'download_link',
        'status',
        'verified_at',
        'otp_expires_at',
        'attempt_count',
        'last_attempt_at',
        'otp_sent_via_sms',
        'otp_sent_via_email',
        'notification_sent_to_agent',
        'notification_sent_at',
    ];

    protected $hidden = [
        'otp',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'otp_sent_via_sms' => 'boolean',
        'otp_sent_via_email' => 'boolean',
        'notification_sent_to_agent' => 'boolean',
    ];

    /**
     * Get the customer that owns this OTP request.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the booking associated with this OTP request.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Get the tour associated with this OTP request.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    /**
     * Check if OTP has expired.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->otp_expires_at);
    }

    /**
     * Check if OTP is still pending verification.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if OTP has been verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if maximum attempts exceeded (max 3 attempts).
     */
    public function isMaxAttemptsExceeded(): bool
    {
        return $this->attempt_count >= 5;
    }

    /**
     * Mark OTP as verified.
     */
    public function markAsVerified(): bool
    {
        return $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Increment attempt count.
     */
    public function incrementAttempt(): bool
    {
        return $this->update([
            'attempt_count' => $this->attempt_count + 1,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Mark as expired.
     */
    public function markAsExpired(): bool
    {
        return $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Mark as failed (max attempts exceeded).
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark notification as sent to agent.
     */
    public function markNotificationAsSent(): bool
    {
        return $this->update([
            'notification_sent_to_agent' => true,
            'notification_sent_at' => now(),
        ]);
    }

    /**
     * Get the latest pending OTP request for a customer.
     */
    public static function getLatestPending($customerId)
    {
        return static::where('customer_id', $customerId)
            ->where('status', 'pending')
            ->latest()
            ->first();
    }
}
