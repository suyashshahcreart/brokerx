@extends('admin.layouts.vertical', ['title' => 'Activity Log', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Activity Log</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Activity Log</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Recent Changes</h4>
                    <p class="text-muted mb-0">Track all updates performed across users, roles, and permissions</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form class="row gy-2 gx-3 align-items-end mb-3" id="activity-filters">
                    <div class="col-md-3">
                        <label class="form-label">Log Name</label>
                        <select class="form-select" id="filter-log-name" name="log_name">
                            <option value="">All Logs</option>
                            @foreach($logNames as $name)
                                <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Event</label>
                        <select class="form-select" id="filter-event" name="event">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select class="form-select" id="filter-user" name="causer_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="filter-date-range" name="date_range" placeholder="Select date range">
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="activity-table">
                        <thead class="table-light">
                        <tr>
                            <th width="180">Date & Time</th>
                            <th width="120">Event</th>
                            <th width="150">Subject</th>
                            <th width="160">By</th>
                            <th>Changes</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $ = window.jQuery;
            if (!$) return;

            const daterangepickerReady = typeof window.__loadDateRangePicker === 'function'
                ? window.__loadDateRangePicker()
                : Promise.resolve();

            daterangepickerReady.then(() => {

            const $logFilter = $('#filter-log-name');
            const $eventFilter = $('#filter-event');
            const $userFilter = $('#filter-user');
            const $dateFilter = $('#filter-date-range');

            if ($.fn.select2) {
                $logFilter.select2({ placeholder: 'All Logs', allowClear: true, width: '100%' });
                $eventFilter.select2({ placeholder: 'All Events', allowClear: true, width: '100%' });
                $userFilter.select2({ placeholder: 'All Users', allowClear: true, width: '100%' });
            }

            const table = $('#activity-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.activity.index') }}',
                    data: function (d) {
                        d.log_name = $logFilter.val();
                        d.event = $eventFilter.val();
                        d.causer_id = $userFilter.val();
                        d.date_range = $dateFilter.val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'event_badge', name: 'description', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'subject_info', name: 'subject_type', orderable: false, searchable: false },
                    { data: 'causer_info', name: 'causer.name', orderable: false, searchable: false },
                    { data: 'changes', name: 'properties', orderable: false, searchable: false },
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search activity...'
                },
                lengthMenu: [10, 25, 50, 100],
                drawCallback: function () {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
                    }
                }
            });

            if (typeof $.fn.daterangepicker === 'function' && typeof window.moment === 'function' && typeof window.moment.localeData === 'function') {
                $dateFilter.daterangepicker({
                    autoUpdateInput: false,
                    opens: 'center',
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 2 Days': [moment().subtract(1, 'days'), moment()],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'Last 365 Days': [moment().subtract(364, 'days'), moment()],
                        'This Year': [moment().startOf('year'), moment().endOf('year')],
                        'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
                    },
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'MM/DD/YYYY'
                    }
                });

                $dateFilter.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    table.ajax.reload();
                });

                $dateFilter.on('cancel.daterangepicker', function() {
                    $(this).val('');
                    table.ajax.reload();
                });
            }

            $logFilter.on('change', () => table.ajax.reload());
            $eventFilter.on('change', () => table.ajax.reload());
            $userFilter.on('change', () => table.ajax.reload());
            }).catch(() => {
                console.warn('DateRangePicker failed to load; filters limited to basic fields.');
            });
        });
    </script>
@endsection


