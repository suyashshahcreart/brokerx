@extends('admin.layouts.vertical', ['title' => 'Check Out - ' . $photographerVisitJob->job_code])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin.photographer-visit-jobs.show', $photographerVisitJob) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Job
                    </a>
                </div>
                <h4 class="page-title">
                    <i class="bi bi-box-arrow-left me-2"></i>Check Out - {{ $photographerVisitJob->job_code }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <!-- Job Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="card-title">Job Details</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Job Code:</th>
                                        <td>{{ $photographerVisitJob->job_code }}</td>
                                    </tr>
                                    <tr>
                                        <th>Booking:</th>
                                        <td>#{{ $photographerVisitJob->booking_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Photographer:</th>
                                        <td>{{ $photographerVisitJob->photographer->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Started At:</th>
                                        <td>{{ $photographerVisitJob->started_at ? $photographerVisitJob->started_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td>
                                            @if($photographerVisitJob->started_at)
                                                @php
                                                    $duration = now()->diff($photographerVisitJob->started_at);
                                                    $hours = $duration->h + ($duration->days * 24);
                                                    $minutes = $duration->i;
                                                @endphp
                                                {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Property Information</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Property Type:</th>
                                        <td>{{ $photographerVisitJob->booking->propertyType->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $photographerVisitJob->booking->society_name ?? $photographerVisitJob->booking->address_area }}</td>
                                    </tr>
                                    <tr>
                                        <th>City:</th>
                                        <td>{{ $photographerVisitJob->booking->city->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Instructions:</th>
                                        <td>{{ $photographerVisitJob->instructions ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Check-out Form -->
                    <form action="{{ route('admin.photographer-visit-jobs.check-out', $photographerVisitJob) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Current Location <small class="text-muted">(Optional)</small></label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Enter your current location">
                                    <div class="form-text">This will help track where you completed the job</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="photos_taken" class="form-label">Photos Taken</label>
                                    <input type="number" class="form-control" id="photos_taken" name="photos_taken" min="0" placeholder="Number of photos taken">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="work_summary" class="form-label">Work Summary <small class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="work_summary" name="work_summary" rows="3" placeholder="Describe the work completed..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Check-out Photo <small class="text-muted">(Optional)</small></label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <div class="form-text">Upload a photo to verify your check-out location</div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks <small class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Any final remarks..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.photographer-visit-jobs.show', $photographerVisitJob) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-box-arrow-left me-1"></i> Check Out & Complete Job
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Check-out Information
                    </h5>
                    <div class="alert alert-info">
                        <strong>What happens when you check out?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Job status changes to "Completed"</li>
                            <li>End time is recorded</li>
                            <li>Job duration is calculated</li>
                            <li>Work summary and photos are saved</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Make sure all work is completed</li>
                            <li>Provide accurate photo count</li>
                            <li>Check-out cannot be undone</li>
                            <li>Job will be marked as completed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get current location if supported
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const locationInput = document.getElementById('location');
            if (!locationInput.value) {
                locationInput.value = `${position.coords.latitude}, ${position.coords.longitude}`;
            }
        }, function(error) {
            console.log('Geolocation error:', error);
        });
    }
});
</script>
@endpush