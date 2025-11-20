<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class QR extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'qr_code';

    protected $fillable = [
        'name',
        'code',
        'image',
        'qr_link',
        'booking_id',
        'created_by',
        'updated_by',
    ];

    protected static $logAttributes = [
        'name',
        'code',
        'image',
        'qr_link',
        'booking_id',
        'created_by',
        'updated_by'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults();
    }
}