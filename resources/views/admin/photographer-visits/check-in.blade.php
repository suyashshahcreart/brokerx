@extends('admin.layouts.vertical', ['title' => 'Check In - Visit #' . $photographerVisit->id])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('admin.photographer-visits.show', $photographerVisit) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Visit
                    </a>
                </div>
                <h4 class="page-title">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Check In - Visit #{{ $photographerVisit->id }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <!-- Visit Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="card-title">Visit Details</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Visit ID:</th>
                                        <td>#{{ $photographerVisit->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Booking:</th>
                                        <td>#{{ $photographerVisit->booking_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Photographer:</th>
                                        <td>{{ $photographerVisit->photographer->firstname ?? 'N/A' }} {{ $photographerVisit->photographer->lastname ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Visit Date:</th>
                                        <td>{{ $photographerVisit->visit_date ? $photographerVisit->visit_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($photographerVisit->status) }}
                                            </span>
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
                                        <td>{{ $photographerVisit->booking->propertyType->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $photographerVisit->booking->society_name ?? $photographerVisit->booking->address_area }}</td>
                                    </tr>
                                    <tr>
                                        <th>City:</th>
                                        <td>{{ $photographerVisit->booking->city->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Notes:</th>
                                        <td>{{ $photographerVisit->notes ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Check-in Form -->
                    <form action="{{ route('admin.photographer-visits.check-in', $photographerVisit) }}" method="POST" enctype="multipart/form-data">
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
                                    <div class="form-text">This will help track where you started the visit</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Check-in Photo <small class="text-muted">(Optional)</small></label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                    <div class="form-text">Upload a photo to verify your check-in location</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks <small class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Any remarks about the check-in..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.photographer-visits.show', $photographerVisit) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Check In & Start Visit
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
                        <i class="bi bi-info-circle me-2"></i>Check-in Information
                    </h5>
                    <div class="alert alert-info">
                        <strong>What happens when you check in?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Visit status changes to "Checked In"</li>
                            <li>Start time is recorded</li>
                            <li>You can begin working on the visit</li>
                            <li>Location and photo are saved for verification</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Make sure you're at the correct property location</li>
                            <li>Take a clear photo if required</li>
                            <li>Check-in cannot be undone</li>
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