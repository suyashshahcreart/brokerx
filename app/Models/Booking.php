<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_type_id',
        'property_sub_type_id',
        'owner_type',
        'bhk_id',
        'city_id',
        'state_id',
        'furniture_type',
        'area',
        'price',
        'house_no',
        'building',
        'society_name',
        'address_area',
        'landmark',
        'full_address',
        'pin_code',
        'booking_date',
        'payment_status',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'cashfree_order_id',
        'cashfree_payment_session_id',
        'cashfree_payment_status',
        'cashfree_payment_method',
        'cashfree_payment_amount',
        'cashfree_payment_currency',
        'cashfree_reference_id',
        'cashfree_payment_at',
        'cashfree_payment_message',
        'cashfree_payment_meta',
        'cashfree_last_response',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'cashfree_payment_at' => 'datetime',
        'cashfree_payment_meta' => 'array',
        'cashfree_last_response' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function propertySubType()
    {
        return $this->belongsTo(PropertySubType::class);
    }

    public function bhk()
    {
        return $this->belongsTo(BHK::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
