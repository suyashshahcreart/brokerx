<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourNotification extends Model
{
    protected $fillable = [
        'tour_code',
        'booking_id',
        'phone_number',
        'status',
        'notified_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the booking associated with this notification
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
