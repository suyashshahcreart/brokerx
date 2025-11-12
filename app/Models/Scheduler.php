<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'mobile',
        'email',
        'mobile_verified_at',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * Attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
    ];

    /**
     * Full name accessor.
     */
    public function getNameAttribute(): string
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }

    /**
     * Appointments scheduled by this scheduler (scheduler_id).
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'scheduler_id');
    }

    /**
     * Appointments created by this scheduler (create_by).
     */
    public function createdAppointments()
    {
        return $this->hasMany(Appointment::class, 'create_by');
    }
}
