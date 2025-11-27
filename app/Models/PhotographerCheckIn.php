<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'photo',
        'metadata',
        'checked_in_at',
        'location',
        'ip_address',
        'device_info',
        'remarks',
    ];

    protected $casts = [
        'metadata' => 'array',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the visit that this check-in belongs to
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
     * Scope to get recent check-ins
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('checked_in_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get check-ins by photographer
     */
    public function scopeByPhotographer($query, $photographerId)
    {
        return $query->whereHas('visit', function ($q) use ($photographerId) {
            $q->where('photographer_id', $photographerId);
        });
    }

    /**
     * Get formatted check-in time
     */
    public function getFormattedCheckInTimeAttribute(): string
    {
        return $this->checked_in_at->format('d M Y, h:i A');
    }
}
