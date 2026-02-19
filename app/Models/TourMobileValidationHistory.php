<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourMobileValidationHistory extends Model
{
    protected $fillable = [
        'tour_id',
        'mobile',
        'action',
        'ip_address',
        'user_agent',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
