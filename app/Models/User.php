<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'password',
        'mobile_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the broker profile associated with the user.
     */
    public function broker()
    {
        return $this->hasOne(Broker::class);
    }

    /**
     * Check if user has a broker profile.
     */
    public function isBroker()
    {
        return $this->broker()->exists();
    }

    /**
     * Check if user's broker profile is approved.
     */
    public function isBrokerApproved()
    {
        return $this->broker && $this->broker->status === 'approved';
    }
}
