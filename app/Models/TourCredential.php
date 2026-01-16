<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourCredential extends Model
{
    protected $fillable = [
        'tour_id',
        'user_name',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
