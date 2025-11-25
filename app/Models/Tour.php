<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tour extends Model
{
    /** @use HasFactory<\Database\Factories\TourFactory> */
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'name',
        'title',
        'slug',
        'description',
        'content',
        'featured_image',
        'price',
        'duration_days',
        'location',
        'start_date',
        'end_date',
        'max_participants',
        'status',
        'final_json',
        'revision',
        // SEO Meta Fields
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'og_title',
        'og_description',
        'canonical_url',
        'meta_robots',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'structured_data_type',
        'structured_data',
        'header_code',
        'footer_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'structured_data' => 'array',
            'final_json' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The booking associated with this tour.
     */
    public function booking()
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }


    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'title',
                'slug',
                'status',
                'price',
                'location',
                'start_date',
                'end_date',
                'revision',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($tour) {
            if (empty($tour->slug)) {
                $tour->slug = \Illuminate\Support\Str::slug($tour->title);
            }
        });

        static::updating(function ($tour) {
            if ($tour->isDirty('title') && empty($tour->slug)) {
                $tour->slug = \Illuminate\Support\Str::slug($tour->title);
            }
        });
    }

    /**
     * Scope a query to only include published tours.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft tours.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived tours.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price !== null ? '₹ ' . number_format((float) $this->price, 2) : '-';
    }

    /**
     * Get the duration in a readable format.
     */
    public function getDurationTextAttribute(): string
    {
        if (!$this->duration_days) {
            return '-';
        }
        
        $days = $this->duration_days;
        $nights = $days - 1;
        
        if ($nights > 0) {
            return "{$nights} Night" . ($nights > 1 ? 's' : '') . " / {$days} Day" . ($days > 1 ? 's' : '');
        }
        
        return "{$days} Day" . ($days > 1 ? 's' : '');
    }
}
