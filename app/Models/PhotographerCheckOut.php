<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerCheckOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'photo',
        'metadata',
        'checked_out_at',
        'location',
        'ip_address',
        'device_info',
        'remarks',
        'photos_taken',
        'work_summary',
    ];

    protected $casts = [
        'metadata' => 'array',
        'checked_out_at' => 'datetime',
        'photos_taken' => 'integer',
    ];

    /**
     * Get the visit that this check-out belongs to
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PhotographerVisit::class, 'visit_id');
    }

    /**
     * Get the photographer through the visit
     */
    public function photographer()
    {
        return $this->visit->photographer();
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        // Check if it's already a full URL
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }

        // Return storage path
        return asset('storage/' . $this->photo);
    }

    /**
     * Scope to get recent check-outs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('checked_out_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get check-outs by photographer
     */
    public function scopeByPhotographer($query, $photographerId)
    {
        return $query->whereHas('visit', function ($q) use ($photographerId) {
            $q->where('photographer_id', $photographerId);
        });
    }

    /**
     * Scope to get check-outs with work completed
     */
    public function scopeWithWorkCompleted($query)
    {
        return $query->where('photos_taken', '>', 0);
    }

    /**
     * Get formatted check-out time
     */
    public function getFormattedCheckOutTimeAttribute(): string
    {
        return $this->checked_out_at->format('d M Y, h:i A');
    }

    /**
     * Calculate work duration with the corresponding check-in
     */
    public function getWorkDurationAttribute(): ?int
    {
        $checkIn = $this->visit->checkIn;
        
        if (!$checkIn) {
            return null;
        }

        return $checkIn->checked_in_at->diffInMinutes($this->checked_out_at);
    }

    /**
     * Get formatted work duration
     */
    public function getFormattedWorkDurationAttribute(): ?string
    {
        $duration = $this->work_duration;

        if (!$duration) {
            return null;
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return sprintf('%d hr %d min', $hours, $minutes);
        }

        return sprintf('%d min', $minutes);
    }
}
