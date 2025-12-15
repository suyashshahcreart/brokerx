<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHistory extends Model
{
    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the booking that this history entry belongs to
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who made the status change
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope to filter by booking
     */
    public function scopeForBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeToStatus($query, $status)
    {
        return $query->where('to_status', $status);
    }

    /**
     * Scope to get recent history
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
