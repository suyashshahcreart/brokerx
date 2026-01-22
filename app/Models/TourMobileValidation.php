<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourMobileValidation extends Model
{
    protected $fillable = [
        'tour_id',
        'mobile',
        'base_mobile',
        'country_code',
        'country_name',
        'otp',
        'otp_expired_at',
    ];

    protected $casts = [
        'otp_expired_at' => 'datetime',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
