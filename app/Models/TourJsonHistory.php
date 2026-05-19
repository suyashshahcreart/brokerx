<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores snapshots of tour ZIP-derived JSON/JS assets per upload version.
 *
 * Diff columns (when set): RFC 6902 JSON Patch — array of operations with op, path, optional value.
 */
class TourJsonHistory extends Model
{
    protected $table = 'tour_json_histories';

    protected $fillable = [
        'tour_id',
        'version',
        'virtual_tour_nodes_json',
        'tour_data_json',
        'tour_data_js',
        'virtual_tour_nodes_json_diff',
        'tour_data_json_diff',
        'tour_data_js_diff',
        'type',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'virtual_tour_nodes_json' => 'array',
            'tour_data_json' => 'array',
            'virtual_tour_nodes_json_diff' => 'array',
            'tour_data_json_diff' => 'array',
            'tour_data_js_diff' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
