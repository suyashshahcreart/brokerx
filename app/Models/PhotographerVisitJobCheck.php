<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerVisitJobCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_visit_job_id',
        'type',
        'photo',
        'location',
        'location_timestamp',
        'location_accuracy',
        'location_source',
        'photos_taken',
        'work_summary',
        'remarks',
        'metadata',
        'checked_at',
        'ip_address',
        'device_info',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'location_timestamp' => 'datetime',
        'checked_at' => 'datetime',
        'location_accuracy' => 'float',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(PhotographerVisitJob::class, 'photographer_visit_job_id');
    }
}
