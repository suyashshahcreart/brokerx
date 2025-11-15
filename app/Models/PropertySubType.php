<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertySubType extends Model
{
    /** @use HasFactory<\Database\Factories\PropertySubTypeFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'property_type_id',
        'icon',
    ];

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }
}
