<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

/**
 * Persist tour ZIP job progress with throttled writes and ETA.
 */
class TourZipProgressService
{
    /** @var array<int, array{time: float, progress: float, phase: string}> */
    private static array $lastFlush = [];

    private const MIN_FLUSH_INTERVAL_SECONDS = 2.0;

    private const MIN_PROGRESS_DELTA = 0.25;

    public function resetProgressFields(Booking $booking): void
    {
        $booking->tour_zip_progress = 0;
        $booking->tour_zip_phase = null;
        $booking->tour_zip_current_item = null;
        $booking->tour_zip_items_done = 0;
        $booking->tour_zip_items_total = 0;
        $booking->tour_zip_eta_seconds = null;
    }

    /**
     * Clears granular fields when a new queued job begins (fresh progress).
     */
    public function initializeQueued(Booking $booking, string $message): void
    {
        $booking->tour_zip_status = 'processing';
        $this->resetProgressFields($booking);
        $booking->tour_zip_phase = 'queued';
        $booking->tour_zip_message = $message;
        $booking->tour_zip_started_at = now();
        $booking->tour_zip_finished_at = null;
        $booking->save();
    }

    /**
     * @param  array{current_item?: string|null, items_done?: int|null, items_total?: int|null}  $meta
     */
    public function report(int $bookingId, float $progress, string $phase, string $message, array $meta = [], bool $force = false): void
    {
        $booking = Booking::find($bookingId);
        if (! $booking) {
            return;
        }

        $clamped = max(0.0, min(100.0, round($progress, 2)));

        $now = microtime(true);
        $last = self::$lastFlush[$bookingId] ?? null;
        $phaseChanged = $last === null || ($last['phase'] ?? '') !== $phase;

        if (! $force && $last !== null && ! $phaseChanged) {
            $elapsedSince = $now - $last['time'];
            $progDelta = abs($clamped - (float) $last['progress']);
            if ($elapsedSince < self::MIN_FLUSH_INTERVAL_SECONDS && $progDelta < self::MIN_PROGRESS_DELTA) {
                return;
            }
        }

        $booking->tour_zip_status = 'processing';
        $booking->tour_zip_progress = $clamped;
        $booking->tour_zip_phase = $phase;
        $booking->tour_zip_message = $message;

        if (array_key_exists('current_item', $meta)) {
            $booking->tour_zip_current_item = $meta['current_item'] !== null ? (string) substr($meta['current_item'], 0, 255) : null;
        }
        if (array_key_exists('items_done', $meta)) {
            $booking->tour_zip_items_done = max(0, (int) $meta['items_done']);
        }
        if (array_key_exists('items_total', $meta)) {
            $booking->tour_zip_items_total = max(0, (int) $meta['items_total']);
        }

        if ($booking->tour_zip_started_at === null) {
            $booking->tour_zip_started_at = now();
        }

        if ($booking->tour_zip_started_at !== null && $clamped > 2.0) {
            $started = $booking->tour_zip_started_at->getTimestamp();
            $elapsedSeconds = max(1, now()->getTimestamp() - $started);
            $overallFactor = ($clamped / 100);
            if ($overallFactor > 0.001) {
                $estimatedTotal = $elapsedSeconds / $overallFactor;
                $remaining = (int) max(0, round($estimatedTotal - $elapsedSeconds));
                $booking->tour_zip_eta_seconds = $remaining;
            } else {
                $booking->tour_zip_eta_seconds = null;
            }
        } else {
            $booking->tour_zip_eta_seconds = null;
        }

        try {
            $booking->save();
        } catch (\Throwable $e) {
            Log::warning('TourZipProgressService failed to save booking progress', [
                'booking_id' => $bookingId,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        self::$lastFlush[$bookingId] = [
            'time' => $now,
            'progress' => $clamped,
            'phase' => $phase,
        ];
    }

    public function markDone(int $bookingId, string $message = 'Processing completed'): void
    {
        unset(self::$lastFlush[$bookingId]);
        try {
            $booking = Booking::find($bookingId);
            if (! $booking) {
                return;
            }
            $booking->tour_zip_status = 'done';
            $booking->tour_zip_progress = 100;
            $booking->tour_zip_phase = 'done';
            $booking->tour_zip_message = $message;
            $booking->tour_zip_eta_seconds = 0;
            $booking->tour_zip_finished_at = now();

            $booking->save();
        } catch (\Throwable $e) {
            Log::warning('TourZipProgressService markDone failed', [
                'booking_id' => $bookingId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function markFailed(int $bookingId, string $message): void
    {
        unset(self::$lastFlush[$bookingId]);
        try {
            $booking = Booking::find($bookingId);
            if (! $booking) {
                return;
            }
            $booking->tour_zip_status = 'failed';
            $booking->tour_zip_progress = 0;
            $booking->tour_zip_phase = 'failed';
            $booking->tour_zip_message = $message;
            $booking->tour_zip_eta_seconds = null;
            $booking->tour_zip_finished_at = now();

            $booking->save();
        } catch (\Throwable $e) {
            Log::warning('TourZipProgressService markFailed failed', [
                'booking_id' => $bookingId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
