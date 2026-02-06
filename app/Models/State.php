<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    /** @use HasFactory<\Database\Factories\StateFactory> */
    use HasFactory;

    protected $fillable = ['name', 'code', 'country_id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
