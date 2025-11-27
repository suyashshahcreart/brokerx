@extends('admin.layouts.vertical', ['title' => 'View Job'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.photographer-visit-jobs.index') }}">Photographer Jobs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $photographerVisitJob->job_code }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Job Details - {{ $photographerVisitJob->job_code }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.photographer-visit-jobs.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line me-1"></i> Back to Jobs
                </a>
                @can('photographer_visit_job_edit')
                    <a href="{{ route('admin.photographer-visit-jobs.edit', $photographerVisitJob) }}" class="btn btn-primary">
                        <i class="ri-pencil-line me-1"></i> Edit Job
                    </a>
                @endcan
            </div>
        </div>

        <div class="row">
            <!-- Job Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Job Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Job Code</label>
                                    <p class="fw-semibold">{{ $photographerVisitJob->job_code }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <p>
                                        <span class="badge bg-{{ $photographerVisitJob->status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $photographerVisitJob->status)) }}
                                        </span>
                                        @if($photographerVisitJob->isOverdue())
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Priority</label>
                                    <p>
                                        <span class="badge bg-{{ $photographerVisitJob->priority_color }}">
                                            {{ ucfirst($photographerVisitJob->priority) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Scheduled Date</label>
                                    <p class="fw-semibold">
                                        {{ $photographerVisitJob->scheduled_date ? $photographerVisitJob->scheduled_date->format('d M Y, h:i A') : 'Not scheduled' }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Assigned At</label>
                                    <p>{{ $photographerVisitJob->assigned_at ? $photographerVisitJob->assigned_at->format('d M Y, h:i A') : 'Not assigned yet' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Estimated Duration</label>
                                    <p>{{ $photographerVisitJob->estimated_duration ? $photographerVisitJob->estimated_duration . ' minutes' : 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>

                        @if($photographerVisitJob->instructions)
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="ri-information-line me-1"></i> Instructions</h6>
                                <p class="mb-0">{{ $photographerVisitJob->instructions }}</p>
                            </div>
                        @endif

                        @if($photographerVisitJob->special_requirements)
                            <div class="alert alert-warning">
                                <h6 class="mb-2"><i class="ri-alert-line me-1"></i> Special Requirements</h6>
                                <p class="mb-0">{{ $photographerVisitJob->special_requirements }}</p>
                            </div>
                        @endif

                        @if($photographerVisitJob->notes)
                            <div class="alert alert-secondary">
                                <h6 class="mb-2"><i class="ri-sticky-note-line me-1"></i> Notes</h6>
                                <p class="mb-0">{{ $photographerVisitJob->notes }}</p>
                            </div>
                        @endif

                        @if($photographerVisitJob->cancellation_reason)
                            <div class="alert alert-danger">
                                <h6 class="mb-2"><i class="ri-close-circle-line me-1"></i> Cancellation Reason</h6>
                                <p class="mb-0">{{ $photographerVisitJob->cancellation_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Booking Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Booking ID</label>
                                    <p class="fw-semibold">#{{ $photographerVisitJob->booking->id }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Client</label>
                                    <p>{{ $photographerVisitJob->booking->user->name ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Property Type</label>
                                    <p>{{ $photographerVisitJob->booking->propertyType?->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Area</label>
                                    <p>{{ $photographerVisitJob->booking->area ?? 'N/A' }} sq ft</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Address</label>
                                    <p>{{ $photographerVisitJob->booking->full_address ?? 'No address provided' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Booking Status</label>
                                    <p>
                                        <span class="badge bg-soft-primary text-primary">
                                            {{ ucfirst($photographerVisitJob->booking->status) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visits -->
                @if($photographerVisitJob->visits->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Visit History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Visit Date</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($photographerVisitJob->visits as $visit)
                                            <tr>
                                                <td>{{ $visit->visit_date ? $visit->visit_date->format('d M Y') : 'N/A' }}</td>
                                                <td>{{ $visit->checkIn?->check_in_time ?? 'N/A' }}</td>
                                                <td>{{ $visit->checkOut?->check_out_time ?? 'N/A' }}</td>
                                                <td>
                                                    @if($visit->getDuration())
                                                        {{ $visit->getDuration() }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-secondary text-secondary">
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

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Photographer Info -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Photographer</h4>
                    </div>
                    <div class="card-body">
                        @if($photographerVisitJob->photographer)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm me-3">
                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-4">
                                        {{ strtoupper(substr($photographerVisitJob->photographer->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $photographerVisitJob->photographer->name }}</h5>
                                    <p class="text-muted mb-0">{{ $photographerVisitJob->photographer->email }}</p>
                                    @if($photographerVisitJob->photographer->mobile)
                                        <p class="text-muted mb-0">
                                            <i class="ri-phone-line me-1"></i>{{ $photographerVisitJob->photographer->mobile }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @can('photographer_visit_job_assign')
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#assignPhotographerModal">
                                    <i class="ri-user-line me-1"></i> Reassign Photographer
                                </button>
                            @endcan
                        @else
                            <div class="text-center py-3">
                                <i class="ri-user-line fs-1 text-muted mb-2"></i>
                                <p class="text-muted mb-3">No photographer assigned yet</p>
                                @can('photographer_visit_job_assign')
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignPhotographerModal">
                                        <i class="ri-user-add-line me-1"></i> Assign Photographer
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Timeline</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @if($photographerVisitJob->created_at)
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="ri-checkbox-circle-line text-secondary fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Created</h6>
                                            <p class="text-muted mb-0">{{ $photographerVisitJob->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            
                            @if($photographerVisitJob->assigned_at)
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="ri-user-add-line text-info fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Assigned</h6>
                                            <p class="text-muted mb-0">{{ $photographerVisitJob->assigned_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            
                            @if($photographerVisitJob->started_at)
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="ri-play-circle-line text-primary fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Started</h6>
                                            <p class="text-muted mb-0">{{ $photographerVisitJob->started_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            
                            @if($photographerVisitJob->completed_at)
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="ri-checkbox-circle-line text-success fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Completed</h6>
                                            <p class="text-muted mb-0">{{ $photographerVisitJob->completed_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                @can('photographer_visit_job_delete')
                    <div class="card border-danger">
                        <div class="card-header bg-soft-danger">
                            <h4 class="card-title mb-0 text-danger">Danger Zone</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Once you delete this job, there is no going back.</p>
                            <form action="{{ route('admin.photographer-visit-jobs.destroy', $photographerVisitJob) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this job?')">
                                    <i class="ri-delete-bin-line me-1"></i> Delete Job
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Assign Photographer Modal -->
@can('photographer_visit_job_assign')
<div class="modal fade" id="assignPhotographerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.photographer-visit-jobs.assign', $photographerVisitJob) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Photographer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photographer_id" class="form-label">Select Photographer <span class="text-danger">*</span></label>
                        <select name="photographer_id" id="photographer_id" class="form-select" required>
                            <option value="">Choose a photographer</option>
                            @foreach(\App\Models\User::role('photographer')->get() as $photographer)
                                <option value="{{ $photographer->id }}" {{ $photographerVisitJob->photographer_id == $photographer->id ? 'selected' : '' }}>
                                    {{ $photographer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
