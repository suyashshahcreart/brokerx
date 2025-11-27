@extends('admin.layouts.vertical', ['title' => 'Photographer Visit Jobs'])

@section('css')
<link href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .filters-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 5px;
    }
</style>
@endsection

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
                                    <th style="width: 100px;">Action</th>
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
<script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<script>
$(document).ready(function() {
    var table = $('#jobs-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route('admin.photographer-visit-jobs.index') }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.priority = $('#priorityFilter').val();
                d.photographer_id = $('#photographerFilter').val();
                d.scheduled_date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'job_code', name: 'job_code', width: '15%' },
            { data: 'booking', name: 'booking_id', width: '15%' },
            { data: 'photographer', name: 'photographer.name', width: '15%' },
            { data: 'scheduled_date', name: 'scheduled_date', width: '15%' },
            { data: 'priority', name: 'priority', width: '10%', className: 'text-center' },
            { data: 'status', name: 'status', width: '10%', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%', className: 'text-center' }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No photographer visit jobs available",
            zeroRecords: "No matching jobs found"
        },
        drawCallback: function(settings) {
            // Update statistics
            updateStatistics();
        }
    });

    // Filter handlers
    $('#statusFilter, #priorityFilter, #photographerFilter, #dateFilter').on('change', function() {
        table.draw();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#priorityFilter').val('');
        $('#photographerFilter').val('');
        $('#dateFilter').val('');
        table.draw();
    });

    // Delete handler
    $('#jobs-table').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var jobId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this photographer visit job? This action cannot be undone.')) {
            $.ajax({
                url: '{{ route('admin.photographer-visit-jobs.index') }}/' + jobId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.ajax.reload(null, false);
                    showNotification('success', 'Job deleted successfully');
                },
                error: function(xhr) {
                    var message = xhr.responseJSON?.message || 'Error deleting job';
                    showNotification('error', message);
                }
            });
        }
    });

    // Update statistics
    function updateStatistics() {
        $.ajax({
            url: '{{ route('admin.photographer-visit-jobs.index') }}',
            data: {
                get_stats: true
            },
            success: function(data) {
                if (data.stats) {
                    $('#pendingCount').text(data.stats.pending || 0);
                    $('#assignedCount').text(data.stats.assigned || 0);
                    $('#inProgressCount').text(data.stats.in_progress || 0);
                    $('#completedCount').text(data.stats.completed || 0);
                }
            }
        });
    }

    // Notification helper
    function showNotification(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
        
        var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="bi ' + icon + ' me-2"></i>' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>');
        
        $('.page-title-box').after(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Initial statistics load
    updateStatistics();
});
</script>
@endsection
