<?php

namespace App\Services;

use App\Models\Tour;
use App\Models\TourJsonHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mikemccabe\JsonPatch\JsonPatch;

class TourAssetJsonPersistenceService
{
    /**
     * Deep clone ZIP asset payloads before array_merge/sync mutates nested arrays shared with $result['data'].
     */
    public static function snapshotZipPayloadForHistory(array $result): array
    {
        $slice = [
            'virtual_tour_nodes_json' => $result['virtual_tour_nodes_json'] ?? null,
            'tour_data_json' => $result['tour_data_json'] ?? null,
            's3_config_js' => $result['s3_config_js'] ?? null,
            '_asset_presence' => $result['_asset_presence'] ?? [],
        ];

        return json_decode(json_encode($slice, JSON_INVALID_UTF8_SUBSTITUTE), true) ?? $slice;
    }

    /**
     * Persist virtual-tour-nodes, tour-data.json, and tour-data.js from processZipFile result.
     *
     * Initial row: *_diff columns are null.
     * Later uploads: *_diff stores RFC 6902 JSON Patch — array of { op, path, value? } from previous snapshot to new ZIP content.
     */
    public function recordFromZipResult(Tour $tour, array $zipResult, ?int $userId, string $type = 'zip_upload', ?string $notes = null): void
    {
        $userId = $userId ?? 1;

        // Reload from DB so "before" state is not affected by in-memory merges on the Tour instance.
        $prior = Tour::query()
            ->whereKey($tour->id)
            ->first(['virtual_tour_nodes_json', 'tour_data_json', 's3_config_js']);

        $prevVtn = $prior?->virtual_tour_nodes_json;
        $prevTd = $prior?->tour_data_json;
        $prevJs = $prior?->s3_config_js;

        $presence = $zipResult['_asset_presence'] ?? [];
        $newVtn = ! empty($presence['virtual_tour_nodes'])
            ? ($zipResult['virtual_tour_nodes_json'] ?? null)
            : $prevVtn;
        $newTd = ! empty($presence['tour_data_json'])
            ? ($zipResult['tour_data_json'] ?? null)
            : $prevTd;
        $newJs = ! empty($presence['s3_config_js'])
            ? ($zipResult['s3_config_js'] ?? null)
            : $prevJs;

        $lastVersion = (int) TourJsonHistory::where('tour_id', $tour->id)->max('version');
        $version = $lastVersion + 1;
        $isFirst = $lastVersion === 0;

        $resolvedNotes = $notes ?? ($isFirst ? 'Initial save' : 'ZIP upload');
        $resolvedType = $isFirst ? 'initial' : $type;

        if ($isFirst) {
            $diffVtn = null;
            $diffTd = null;
            $diffJs = null;
        } else {
            $diffVtn = $this->buildRfc6902Patch($prevVtn, $newVtn, 'virtual_tour_nodes');
            $diffTd = $this->buildRfc6902Patch($prevTd, $newTd, 'tour_data_json');
            $diffJs = $this->buildRfc6902Patch($prevJs, $newJs, 's3_config_js');
        }

        DB::transaction(function () use (
            $tour,
            $userId,
            $version,
            $newVtn,
            $newTd,
            $newJs,
            $diffVtn,
            $diffTd,
            $diffJs,
            $resolvedType,
            $resolvedNotes
        ) {
            TourJsonHistory::create([
                'tour_id' => $tour->id,
                'version' => $version,
                'virtual_tour_nodes_json' => $newVtn,
                'tour_data_json' => $newTd,
                's3_config_js' => $newJs,
                'virtual_tour_nodes_json_diff' => $diffVtn,
                'tour_data_json_diff' => $diffTd,
                's3_config_js_diff' => $diffJs,
                'type' => $resolvedType,
                'notes' => $resolvedNotes,
                'updated_by' => $userId,
            ]);

            $tour->virtual_tour_nodes_json = $newVtn;
            $tour->tour_data_json = $newTd;
            $tour->s3_config_js = $newJs;
            $tour->updated_by = $userId;
            $tour->save();
        });
    }

    /**
     * @param  array|string|null  $before
     * @param  array|string|null  $after
     */
    private function buildRfc6902Patch($before, $after, string $label): ?array
    {
        try {
            $src = $this->normalizeForJsonPatch($before);
            $dst = $this->normalizeForJsonPatch($after);

            $patch = JsonPatch::diff($src, $dst);

            if (! is_array($patch) || count($patch) === 0) {
                return null;
            }

            // Ensure strict JSON array of ops (numeric indices) for MySQL JSON column
            return array_values($patch);
        } catch (\Throwable $e) {
            Log::warning('Tour JSON Patch diff failed', [
                'asset' => $label,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * JsonPatch::diff expects arrays. JSON documents decode to associative/numeric arrays (RFC 6902 paths like /branding/...).
     * Non-JSON strings (e.g. tour-data.js source) use line-split numeric arrays so ops use /0, /1, …
     *
     * @param  array|string|null  $value
     */
    private function normalizeForJsonPatch($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            if ($value === '') {
                return [];
            }

            return explode("\n", str_replace("\r\n", "\n", $value));
        }

        return [];
    }
}
