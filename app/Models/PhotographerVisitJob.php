<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PhotographerVisitJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'tour_id',
        'photographer_id',
        'job_code',
        'status',
        'priority',
        'scheduled_date',
        'assigned_at',
        'started_at',
        'completed_at',
        'instructions',
        'special_requirements',
        'estimated_duration',
        'metadata',
        'cancellation_reason',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
        'assigned_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_date' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot method to generate job code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->job_code)) {
                $job->job_code = 'PVJ-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the booking associated with this job
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the tour associated with this job
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the photographer assigned to this job
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get all visits for this job
     */
    public function visits(): HasMany
    {
        return $this->hasMany(PhotographerVisit::class, 'job_id');
    }

    public function checks(): HasMany
    {
        return $this->hasMany(PhotographerVisitJobCheck::class);
    }

    /**
     * Get the user who created this job
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who assigned the photographer
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for jobs by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for jobs by photographer
     */
    public function scopeForPhotographer($query, $photographerId)
    {
        return $query->where('photographer_id', $photographerId);
    }

    /**
     * Scope for pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for assigned jobs
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for jobs by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for upcoming jobs
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>', now())
            ->whereIn('status', ['pending', 'assigned']);
    }

    /**
     * Assign photographer to this job
     */
    public function assignPhotographer($photographerId, $assignedBy = null)
    {
        $this->update([
            'photographer_id' => $photographerId,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assigned_by' => $assignedBy ?? auth()->id(),
        ]);

        return $this;
    }

    /**
     * Mark job as in progress
     */
    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Cancel the job
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Check if job is assigned
     */
    public function isAssigned(): bool
    {
        return !is_null($this->photographer_id) && strtolower($this->status) === 'assigned';
    }

    /**
     * Check if job is in progress
     */
    public function isInProgress(): bool
    {
        return strtolower($this->status) === 'in_progress';
    }

    /**
     * Check if job is completed
     */
    public function isCompleted(): bool
    {
        return strtolower($this->status) === 'completed';
    }

    /**
     * Check if job is overdue
     */
    public function isOverdue(): bool
    {
         $status = strtolower($this->status ?? '');

         return $this->scheduled_date && 
             $this->scheduled_date->isPast() && 
             !in_array($status, ['completed', 'cancelled']);
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        $priority = strtolower($this->priority ?? '');

        return match($priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'info',
            'low' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        $status = strtolower($this->status ?? '');

        return match($status) {
            'pending' => 'secondary',
            'assigned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
