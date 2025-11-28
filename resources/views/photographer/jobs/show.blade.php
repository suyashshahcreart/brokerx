@extends('admin.layouts.vertical',['title'=>'Job Show'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title">Job Details - {{ $job->job_code }}</h4>
                <a href="{{ route('photographer.jobs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Jobs
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Job Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Job Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Job Code:</strong> {{ $job->job_code }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $job->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </p>
                            <p><strong>Priority:</strong> 
                                <span class="badge bg-{{ $job->priority_color }}">
                                    {{ ucfirst($job->priority) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Scheduled Date:</strong> {{ $job->scheduled_date?->format('d M Y, h:i A') ?? 'Not scheduled' }}</p>
                            <p><strong>Assigned At:</strong> {{ $job->assigned_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            @if($job->isOverdue())
                                <p><span class="badge bg-danger">OVERDUE</span></p>
                            @endif
                        </div>
                    </div>

                    @if($job->instructions)
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Instructions:</h6>
                            <p class="mb-0">{{ $job->instructions }}</p>
                        </div>
                    @endif

                    @if($job->special_requirements)
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Special Requirements:</h6>
                            <p class="mb-0">{{ $job->special_requirements }}</p>
                        </div>
                    @endif

                    @if($job->notes)
                        <div class="alert alert-secondary">
                            <h6><i class="bi bi-sticky"></i> Notes:</h6>
                            <p class="mb-0">{{ $job->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Booking Details -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Booking Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Booking ID:</strong> #{{ $job->booking->id }}</p>
                            <p><strong>Client:</strong> {{ $job->booking->user->name ?? 'N/A' }}</p>
                            <p><strong>Property Type:</strong> {{ $job->booking->propertyType?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Area:</strong> {{ $job->booking->area }} sq ft</p>
                            <p><strong>Address:</strong> {{ $job->booking->full_address ?? 'No address provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visits -->
            @if($job->visits->count() > 0)
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Visit History</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Visit Date</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($job->visits as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_date?->format('d M Y') ?? 'N/A' }}</td>
                                            <td>{{ $visit->checkIn?->check_in_time ?? 'N/A' }}</td>
                                            <td>{{ $visit->checkOut?->check_out_time ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($visit->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Actions</h5>

                    @if($job->status === 'assigned')
                        <form action="{{ route('photographer.jobs.accept', $job) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Accept Job
                            </button>
                        </form>
                    @endif

                    @if($job->isInProgress())
                        <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeJobModal">
                            <i class="bi bi-check2-all"></i> Mark as Completed
                        </button>
                    @endif

                    @if($job->status === 'assigned' || $job->isInProgress())
                        <a href="{{ route('admin.photographer-visits.create', ['job_id' => $job->id]) }}" class="btn btn-info w-100">
                            <i class="bi bi-plus-circle"></i> Create Visit
                        </a>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Timeline</h5>
                    <ul class="list-unstyled">
                        @if($job->created_at)
                            <li class="mb-2">
                                <i class="bi bi-circle-fill text-secondary"></i>
                                Created: {{ $job->created_at->format('d M Y, h:i A') }}
                            </li>
                        @endif
                        @if($job->assigned_at)
                            <li class="mb-2">
                                <i class="bi bi-circle-fill text-info"></i>
                                Assigned: {{ $job->assigned_at->format('d M Y, h:i A') }}
                            </li>
                        @endif
                        @if($job->started_at)
                            <li class="mb-2">
                                <i class="bi bi-circle-fill text-primary"></i>
                                Started: {{ $job->started_at->format('d M Y, h:i A') }}
                            </li>
                        @endif
                        @if($job->completed_at)
                            <li class="mb-2">
                                <i class="bi bi-circle-fill text-success"></i>
                                Completed: {{ $job->completed_at->format('d M Y, h:i A') }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Job Modal -->
<div class="modal fade" id="completeJobModal" tabindex="-1" aria-labelledby="completeJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('photographer.jobs.complete', $job) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeJobModalLabel">Complete Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Completion Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Add any final notes about the job completion..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
