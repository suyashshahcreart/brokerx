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
        'check_in_id',
        'check_out_id',
        'metadata',
        'visit_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'visit_date' => 'datetime',
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

    /**
     * Get the check-in record for this visit
     */
    public function checkIn(): HasOne
    {
        return $this->hasOne(PhotographerCheckIn::class, 'visit_id');
    }

    /**
     * Get the check-out record for this visit
     */
    public function checkOut(): HasOne
    {
        return $this->hasOne(PhotographerCheckOut::class, 'visit_id');
    }

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
        return $this->status === 'checked_in' && $this->checkIn()->exists();
    }

    /**
     * Check if the visit is checked out
     */
    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out' && $this->checkOut()->exists();
    }

    /**
     * Check if the visit is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->checkIn()->exists() && $this->checkOut()->exists();
    }

    /**
     * Get the duration of the visit in minutes
     */
    public function getDuration(): ?int
    {
        if (!$this->checkIn || !$this->checkOut) {
            return null;
        }

        return $this->checkIn->checked_in_at->diffInMinutes($this->checkOut->checked_out_at);
    }
}
