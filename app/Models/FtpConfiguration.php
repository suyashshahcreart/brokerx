<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FtpConfiguration extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_name',
        'display_name',
        'main_url',
        'driver',
        'host',
        'username',
        'password',
        'port',
        'root',
        'passive',
        'ssl',
        'timeout',
        'remote_path_pattern',
        'url_pattern',
        'is_active',
        'sort_order',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Hide password from JSON/array serialization
     */
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'passive' => 'boolean',
            'ssl' => 'boolean',
            'is_active' => 'boolean',
            'port' => 'integer',
            'timeout' => 'integer',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Encrypt password when setting it
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when getting it
     */
    public function getPasswordAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // If decryption fails (e.g., old plain text password), return as-is
            // This handles migration from plain text to encrypted
            return $value;
        }
    }

    /**
     * Get the user who created this FTP configuration.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this FTP configuration.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category_name', 'display_name', 'main_url', 'host', 'username', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get FTP configuration array for Laravel Storage
     */
    public function getStorageConfigAttribute(): array
    {
        $host = preg_replace('#^s?ftps?://#', '', $this->host);
        
        $config = [
            'driver' => $this->driver,
            'host' => $host,
            'username' => $this->username,
            'password' => $this->password,
            'port' => $this->port,
            'root' => $this->root,
            'timeout' => $this->timeout,
            'throw' => false,
        ];

        if ($this->driver === 'ftp') {
            $config['passive'] = $this->passive;
            $config['ssl'] = $this->ssl;
        } else if ($this->driver === 'sftp') {
            $config['visibility'] = 'public';
            $config['permissions'] = [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ];
        }

        return $config;
    }

    /**
     * Get remote path for tour upload (with customer_id)
     */
    public function getRemotePathForTour(string $tourSlug, int $customerId): string
    {
        $pattern = $this->remote_path_pattern ?? '{customer_id}/{slug}/index.php';
        return str_replace(
            ['{customer_id}', '{slug}'],
            [$customerId, $tourSlug],
            $pattern
        );
    }

    /**
     * Get URL for tour (with customer_id)
     */
    public function getUrlForTour(string $tourSlug, int $customerId): string
    {
        $remotePath = $this->getRemotePathForTour($tourSlug, $customerId);
        $pattern = $this->url_pattern ?? 'https://{main_url}/{remote_path}';
        
        return str_replace(
            ['{main_url}', '{remote_path}'],
            [$this->main_url, $remotePath],
            $pattern
        );
    }

    /**
     * Scope to get active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }
}
