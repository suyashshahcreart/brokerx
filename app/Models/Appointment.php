<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scheduler_id',
        'property_id',
        'date',
        'start_time',
        'end_time',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'status',
        'assigne_by',
        'assigne_to',
        'completed_by',
        'updated_by',
        'create_by',
    ];

    /**
     * The attributes that should be cast.     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scheduler()
    {
        return $this->belongsTo(Scheduler::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Scheduler::class, 'create_by');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigne_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigne_to');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function property()
    {
        return $this->belongsTo('App\\Models\\Property', 'property_id');
    }
}
