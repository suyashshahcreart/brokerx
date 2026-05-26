@extends('admin.layouts.vertical', ['title' => $title ?? 'Queue monitor', 'subTitle' => 'Admin'])

@section('content')
    <div id="queue-monitor-root"
         class="mb-4"
         data-api-url="{{ route('admin.queue-monitor.data') }}"
         data-filters-url="{{ route('admin.queue-monitor.filters') }}">

        <div class="row g-2 mb-3 align-items-end" id="queue-monitor-summary">
            <div class="col-auto">
                <span class="badge bg-secondary-subtle text-secondary">Pending: <strong id="qm-sum-pending">—</strong></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning-subtle text-warning">Running: <strong id="qm-sum-running">—</strong></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-info-subtle text-info">Delayed: <strong id="qm-sum-delayed">—</strong></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-danger-subtle text-danger">Failed 24h: <strong id="qm-sum-failed">—</strong></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary-subtle text-primary">Tours processing: <strong id="qm-sum-tours">—</strong></span>
            </div>
            <div class="col-auto ms-auto form-check form-switch">
                <input class="form-check-input" type="checkbox" id="qm-auto-refresh" checked>
                <label class="form-check-label" for="qm-auto-refresh">Auto-refresh (2s)</label>
            </div>
        </div>

        @if(!empty($summary_initial['default_pending_warning']))
            <div class="alert alert-warning py-2 small mb-3" role="alert">
                There are jobs waiting on the <strong>default</strong> queue. Ensure a worker is listening to that queue if you expect them to run.
            </div>
        @endif

        <div class="alert alert-light border small mb-3" role="status">
            {{ config('queue_monitor.supervisor_queues_note') }}
        </div>

        <form id="queue-monitor-form" class="card mb-3">
            <div class="card-body row g-2">
                <div class="col-md-2">
                    <label class="form-label small mb-0">Queue</label>
                    <select name="queue" class="form-select form-select-sm" id="qm-filter-queue">
                        <option value="all">All</option>
                        @foreach($filters_initial['queues'] ?? [] as $q)
                            <option value="{{ $q }}">{{ $q }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm" id="qm-filter-status">
                        <option value="all">All (active mix)</option>
                        <option value="pending">Pending</option>
                        <option value="running">Running</option>
                        <option value="delayed">Delayed</option>
                        <option value="failed">Failed</option>
                        <option value="tour_processing">Tour processing</option>
                        <option value="tour_done">Tour done (recent)</option>
                        <option value="tour_failed">Tour failed (recent)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Job type</label>
                    <select name="job_class" class="form-select form-select-sm" id="qm-filter-job-class">
                        <option value="all">All</option>
                        @foreach($filters_initial['job_classes'] ?? [] as $jc)
                            <option value="{{ $jc }}">{{ class_basename($jc) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-0">Booking</label>
                    <input type="number" name="booking_id" class="form-control form-control-sm" id="qm-filter-booking" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Search</label>
                    <input type="search" name="search" class="form-control form-control-sm" id="qm-filter-search" placeholder="Customer name">
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-0">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" id="qm-filter-from">
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-0">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" id="qm-filter-to">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Apply</button>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 align-middle">
                        <thead class="bg-light-subtle">
                        <tr>
                            <th>Queue</th>
                            <th>Job</th>
                            <th>Status</th>
                            <th>Subject</th>
                            <th>Progress</th>
                            <th>Queued</th>
                            <th>Started</th>
                            <th>ETA / note</th>
                            <th>Finished</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody id="queue-monitor-tbody">
                        <tr><td colspan="10" class="text-center text-muted py-4">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center p-2 border-top">
                    <div class="small text-muted" id="qm-pagination-info"></div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="qm-page-prev" disabled>Prev</button>
                        <button type="button" class="btn btn-outline-secondary" id="qm-page-next" disabled>Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/queue-monitor-index.js'])
@endsection
