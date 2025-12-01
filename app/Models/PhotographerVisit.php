<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhotographerVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_id',
        'booking_id',
        'tour_id',
        'photographer_id',
        'metadata',
        'visit_date',
        'status',
        'notes',
        // merged check-in fields
        'check_in_photo',
        'check_in_metadata',
        'checked_in_at',
        'check_in_location',
        'check_in_ip_address',
        'check_in_device_info',
        'check_in_remarks',
        // merged check-out fields
        'check_out_photo',
        'check_out_metadata',
        'checked_out_at',
        'check_out_location',
        'check_out_ip_address',
        'check_out_device_info',
        'check_out_remarks',
        'photos_taken',
        'work_summary',
    ];

    protected $casts = [
        'metadata' => 'array',
        'visit_date' => 'datetime',
        'check_in_metadata' => 'array',
        'check_out_metadata' => 'array',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    /**
     * Get the job for this visit
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(PhotographerVisitJob::class, 'job_id');
    }

    /**
     * Get the photographer (user) assigned to this visit
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get the booking associated with this visit
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the tour associated with this visit
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    // check-in/out now live on this model (no separate relations)

    /**
     * Scope to get visits by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get visits for a specific photographer
     */
    public function scopeForPhotographer($query, $photographerId)
    {
        return $query->where('photographer_id', $photographerId);
    }

    /**
     * Scope to get visits for a specific booking
     */
    public function scopeForBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope to get visits within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    /**
     * Check if the visit is checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->status === 'checked_in' && !is_null($this->checked_in_at);
    }

    /**
     * Check if the visit is checked out
     */
    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out' && !is_null($this->checked_out_at);
    }

    /**
     * Check if the visit is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && !is_null($this->checked_in_at) && !is_null($this->checked_out_at);
    }

    /**
     * Get the duration of the visit in minutes
     */
    public function getDuration(): ?int
    {
        if (!$this->checked_in_at || !$this->checked_out_at) {
            return null;
        }
        return $this->checked_in_at->diffInMinutes($this->checked_out_at);
    }
}
