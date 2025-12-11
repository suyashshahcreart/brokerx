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
                    @foreach($photographerVisitJob->visits as $visit)
                        <!-- Check-in Details -->
                        @if($visit->checked_in_at)
                            <div class="card">
                                <div class="card-header bg-soft-success">
                                    <h4 class="card-title mb-0">
                                        <i class="ri-login-circle-line me-2"></i>Check-In Details
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Checked In At</label>
                                                        <p class="fw-semibold">{{ $visit->checked_in_at->format('d M Y, h:i A') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Location</label>
                                                        @if($visit->check_in_location)
                                                            <p>
                                                                <a href="https://www.google.com/maps?q={{ $visit->check_in_location }}" target="_blank" class="text-decoration-none">
                                                                    {{ $visit->check_in_location }} <i class="ri-external-link-line"></i>
                                                                </a>
                                                            </p>
                                                        @else
                                                            <p class="text-muted">Not recorded</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    @if($visit->check_in_metadata)
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted">Location Accuracy</label>
                                                            <p>{{ $visit->check_in_metadata['location_accuracy'] ?? '-' }} meters</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted">Location Source</label>
                                                            <p><span class="badge bg-secondary">{{ strtoupper($visit->check_in_metadata['location_source'] ?? 'Unknown') }}</span></p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($visit->check_in_remarks)
                                                <div class="alert alert-info mb-0">
                                                    <h6 class="mb-2"><i class="ri-sticky-note-line me-1"></i> Check-In Remarks</h6>
                                                    <p class="mb-0">{{ $visit->check_in_remarks }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            @if($visit->check_in_photo)
                                                <label class="form-label text-muted">Check-In Photo</label>
                                                <img src="{{ asset('storage/' . $visit->check_in_photo) }}" 
                                                     alt="Check-in photo" 
                                                     class="img-fluid rounded border"
                                                     style="cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            @else
                                                <p class="text-muted">No photo captured</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Check-out Details -->
                        @if($visit->checked_out_at)
                            <div class="card">
                                <div class="card-header bg-soft-warning">
                                    <h4 class="card-title mb-0">
                                        <i class="ri-logout-circle-line me-2"></i>Check-Out Details
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Checked Out At</label>
                                                        <p class="fw-semibold">{{ $visit->checked_out_at->format('d M Y, h:i A') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Location</label>
                                                        @if($visit->check_out_location)
                                                            <p>
                                                                <a href="https://www.google.com/maps?q={{ $visit->check_out_location }}" target="_blank" class="text-decoration-none">
                                                                    {{ $visit->check_out_location }} <i class="ri-external-link-line"></i>
                                                                </a>
                                                            </p>
                                                        @else
                                                            <p class="text-muted">Not recorded</p>
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Duration</label>
                                                        @if($visit->getDuration())
                                                            @php
                                                                $duration = $visit->getDuration();
                                                                $hours = floor($duration / 60);
                                                                $minutes = $duration % 60;
                                                            @endphp
                                                            <p class="fw-semibold">
                                                                @if($hours > 0)
                                                                    {{ $hours }} hr {{ $minutes }} min
                                                                @else
                                                                    {{ $minutes }} min
                                                                @endif
                                                            </p>
                                                        @else
                                                            <p class="text-muted">-</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    @if($visit->check_out_metadata)
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted">Location Accuracy</label>
                                                            <p>{{ $visit->check_out_metadata['location_accuracy'] ?? '-' }} meters</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted">Location Source</label>
                                                            <p><span class="badge bg-secondary">{{ strtoupper($visit->check_out_metadata['location_source'] ?? 'Unknown') }}</span></p>
                                                        </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted">Photos Taken</label>
                                                        <p class="fw-semibold">{{ $visit->photos_taken ?? 0 }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($visit->work_summary)
                                                <div class="alert alert-success mb-2">
                                                    <h6 class="mb-2"><i class="ri-file-list-line me-1"></i> Work Summary</h6>
                                                    <p class="mb-0">{{ $visit->work_summary }}</p>
                                                </div>
                                            @endif
                                            @if($visit->check_out_remarks)
                                                <div class="alert alert-info mb-0">
                                                    <h6 class="mb-2"><i class="ri-sticky-note-line me-1"></i> Check-Out Remarks</h6>
                                                    <p class="mb-0">{{ $visit->check_out_remarks }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            @if($visit->check_out_photo)
                                                <label class="form-label text-muted">Check-Out Photo</label>
                                                <img src="{{ asset('storage/' . $visit->check_out_photo) }}" 
                                                     alt="Check-out photo" 
                                                     class="img-fluid rounded border"
                                                     style="cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            @else
                                                <p class="text-muted">No photo captured</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Visit Summary -->
                        @if($visit->checked_in_at && $visit->checked_out_at)
                            <div class="card border-success">
                                <div class="card-header bg-soft-success">
                                    <h4 class="card-title mb-0">
                                        <i class="ri-bar-chart-box-line me-2"></i>Visit Summary
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <i class="ri-time-line text-primary fs-1 mb-2"></i>
                                                <h5 class="mb-1">
                                                    @php
                                                        $duration = $visit->getDuration();
                                                        $hours = floor($duration / 60);
                                                        $minutes = $duration % 60;
                                                    @endphp
                                                    @if($hours > 0)
                                                        {{ $hours }}h {{ $minutes }}m
                                                    @else
                                                        {{ $minutes }}m
                                                    @endif
                                                </h5>
                                                <p class="text-muted mb-0">Total Duration</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <i class="ri-camera-line text-success fs-1 mb-2"></i>
                                                <h5 class="mb-1">{{ $visit->photos_taken ?? 0 }}</h5>
                                                <p class="text-muted mb-0">Photos Taken</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <i class="ri-login-circle-line text-info fs-1 mb-2"></i>
                                                <h5 class="mb-1">{{ $visit->checked_in_at->format('h:i A') }}</h5>
                                                <p class="text-muted mb-0">Check-In Time</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <i class="ri-logout-circle-line text-warning fs-1 mb-2"></i>
                                                <h5 class="mb-1">{{ $visit->checked_out_at->format('h:i A') }}</h5>
                                                <p class="text-muted mb-0">Check-Out Time</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-calendar-line text-muted fs-1 mb-3"></i>
                            <p class="text-muted mb-0">No visits recorded yet</p>
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
