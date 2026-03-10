<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory,SoftDeletes, HasApiTokens;

    protected $fillable = [
        'firstname',
        'lastname',
        'mobile',
        'base_mobile',
        'country_code',
        'dial_code',
        'country_id',
        'email',
        'password',
        'mobile_verified_at',
        'otp',
        'otp_verified_at',
        'otp_expires_at',
        'cover_photo',
        'profile_photo',
        'company_name',
        'company_website',
        'tag_line',
        'designation',
        'social_link',
        'is_active',
        'created_by',
        'updated_by',
        // SEO fields
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'meta_robots',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'og_title',
        'og_description',
        'header_code',
        'footer_code',
        'gtm_tag',
        'slug',
    ];

    protected $hidden = [
        'password',
        'otp',
    ];

    protected $casts = [
        'mobile_verified_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_active' => 'bool',
        'password' => 'hashed',
        'social_link' => 'array',
    ];

    public function getNameAttribute(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    /**
     * Update only SEO related attributes on the model.
     * Accepts a subset of fields and ignores others.
     *
     * @param  array  $data
     * @return bool
     */
    public function updateSeo(array $data): bool
    {
        $allowed = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'canonical_url',
            'meta_robots',
            'twitter_title',
            'twitter_description',
            'twitter_image',
            'og_title',
            'og_description',
            'header_code',
            'footer_code',
            'gtm_tag',
            'slug',
        ];

        $updateData = array_intersect_key($data, array_flip($allowed));
        return $this->update($updateData);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
