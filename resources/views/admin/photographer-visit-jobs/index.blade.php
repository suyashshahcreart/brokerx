@extends('admin.layouts.vertical', ['title' => 'Photographer Visit Jobs'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin.photographer-visit-jobs.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Create New Job
                    </a>
                </div>
                <h4 class="page-title">
                    <i class="bi bi-briefcase me-2"></i>Photographer Visit Jobs
                </h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h6 class="text-muted">Pending Jobs</h6>
                    <h3 class="mb-0" id="pendingCount">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h6 class="text-muted">Assigned Jobs</h6>
                    <h3 class="mb-0" id="assignedCount">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h6 class="text-muted">In Progress</h6>
                    <h3 class="mb-0" id="inProgressCount">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h3 class="mb-0" id="completedCount">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="filters-card">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="filter-label">Status</label>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="filter-label">Priority</label>
                                <select id="priorityFilter" class="form-select form-select-sm">
                                    <option value="">All Priorities</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="normal">Normal</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="filter-label">Photographer</label>
                                <select id="photographerFilter" class="form-select form-select-sm">
                                    <option value="">All Photographers</option>
                                    @foreach($photographers as $photographer)
                                        <option value="{{ $photographer->id }}">{{ $photographer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="filter-label">Scheduled Date</label>
                                <input type="date" id="dateFilter" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1 mb-2">
                                <label class="filter-label">&nbsp;</label>
                                <button type="button" id="clearFilters" class="btn btn-sm btn-secondary w-100">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="jobs-table" class="table table-striped table-hover dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Booking</th>
                                    <th>Photographer</th>
                                    <th>Scheduled Date</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 90px;">Check In</th>
                                    <th style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Pass routes to JavaScript
    window.photographerJobsRoutes = {
        index: '{{ route('admin.photographer-visit-jobs.index') }}',
        store: '{{ route('admin.photographer-visit-jobs.store') }}',
        show: '{{ route('admin.photographer-visit-jobs.show', ':id') }}',
        edit: '{{ route('admin.photographer-visit-jobs.edit', ':id') }}',
        destroy: '{{ route('admin.photographer-visit-jobs.destroy', ':id') }}',
        checkInForm: '{{ route('admin.photographer-visit-jobs.check-in-form', ':id') }}',
        checkOutForm: '{{ route('admin.photographer-visit-jobs.check-out-form', ':id') }}'
    };
</script>
@vite(['resources/js/pages/photographer-visit-jobs-index.js'])
@endsection
