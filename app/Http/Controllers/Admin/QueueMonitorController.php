<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QueueMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function __construct(
        protected QueueMonitorService $queueMonitor
    ) {}

    public function index(): View
    {
        return view('admin.queue-monitor.index', [
            'title' => 'Queue monitor',
            'filters_initial' => $this->queueMonitor->getFilterOptions(),
            'summary_initial' => $this->queueMonitor->getSummary(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $baseFilters = [
            'queue' => $request->input('queue', 'all'),
            'job_class' => $request->input('job_class', 'all'),
            'booking_id' => $request->filled('booking_id') ? (int) $request->input('booking_id') : null,
            'search' => trim((string) $request->input('search', '')),
            'date_from' => $request->filled('date_from') ? $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->input('date_to') : null,
        ];

        if ($request->boolean('dashboard')) {
            $limit = (int) $request->input('limit', 8);
            $chip = (string) $request->input('chip', 'all');

            return response()->json($this->queueMonitor->dashboardData($limit, $chip, $baseFilters));
        }

        $filters = array_merge($baseFilters, [
            'status' => $request->input('status', 'all'),
        ]);

        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('per_page', 25)));

        return response()->json($this->queueMonitor->list($filters, $page, $perPage));
    }

    public function filters(): JsonResponse
    {
        return response()->json($this->queueMonitor->getFilterOptions());
    }
}
