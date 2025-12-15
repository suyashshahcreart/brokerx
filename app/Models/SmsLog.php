<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gateway',
        'type',
        'template_key',
        'template_id',
        'mobile',
        'message',
        'params',
        'status',
        'status_code',
        'success',
        'response_body',
        'response_json',
        'error_message',
        'gateway_message_id',
        'cost',
        'sender_id',
        'user_id',
        'reference_type',
        'reference_id',
        'notes',
        'sent_at',
        'delivered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'params' => 'array',
            'response_json' => 'array',
            'success' => 'boolean',
            'cost' => 'decimal:4',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user who triggered this SMS (if manual)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo('reference');
    }

    /**
     * Scope: Filter by gateway
     */
    public function scopeGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Successful SMS only
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true)->where('status', 'sent');
    }

    /**
     * Scope: Failed SMS only
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false)->orWhere('status', 'failed');
    }

    /**
     * Scope: Recent SMS
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
