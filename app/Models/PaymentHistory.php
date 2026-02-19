<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHistory extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_session_id',
        'status',
        'amount',
        'currency',
        'payment_method',
        'gateway_response',
        'gateway_meta',
        'gateway_message',
        'initiated_at',
        'completed_at',
        'failed_at',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'gateway_meta' => 'array',
        'amount' => 'integer',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the booking that this payment history belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who made this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by booking
     */
    public function scopeForBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope to filter by gateway
     */
    public function scopeForGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get recent payments
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    /**
     * Get amount in rupees (convert from paise)
     */
    public function getAmountInRupeesAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'processing';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'cancelled';
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get gateway display name
     */
    public function getGatewayNameAttribute(): string
    {
        return match($this->gateway) {
            'cashfree' => 'Cashfree',
            'payu' => 'PayU',
            'razorpay' => 'Razorpay',
            default => ucfirst($this->gateway),
        };
    }
}
