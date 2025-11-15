<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyTypeFactory> */
    use HasFactory;
    protected $fillable = ['name', 'icon',];
    public function subTypes()
    {
        return $this->hasMany(PropertySubType::class, 'property_type_id');
    }
}
