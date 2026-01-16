<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QRAnalytics extends Model
{
    use HasFactory;

    protected $table = 'qr_analytics';

    protected $fillable = [
        'tour_code',
        'booking_id',
        'page_url',
        'page_type',
        'user_ip',
        'user_agent',
        'browser_name',
        'browser_version',
        'os_name',
        'os_version',
        'device_type',
        'screen_resolution',
        'language',
        'country',
        'city',
        'region',
        'full_address',
        'pincode',
        'latitude',
        'longitude',
        'timezone',
        'location_source',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'session_id',
        'user_id',
        'scan_date',
        'tracking_status',
        'error_message',
        'load_time',
        'metadata',
    ];

    protected $casts = [
        'scan_date' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'load_time' => 'decimal:4',
        'metadata' => 'array',
    ];

    /**
     * Get the booking associated with this QR analytics record
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user associated with this QR analytics record (if logged in)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
