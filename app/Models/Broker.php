<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'whatsapp_number',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'license_number',
        'company_name',
        'position_title',
        'years_of_experience',
        'license_verified',
        'commission_rate',
        'bio',
        'profile_image',
        'cover_image',
        'social_links',
        'status',
        'working_status',
        'total_sales',
        'average_rating',
        'approved_at',
        'joined_at',
    ];

    protected $casts = [
        'social_links' => 'array',
        'license_verified' => 'boolean',
        'working_status' => 'boolean',
        'years_of_experience' => 'integer',
        'total_sales' => 'integer',
        'commission_rate' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'approved_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
