<?php

namespace App\Services;

use App\Models\Booking;
use App\Support\QueueJobPayloadParser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueMonitorService
{
    public function getFilterOptions(): array
    {
        return Cache::remember('queue_monitor.filters', 60, function () {
            $queuesJobs = DB::table('jobs')->select('queue')->distinct()->pluck('queue');
            $queuesFailed = DB::table('failed_jobs')->select('queue')->distinct()->pluck('queue');
            $queues = $queuesJobs->merge($queuesFailed)->unique()->sort()->values()->all();

            $known = config('queue_monitor.known_queues', []);
            $queues = collect($known)->merge($queues)->unique()->sort()->values()->all();

            $classes = $this->distinctDisplayNamesFromPayloads(400);

            return [
                'queues' => $queues,
                'job_classes' => array_values($classes),
            ];
        });
    }

    /**
     * @return array<int, string>
     */
    private function distinctDisplayNamesFromPayloads(int $limit): array
    {
        $classes = collect();

        DB::table('jobs')
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('payload')
            ->each(function ($payload) use ($classes) {
                $p = QueueJobPayloadParser::decodePayload($payload);
                $name = QueueJobPayloadParser::displayName($p);
                if ($name !== '' && $name !== 'UnknownJob') {
                    $classes->push($name);
                }
            });

        $since = Carbon::now()->subDays((int) config('queue_monitor.recent_failed_days_for_filters', 30));

        DB::table('failed_jobs')
            ->where('failed_at', '>=', $since)
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('payload')
            ->each(function ($payload) use ($classes) {
                $p = QueueJobPayloadParser::decodePayload($payload);
                $name = QueueJobPayloadParser::displayName($p);
                if ($name !== '' && $name !== 'UnknownJob') {
                    $classes->push($name);
                }
            });

        return $classes->unique()->sort()->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        $now = Carbon::now()->timestamp;

        $pending = DB::table('jobs')
            ->whereNull('reserved_at')
            ->where('available_at', '<=', $now)
            ->count();

        $delayed = DB::table('jobs')
            ->where('available_at', '>', $now)
            ->count();

        $running = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->count();

        $failed24 = DB::table('failed_jobs')
            ->where('failed_at', '>=', Carbon::now()->subDay())
            ->count();

        $toursProcessing = Booking::query()
            ->where('tour_zip_status', 'processing')
            ->count();

        $byQueue = DB::table('jobs')
            ->select('queue')
            ->selectRaw('SUM(CASE WHEN reserved_at IS NULL AND available_at <= ? THEN 1 ELSE 0 END) as pending', [$now])
            ->selectRaw('SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as running', [])
            ->groupBy('queue')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->queue => [
                        'pending' => (int) $row->pending,
                        'running' => (int) $row->running,
                    ],
                ];
            })
            ->all();

        $defaultPending = DB::table('jobs')
            ->where('queue', 'default')
            ->whereNull('reserved_at')
            ->where('available_at', '<=', $now)
            ->count();

        return [
            'pending' => $pending,
            'delayed' => $delayed,
            'running' => $running,
            'failed_24h' => $failed24,
            'tours_processing' => $toursProcessing,
            'by_queue' => $byQueue,
            'default_pending_warning' => $defaultPending > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{summary: array, rows: array<int, array>, pagination: array, worker_note: string}
     */
    public function list(array $filters, int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $queue = (string) ($filters['queue'] ?? 'all');
        $status = (string) ($filters['status'] ?? 'all');
        $jobClass = (string) ($filters['job_class'] ?? 'all');
        $bookingId = isset($filters['booking_id']) && $filters['booking_id'] !== '' && $filters['booking_id'] !== null
            ? (int) $filters['booking_id'] : null;
        $search = trim((string) ($filters['search'] ?? ''));
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $dateFromC = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $dateToC = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        $rows = collect();

        $includeJobs = in_array($status, ['all', 'pending', 'running', 'delayed'], true);
        $includeFailed = $status === 'all' || $status === 'failed';
        $includeTourProcessing = $status === 'all' || $status === 'tour_processing';
        $includeTourDone = $status === 'tour_done';
        $includeTourFailed = $status === 'tour_failed';

        if ($includeJobs) {
            $rows = $rows->merge($this->collectJobRows($queue, $jobClass, $bookingId, $status, $dateFromC, $dateToC));
        }

        if ($includeFailed) {
            $rows = $rows->merge($this->collectFailedRows($queue, $jobClass, $bookingId, $dateFromC, $dateToC));
        }

        if ($includeTourProcessing) {
            $rows = $rows->merge($this->collectBookingRows('processing', $bookingId, $search, $dateFromC, $dateToC));
        }

        if ($includeTourDone) {
            $days = (int) config('queue_monitor.completed_tours_default_days', 14);
            $from = $dateFromC ?? Carbon::now()->subDays($days)->startOfDay();
            $to = $dateToC ?? Carbon::now()->endOfDay();
            $rows = $rows->merge($this->collectBookingRows('done', $bookingId, $search, $from, $to));
        }

        if ($includeTourFailed) {
            $days = (int) config('queue_monitor.completed_tours_default_days', 14);
            $from = $dateFromC ?? Carbon::now()->subDays($days)->startOfDay();
            $to = $dateToC ?? Carbon::now()->endOfDay();
            $rows = $rows->merge($this->collectBookingRows('failed', $bookingId, $search, $from, $to));
        }

        $rows = $this->dedupeRows($rows);

        $rows = $this->preloadCustomerNamesForSearch($rows);

        if ($search !== '') {
            $needle = Str::lower($search);
            $rows = $rows->filter(function (array $r) use ($needle) {
                $hay = Str::lower(
                    ($r['subject'] ?? '').
                    ' '.($r['customer_name'] ?? '').
                    ' '.($r['booking_id'] ?? '').
                    ' '.($r['job_class'] ?? '')
                );

                return str_contains($hay, $needle);
            });
        }

        if ($bookingId !== null) {
            $rows = $rows->filter(fn (array $r) => (int) ($r['booking_id'] ?? 0) === $bookingId);
        }

        if ($jobClass !== 'all') {
            $rows = $rows->filter(function (array $r) use ($jobClass) {
                return ($r['job_class'] ?? '') === $jobClass;
            });
        }

        $sorted = $this->sortRows($rows->values());

        $total = $sorted->count();
        $slice = $sorted->forPage($page, $perPage)->values()->all();

        $bookings = $this->loadBookingsForRows($slice);
        $slice = array_map(function (array $r) use ($bookings) {
            return $this->enrichRowWithBooking($r, $bookings);
        }, $slice);

        return [
            'summary' => $this->getSummary(),
            'rows' => $slice,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
            'worker_note' => (string) config('queue_monitor.supervisor_queues_note', ''),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectJobRows(
        string $queue,
        string $jobClass,
        ?int $bookingId,
        string $statusFilter,
        ?Carbon $dateFrom,
        ?Carbon $dateTo
    ): Collection {
        $q = DB::table('jobs');
        if ($queue !== 'all') {
            $q->where('queue', $queue);
        }

        if ($dateFrom !== null) {
            $q->where('created_at', '>=', $dateFrom->timestamp);
        }
        if ($dateTo !== null) {
            $q->where('created_at', '<=', $dateTo->timestamp);
        }

        $now = Carbon::now()->timestamp;
        $out = collect();

        foreach ($q->orderByDesc('id')->limit(2000)->get() as $row) {
            $payload = QueueJobPayloadParser::decodePayload($row->payload);
            $displayName = QueueJobPayloadParser::displayName($payload);
            if ($jobClass !== 'all' && $displayName !== $jobClass) {
                continue;
            }

            $bid = QueueJobPayloadParser::bookingIdFromPayload($payload);
            if ($bookingId !== null && $bid !== $bookingId) {
                continue;
            }

            $avail = (int) $row->available_at;
            $res = $row->reserved_at !== null ? (int) $row->reserved_at : null;

            if ($avail > $now) {
                $uiStatus = 'delayed';
            } elseif ($res !== null) {
                $uiStatus = 'running';
            } else {
                $uiStatus = 'pending';
            }

            if ($statusFilter !== 'all' && $uiStatus !== $statusFilter) {
                continue;
            }

            $queuedAt = Carbon::createFromTimestamp((int) $row->created_at);
            $startedAt = $res ? Carbon::createFromTimestamp($res) : null;

            $out->push([
                'id' => 'job-'.$row->id,
                'source' => 'jobs',
                'queue' => (string) $row->queue,
                'job_class' => $displayName,
                'job_label' => $this->jobLabel($displayName),
                'status' => $uiStatus,
                'attempts' => (int) $row->attempts,
                'booking_id' => $bid,
                'customer_name' => null,
                'subject' => $bid ? 'Booking #'.$bid : $this->jobLabel($displayName),
                'queued_at' => $queuedAt->toIso8601String(),
                'started_at' => $startedAt?->toIso8601String(),
                'finished_at' => null,
                'error_summary' => null,
                'progress' => null,
                'links' => [],
            ]);
        }

        return $out;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectFailedRows(
        string $queue,
        string $jobClass,
        ?int $bookingId,
        ?Carbon $dateFrom,
        ?Carbon $dateTo
    ): Collection {
        $q = DB::table('failed_jobs');
        if ($queue !== 'all') {
            $q->where('queue', $queue);
        }
        if ($dateFrom !== null) {
            $q->where('failed_at', '>=', $dateFrom);
        }
        if ($dateTo !== null) {
            $q->where('failed_at', '<=', $dateTo);
        }

        $out = collect();
        foreach ($q->orderByDesc('id')->limit(500)->get() as $row) {
            $payload = QueueJobPayloadParser::decodePayload($row->payload);
            $displayName = QueueJobPayloadParser::displayName($payload);
            if ($jobClass !== 'all' && $displayName !== $jobClass) {
                continue;
            }
            $bid = QueueJobPayloadParser::bookingIdFromPayload($payload);
            if ($bookingId !== null && $bid !== $bookingId) {
                continue;
            }

            $failedAt = Carbon::parse($row->failed_at);
            $exception = (string) ($row->exception ?? '');
            $firstLine = $exception !== '' ? Str::limit(strtok($exception, "\n"), 200) : null;

            $out->push([
                'id' => 'failed-'.$row->id,
                'source' => 'failed_jobs',
                'queue' => (string) $row->queue,
                'job_class' => $displayName,
                'job_label' => $this->jobLabel($displayName),
                'status' => 'failed',
                'attempts' => null,
                'booking_id' => $bid,
                'customer_name' => null,
                'subject' => $bid ? 'Booking #'.$bid : $this->jobLabel($displayName),
                'queued_at' => null,
                'started_at' => null,
                'finished_at' => $failedAt->toIso8601String(),
                'error_summary' => $firstLine,
                'progress' => null,
                'links' => [],
            ]);
        }

        return $out;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectBookingRows(
        string $zipStatus,
        ?int $bookingId,
        string $search,
        ?Carbon $dateFrom,
        ?Carbon $dateTo
    ): Collection {
        $q = Booking::query()
            ->with('customer')
            ->where('tour_zip_status', $zipStatus);

        if ($bookingId !== null) {
            $q->where('id', $bookingId);
        }

        if ($zipStatus === 'processing') {
            if ($dateFrom !== null) {
                $q->where(function ($qq) use ($dateFrom) {
                    $qq->where('tour_zip_started_at', '>=', $dateFrom)
                        ->orWhereNull('tour_zip_started_at');
                });
            }
            if ($dateTo !== null) {
                $q->where(function ($qq) use ($dateTo) {
                    $qq->where('tour_zip_started_at', '<=', $dateTo)
                        ->orWhereNull('tour_zip_started_at');
                });
            }
        } else {
            if ($dateFrom !== null) {
                $q->where('tour_zip_finished_at', '>=', $dateFrom);
            }
            if ($dateTo !== null) {
                $q->where('tour_zip_finished_at', '<=', $dateTo);
            }
        }

        if ($search !== '') {
            $term = '%'.str_replace('%', '\\%', $search).'%';
            $q->whereHas('customer', function ($cq) use ($term) {
                $cq->where('firstname', 'like', $term)
                    ->orWhere('lastname', 'like', $term);
            });
        }

        $cls = config('queue_monitor.tour_job_classes.0', 'App\\Jobs\\ProcessTourZipFile');

        return $q->orderByDesc('updated_at')->limit(500)->get()->map(function (Booking $b) use ($cls, $zipStatus) {
            $uiStatus = match ($zipStatus) {
                'processing' => 'tour_processing',
                'done' => 'tour_done',
                'failed' => 'tour_failed',
                default => $zipStatus,
            };

            return [
                'id' => 'booking-'.$b->id.'-'.$zipStatus,
                'source' => 'bookings',
                'queue' => 'tour-processing',
                'job_class' => $cls,
                'job_label' => $this->jobLabel($cls),
                'status' => $uiStatus,
                'attempts' => null,
                'booking_id' => $b->id,
                'customer_name' => $b->customer
                    ? trim(($b->customer->firstname ?? '').' '.($b->customer->lastname ?? ''))
                    : null,
                'subject' => 'Booking #'.$b->id,
                'queued_at' => optional($b->tour_zip_started_at)?->toIso8601String(),
                'started_at' => optional($b->tour_zip_started_at)?->toIso8601String(),
                'finished_at' => optional($b->tour_zip_finished_at)?->toIso8601String(),
                'error_summary' => $zipStatus === 'failed' ? Str::limit((string) $b->tour_zip_message, 200) : null,
                'progress' => null,
                'links' => [],
            ];
        });
    }

    /**
     * Fill customer_name before text search so job rows filter by booking customer too.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function preloadCustomerNamesForSearch(Collection $rows): Collection
    {
        $ids = $rows->pluck('booking_id')->filter(fn ($id) => (int) $id > 0)->map(fn ($id) => (int) $id)->unique()->values()->all();
        if ($ids === []) {
            return $rows;
        }

        $bookings = Booking::query()->with('customer')->whereIn('id', $ids)->get()->keyBy('id');

        return $rows->map(function (array $r) use ($bookings) {
            if (! empty($r['customer_name']) || empty($r['booking_id'])) {
                return $r;
            }
            $b = $bookings->get((int) $r['booking_id']);
            if ($b?->customer) {
                $r['customer_name'] = trim(($b->customer->firstname ?? '').' '.($b->customer->lastname ?? ''));
                if (str_starts_with((string) ($r['subject'] ?? ''), 'Booking #')) {
                    $r['subject'] = '#'.$r['booking_id'].' · '.$r['customer_name'];
                }
            }

            return $r;
        });
    }

    /**
     * Prefer job row over booking-only when same booking and processing.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function dedupeRows(Collection $rows): Collection
    {
        $byBooking = [];
        $noBooking = [];

        foreach ($rows as $r) {
            $bid = $r['booking_id'] ?? null;
            if ($bid === null || (int) $bid <= 0) {
                $noBooking[] = $r;

                continue;
            }

            $bid = (int) $bid;
            $key = $bid;
            $prev = $byBooking[$key] ?? null;
            if ($prev === null) {
                $byBooking[$key] = $r;

                continue;
            }

            // Prefer jobs source over bookings for same booking while processing-ish
            $score = function (array $x): int {
                $src = $x['source'] ?? '';

                return match ($src) {
                    'jobs' => 3,
                    'failed_jobs' => 2,
                    'bookings' => 1,
                    default => 0,
                };
            };

            if ($score($r) > $score($prev)) {
                $byBooking[$key] = $r;
            } elseif ($score($r) === $score($prev) && (($r['source'] ?? '') === 'jobs')) {
                $byBooking[$key] = $r;
            }
        }

        return collect(array_values($byBooking))->merge(collect($noBooking));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function sortRows(Collection $rows): Collection
    {
        return $rows->sort(function ($a, $b) {
            $order = ['running' => 0, 'delayed' => 1, 'pending' => 2, 'tour_processing' => 3, 'failed' => 4, 'tour_failed' => 5, 'tour_done' => 6];

            $sa = $order[$a['status']] ?? 99;
            $sb = $order[$b['status']] ?? 99;
            if ($sa !== $sb) {
                return $sa <=> $sb;
            }

            $ta = strtotime($a['queued_at'] ?? $a['finished_at'] ?? $a['started_at'] ?? 'now') ?: 0;
            $tb = strtotime($b['queued_at'] ?? $b['finished_at'] ?? $b['started_at'] ?? 'now') ?: 0;

            return $tb <=> $ta;
        })->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, Booking>
     */
    private function loadBookingsForRows(array $rows): array
    {
        $ids = collect($rows)->pluck('booking_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();
        if ($ids === []) {
            return [];
        }

        return Booking::query()
            ->with('customer')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }

    /**
     * @param  array<int, Booking>  $bookings
     * @return array<string, mixed>
     */
    private function enrichRowWithBooking(array $r, array $bookings): array
    {
        $bid = $r['booking_id'] ?? null;
        if ($bid === null) {
            return $r;
        }

        /** @var Booking|null $b */
        $b = $bookings[(int) $bid] ?? null;
        if ($b !== null && $r['customer_name'] === null && $b->customer) {
            $r['customer_name'] = trim(($b->customer->firstname ?? '').' '.($b->customer->lastname ?? ''));
            $r['subject'] = '#'.$b->id.' · '.($r['customer_name'] !== '' ? $r['customer_name'] : $r['subject']);
        }

        $tourClasses = config('queue_monitor.tour_job_classes', []);
        $isTourJob = isset($r['job_class']) && in_array($r['job_class'], $tourClasses, true);

        if ($b !== null && ($isTourJob || in_array($r['status'] ?? '', ['tour_processing', 'tour_done', 'tour_failed'], true))) {
            $st = $b->tour_zip_status ?? 'pending';

            // Progress block when booking has ZIP fields meaningful
            if (in_array($st, ['processing', 'done', 'failed'], true)) {
                $r['progress'] = [
                    'tour_zip_status' => $st,
                    'percent' => (float) ($b->tour_zip_progress ?? 0),
                    'phase' => $b->tour_zip_phase,
                    'message' => $b->tour_zip_message,
                    'items_done' => (int) ($b->tour_zip_items_done ?? 0),
                    'items_total' => (int) ($b->tour_zip_items_total ?? 0),
                    'eta_seconds' => $b->tour_zip_eta_seconds,
                    'current_item' => $b->tour_zip_current_item,
                ];

                // Align timestamps from booking for tour UX
                if ($r['started_at'] === null && $b->tour_zip_started_at) {
                    $r['started_at'] = $b->tour_zip_started_at->toIso8601String();
                }
                if ($r['queued_at'] === null && $b->tour_zip_started_at) {
                    $r['queued_at'] = $b->tour_zip_started_at->toIso8601String();
                }
                if (in_array($r['status'] ?? '', ['tour_done', 'tour_failed', 'running', 'pending', 'delayed'], true) &&
                    ($st === 'done' || $st === 'failed')
                ) {
                    $r['finished_at'] = optional($b->tour_zip_finished_at)?->toIso8601String();
                }

                // If booking finished but jobs row stale, elevate status hint
                if (($r['status'] === 'running' || $r['status'] === 'pending' || $r['status'] === 'delayed') &&
                    ($st === 'done' || $st === 'failed')
                ) {
                    $r['status'] = $st === 'done' ? 'tour_done' : 'tour_failed';
                }
            }
        }

        if ($bid !== null) {
            $r['links'] = [
                'booking' => route('admin.bookings.show', $bid),
                'tour_show' => route('admin.tour-manager.show', $bid),
                'tour_upload' => route('admin.tour-manager.upload', $bid),
            ];
        }

        return $r;
    }

    private function jobLabel(string $displayName): string
    {
        $labels = config('queue_monitor.job_labels', []);

        return $labels[$displayName] ?? class_basename($displayName);
    }

    /**
     * Dashboard card: merges running, pending, tour processing, plus a few recent failures.
     *
     * @param  array<string, mixed>  $baseFilters queue, booking_id, search, dates
     */
    public function dashboardData(int $limit, string $chip, array $baseFilters = []): array
    {
        $limit = max(1, min(50, $limit));

        $base = array_merge([
            'queue' => 'all',
            'job_class' => 'all',
            'booking_id' => null,
            'search' => '',
            'date_from' => null,
            'date_to' => null,
        ], $baseFilters);

        if ($chip === 'failed') {
            $data = $this->list(array_merge($base, ['status' => 'failed']), 1, $limit);

            return $this->wrapDashboardResponse($data, $limit);
        }

        if (in_array($chip, ['pending', 'running', 'delayed'], true)) {
            $data = $this->list(array_merge($base, ['status' => $chip]), 1, $limit);

            return $this->wrapDashboardResponse($data, $limit);
        }

        $chunks = [];
        foreach (['running', 'pending', 'tour_processing'] as $st) {
            $chunks = array_merge($chunks, $this->list(array_merge($base, ['status' => $st]), 1, $limit)['rows']);
        }
        $failLimit = max(3, min(8, $limit));
        $chunks = array_merge($chunks, $this->list(array_merge($base, ['status' => 'failed']), 1, $failLimit)['rows']);

        $merged = $this->dedupeRows(collect($chunks));
        $sorted = $this->sortRows($merged);
        $slice = array_slice($sorted->all(), 0, $limit);
        $bookings = $this->loadBookingsForRows($slice);
        $slice = array_map(fn (array $row) => $this->enrichRowWithBooking($row, $bookings), $slice);

        return [
            'summary' => $this->getSummary(),
            'rows' => $slice,
            'pagination' => [
                'total' => count($slice),
                'per_page' => $limit,
                'current_page' => 1,
                'last_page' => 1,
            ],
            'worker_note' => (string) config('queue_monitor.supervisor_queues_note', ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $data  output of list()
     */
    private function wrapDashboardResponse(array $data, int $limit): array
    {
        $rows = array_slice($data['rows'], 0, $limit);

        return [
            'summary' => $data['summary'],
            'rows' => $rows,
            'pagination' => [
                'total' => min((int) ($data['pagination']['total'] ?? count($rows)), $limit),
                'per_page' => $limit,
                'current_page' => 1,
                'last_page' => 1,
            ],
            'worker_note' => $data['worker_note'] ?? '',
        ];
    }

}
