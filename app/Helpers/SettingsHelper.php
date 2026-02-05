<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getSetting')) {
    /**
     * Get a setting value by name with Redis caching
     * 
     * @param string $name Setting name
     * @param mixed $default Default value if setting not found
     * @param int $cacheMinutes Cache duration in minutes (default: 1440 = 24 hours)
     * @return mixed
     */
    function getSetting(string $name, $default = null, int $cacheMinutes = 1440)
    {
        $cacheKey = "setting_{$name}";
        
        // Try Redis first, fallback to database cache if Redis not available
        try {
            return Cache::store('redis')->remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($name, $default) {
                return Setting::where('name', $name)->value('value') ?? $default;
            });
        } catch (\Exception $e) {
            // Fallback to database cache if Redis is not available
            // This ensures the app works even without Redis installed
            try {
                return Cache::store('database')->remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($name, $default) {
                    return Setting::where('name', $name)->value('value') ?? $default;
                });
            } catch (\Exception $dbException) {
                // If database cache also fails, return directly from database (no cache)
                \Log::error("Both Redis and database cache unavailable: " . $dbException->getMessage());
                return Setting::where('name', $name)->value('value') ?? $default;
            }
        }
    }
}

if (!function_exists('getQrLinkBase')) {
    /**
     * Get QR link base URL from settings with Redis caching
     * 
     * @return string
     */
    function getQrLinkBase(): string
    {
        return getSetting('qr_link_base', 'https://qr.proppik.com/');
    }
}

if (!function_exists('getApiBaseUrl')) {
    /**
     * Get API base URL from settings with Redis caching
     * 
     * @return string
     */
    function getApiBaseUrl(): string
    {
        return getSetting('api_base_url', 'https://dev.proppik.in/api/');
    }
}

if (!function_exists('getS3LinkBase')) {
    /**
     * Get S3 link base URL from settings with Redis caching
     * 
     * @return string
     */
    function getS3LinkBase(): string
    {
        return getSetting('s3_link_base', 'https://creartimages.s3.ap-south-1.amazonaws.com/');
    }
}

if (!function_exists('clearSettingCache')) {
    /**
     * Clear cache for a specific setting
     * 
     * @param string $name Setting name
     * @return bool
     */
    function clearSettingCache(string $name): bool
    {
        $cacheKey = "setting_{$name}";
        
        // Try to clear Redis cache first
        try {
            Cache::store('redis')->forget($cacheKey);
        } catch (\Exception $e) {
            // If Redis fails, clear database cache instead
            try {
                Cache::store('database')->forget($cacheKey);
            } catch (\Exception $dbException) {
                \Log::warning("Both Redis and database cache unavailable for clearing: " . $dbException->getMessage());
            }
        }
        
        return true;
    }
}

if (!function_exists('clearAllSettingsCache')) {
    /**
     * Clear all settings cache
     * 
     * @return bool
     */
    function clearAllSettingsCache(): bool
    {
        try {
            // Get all settings from database
            $settings = Setting::pluck('name');
            
            foreach ($settings as $name) {
                $cacheKey = "setting_{$name}";
                // Try Redis first, then database cache
                try {
                    Cache::store('redis')->forget($cacheKey);
                } catch (\Exception $e) {
                    // Fallback to database cache
                    try {
                        Cache::store('database')->forget($cacheKey);
                    } catch (\Exception $dbException) {
                        // Continue with next setting if cache clearing fails
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error clearing settings cache: " . $e->getMessage());
            return false;
        }
        
        return true;
    }
}
