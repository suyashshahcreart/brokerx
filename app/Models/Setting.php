<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Setting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'value',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this setting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this setting.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'value', 'created_by', 'updated_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Clear cache when setting is saved (created or updated)
        static::saved(function ($setting) {
            $cacheKey = "setting_{$setting->name}";
            try {
                Cache::store('redis')->forget($cacheKey);
            } catch (\Exception $e) {
                // Fallback to database cache if Redis unavailable
                try {
                    Cache::store('database')->forget($cacheKey);
                } catch (\Exception $dbException) {
                    // If database cache also fails, continue silently
                    \Log::warning("Cache clearing failed for setting {$setting->name}: " . $dbException->getMessage());
                }
            }
        });
        
        // Clear cache when setting is deleted
        static::deleted(function ($setting) {
            $cacheKey = "setting_{$setting->name}";
            try {
                Cache::store('redis')->forget($cacheKey);
            } catch (\Exception $e) {
                // Fallback to database cache if Redis unavailable
                try {
                    Cache::store('database')->forget($cacheKey);
                } catch (\Exception $dbException) {
                    // If database cache also fails, continue silently
                    \Log::warning("Cache clearing failed for setting {$setting->name}: " . $dbException->getMessage());
                }
            }
        });
    }
}
