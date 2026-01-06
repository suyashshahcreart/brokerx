@extends('admin.layouts.vertical', ['title' => 'Edit Job'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.photographer-visit-jobs.index') }}">Photographer Jobs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $photographerVisitJob->job_code }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Edit Job - {{ $photographerVisitJob->job_code }}</h3>
            </div>
            <div>
                <a href="{{ route('admin.photographer-visit-jobs.show', $photographerVisitJob) }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line me-1"></i> Back to Details
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('admin.photographer-visit-jobs.update', $photographerVisitJob) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Job Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="booking_id" class="form-label">Booking <span class="text-danger">*</span></label>
                                    <select name="booking_id" id="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                                        <option value="">Select a booking</option>
                                        @foreach($bookings as $booking)
                                            <option value="{{ $booking->id }}" {{ old('booking_id', $photographerVisitJob->booking_id) == $booking->id ? 'selected' : '' }}>
                                                #{{ $booking->id }} - {{ $booking->propertyType?->name }} - {{ $booking->user?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('booking_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="photographer_id" class="form-label">Photographer</label>
                                    <select name="photographer_id" id="photographer_id" class="form-select @error('photographer_id') is-invalid @enderror">
                                        <option value="">Not assigned</option>
                                        @foreach($photographers as $photographer)
                                            <option value="{{ $photographer->id }}" {{ old('photographer_id', $photographerVisitJob->photographer_id) == $photographer->id ? 'selected' : '' }}>
                                                {{ $photographer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('photographer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="scheduled_date" id="scheduled_date" 
                                           class="form-control @error('scheduled_date') is-invalid @enderror" 
                                           value="{{ old('scheduled_date', $photographerVisitJob->scheduled_date ? $photographerVisitJob->scheduled_date->format('Y-m-d\TH:i') : '') }}" required>
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estimated_duration" class="form-label">Estimated Duration (minutes)</label>
                                    <input type="number" name="estimated_duration" id="estimated_duration" 
                                           class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           value="{{ old('estimated_duration', $photographerVisitJob->estimated_duration) }}" 
                                           placeholder="e.g., 60" min="1">
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                        <option value="low" {{ old('priority', $photographerVisitJob->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="normal" {{ old('priority', $photographerVisitJob->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority', $photographerVisitJob->priority) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $photographerVisitJob->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $photographerVisitJob->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="assigned" {{ old('status', $photographerVisitJob->status) == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="in_progress" {{ old('status', $photographerVisitJob->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status', $photographerVisitJob->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $photographerVisitJob->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="instructions" class="form-label">Instructions</label>
                                    <textarea name="instructions" id="instructions" rows="3" 
                                              class="form-control @error('instructions') is-invalid @enderror" 
                                              placeholder="Enter special instructions for the photographer">{{ old('instructions', $photographerVisitJob->instructions) }}</textarea>
                                    @error('instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="special_requirements" class="form-label">Special Requirements</label>
                                    <textarea name="special_requirements" id="special_requirements" rows="3" 
                                              class="form-control @error('special_requirements') is-invalid @enderror" 
                                              placeholder="Any special requirements or equipment needed">{{ old('special_requirements', $photographerVisitJob->special_requirements) }}</textarea>
                                    @error('special_requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Additional notes">{{ old('notes', $photographerVisitJob->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3" id="cancellationReasonField" style="display: {{ old('status', $photographerVisitJob->status) == 'cancelled' ? 'block' : 'none' }};">
                                    <label for="cancellation_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" 
                                              class="form-control @error('cancellation_reason') is-invalid @enderror" 
                                              placeholder="Please provide reason for cancellation">{{ old('cancellation_reason', $photographerVisitJob->cancellation_reason) }}</textarea>
                                    @error('cancellation_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.photographer-visit-jobs.show', $photographerVisitJob) }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Update Job
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <!-- Current Job Info -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Current Job Info</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Job Code</small>
                            <p class="fw-semibold mb-0">{{ $photographerVisitJob->job_code }}</p>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted">Current Status</small>
                            <p class="mb-0">
                                <span class="badge bg-{{ $photographerVisitJob->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $photographerVisitJob->status)) }}
                                </span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted">Current Priority</small>
                            <p class="mb-0">
                                <span class="badge bg-{{ $photographerVisitJob->priority_color }}">
                                    {{ ucfirst($photographerVisitJob->priority) }}
                                </span>
                            </p>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted">Created At</small>
                            <p class="mb-0">{{ $photographerVisitJob->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        @if($photographerVisitJob->assigned_at)
                            <hr>
                            <div class="mb-2">
                                <small class="text-muted">Assigned At</small>
                                <p class="mb-0">{{ $photographerVisitJob->assigned_at->format('d M Y, h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Help -->
                <div class="card border-info">
                    <div class="card-header bg-soft-info">
                        <h4 class="card-title mb-0 text-info"><i class="ri-information-line me-1"></i> Help</h4>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3">
                            <li class="mb-2"><small>All fields marked with <span class="text-danger">*</span> are required.</small></li>
                            <li class="mb-2"><small>Changing status to "Assigned" will set the assigned_at timestamp.</small></li>
                            <li class="mb-2"><small>Changing status to "In Progress" will set the started_at timestamp.</small></li>
                            <li class="mb-2"><small>Changing status to "Completed" will set the completed_at timestamp.</small></li>
                            <li class="mb-2"><small>If status is "Cancelled", you must provide a cancellation reason.</small></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const cancellationReasonField = document.getElementById('cancellationReasonField');
        const cancellationReasonTextarea = document.getElementById('cancellation_reason');

        statusSelect.addEventListener('change', function() {
            if (this.value === 'cancelled') {
                cancellationReasonField.style.display = 'block';
                cancellationReasonTextarea.required = true;
            } else {
                cancellationReasonField.style.display = 'none';
                cancellationReasonTextarea.required = false;
            }
        });
    });
</script>
@endpush
@endsection
