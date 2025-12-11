@extends('admin.layouts.vertical',['title'=>'Job listing'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">My Jobs</h4>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('photographer.jobs.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All</option>
                                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="">All</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-briefcase me-2"></i>Assigned Jobs
                    </h5>

                    @if($jobs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Code</th>
                                        <th>Booking</th>
                                        <th>Property</th>
                                        <th>Scheduled Date</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jobs as $job)
                                        <tr class="{{ $job->isOverdue() ? 'table-danger' : '' }}">
                                            <td>
                                                <strong>{{ $job->job_code }}</strong>
                                                @if($job->isOverdue())
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @endif
                                            </td>
                                            <td>#{{ $job->booking_id }}</td>
                                            <td>
                                                {{ $job->booking->propertyType?->name ?? 'N/A' }}<br>
                                                <small class="text-muted">{{ $job->booking->full_address ?? 'No address' }}</small>
                                            </td>
                                            <td>{{ $job->scheduled_date?->format('d M Y, h:i A') ?? 'Not scheduled' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $job->priority_color }}">
                                                    {{ ucfirst($job->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $job->status_color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('photographer.jobs.show', $job) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                @if($job->status === 'assigned')
                                                    <form action="{{ route('photographer.jobs.accept', $job) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-circle"></i> Accept
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $jobs->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No jobs found matching your criteria.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
