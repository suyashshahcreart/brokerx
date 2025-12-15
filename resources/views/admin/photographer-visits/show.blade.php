@extends('admin.layouts.vertical', ['title' => 'Visit Details', 'subTitle' => 'Management'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.photographer-visits.index') }}">Photographer Visits</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $photographerVisit->id }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Visit #{{ $photographerVisit->id }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.photographer-visits.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.photographer-visits.edit', $photographerVisit) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i> Edit
                </a>
            </div>
        </div>

        <!-- Visit Overview -->
        <div class="card panel-card border-primary border-top mb-3" data-panel-card>
            <div class="card-header">
                <h4 class="card-title mb-0">Visit Overview</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h5 class="mb-3">Basic Information</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Visit ID</dt>
                            <dd class="col-sm-8">#{{ $photographerVisit->id }}</dd>

                            <dt class="col-sm-4">Photographer</dt>
                            <dd class="col-sm-8">
                                {{ $photographerVisit->photographer->firstname }} {{ $photographerVisit->photographer->lastname }}
                                <div class="text-muted small">{{ $photographerVisit->photographer->email }}</div>
                                <div class="text-muted small">{{ $photographerVisit->photographer->mobile }}</div>
                            </dd>

                            <dt class="col-sm-4">Booking</dt>
                            <dd class="col-sm-8">
                                <a href="{{ route('admin.bookings.show', $photographerVisit->booking) }}" class="text-decoration-none">
                                    #{{ $photographerVisit->booking->id }}
                                </a>
                                <div class="text-muted small">{{ $photographerVisit->booking->society_name ?? $photographerVisit->booking->address_area }}</div>
                            </dd>

                            <dt class="col-sm-4">Visit Date</dt>
                            <dd class="col-sm-8">{{ optional($photographerVisit->visit_date)->format('d M Y, h:i A') ?? '-' }}</dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @php
                                    $badges = [
                                        'pending' => 'secondary',
                                        'checked_in' => 'info',
                                        'checked_out' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $badges[$photographerVisit->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucwords(str_replace('_', ' ', $photographerVisit->status)) }}</span>
                            </dd>

                            @if($photographerVisit->job)
                            <dt class="col-sm-4">Job Code</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-dark">{{ $photographerVisit->job->job_code }}</span>
                            </dd>
                            @endif

                            @if($photographerVisit->cancel_reason)
                            <dt class="col-sm-4">Cancel Reason</dt>
                            <dd class="col-sm-8">
                                <div class="alert alert-danger mb-0">{{ $photographerVisit->cancel_reason }}</div>
                            </dd>
                            @endif

                            @if($photographerVisit->notes)
                            <dt class="col-sm-4">Notes</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->notes }}</dd>
                            @endif
                        </dl>
                    </div>

                    <div class="col-lg-6">
                        <h5 class="mb-3">Property Details</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Property Type</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->booking->propertyType?->name ?? '-' }} / {{ $photographerVisit->booking->propertySubType?->name ?? '-' }}</dd>

                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">
                                {{ $photographerVisit->booking->city?->name ?? '-' }}, {{ $photographerVisit->booking->state?->name ?? '-' }}
                                <div class="text-muted small">{{ $photographerVisit->booking->full_address }}</div>
                            </dd>

                            <dt class="col-sm-4">Area</dt>
                            <dd class="col-sm-8">{{ number_format($photographerVisit->booking->area) }} sq ft</dd>

                            <dt class="col-sm-4">Created At</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->created_at->format('d M Y, h:i A') }}</dd>

                            <dt class="col-sm-4">Updated At</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->updated_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check-In Details -->
        @if($photographerVisit->checked_in_at)
        <div class="card panel-card border-success border-top mb-3" data-panel-card>
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="ri-login-circle-line text-success fs-4 me-2"></i>
                    <h4 class="card-title mb-0">Check-In Details</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Checked In At</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checked_in_at->format('d M Y, h:i A') }}</dd>

                            @if($photographerVisit->check_in_ip_address)
                            <dt class="col-sm-4">IP Address</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_in_ip_address }}</dd>
                            @endif

                            @if($photographerVisit->check_in_location)
                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">
                                <a href="https://www.google.com/maps?q={{ $photographerVisit->check_in_location }}" target="_blank" class="text-decoration-none">
                                    {{ $photographerVisit->check_in_location }} <i class="ri-external-link-line"></i>
                                </a>
                            </dd>
                            @endif

                            @if($photographerVisit->check_in_metadata)
                            <dt class="col-sm-4">Location Accuracy</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_in_metadata['location_accuracy'] ?? '-' }} meters</dd>

                            <dt class="col-sm-4">Location Source</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-secondary">{{ strtoupper($photographerVisit->check_in_metadata['location_source'] ?? 'Unknown') }}</span>
                            </dd>
                            @endif

                            @if($photographerVisit->check_in_device_info)
                            <dt class="col-sm-4">Device Info</dt>
                            <dd class="col-sm-8"><small class="text-muted">{{ Str::limit($photographerVisit->check_in_device_info, 60) }}</small></dd>
                            @endif

                            @if($photographerVisit->check_in_remarks)
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_in_remarks }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        @if($photographerVisit->check_in_photo)
                        <div>
                            <label class="form-label fw-bold">Check-In Photo</label>
                            <div class="border rounded p-2">
                                <img src="{{ asset('storage/' . $photographerVisit->check_in_photo) }}" 
                                     alt="Check-in Photo" 
                                     class="img-fluid rounded" 
                                     style="max-height: 300px; cursor: pointer;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Check-Out Details -->
        @if($photographerVisit->checked_out_at)
        <div class="card panel-card border-warning border-top mb-3" data-panel-card>
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="ri-logout-circle-line text-warning fs-4 me-2"></i>
                    <h4 class="card-title mb-0">Check-Out Details</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Checked Out At</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checked_out_at->format('d M Y, h:i A') }}</dd>

                            @if($photographerVisit->checked_in_at && $photographerVisit->checked_out_at)
                            <dt class="col-sm-4">Duration</dt>
                            <dd class="col-sm-8">
                                @php
                                    $duration = $photographerVisit->getDuration();
                                    $hours = floor($duration / 60);
                                    $minutes = $duration % 60;
                                @endphp
                                <strong class="text-primary">{{ $hours > 0 ? "{$hours}h " : "" }}{{ $minutes }}m</strong>
                            </dd>
                            @endif

                            @if($photographerVisit->photos_taken)
                            <dt class="col-sm-4">Photos Taken</dt>
                            <dd class="col-sm-8"><span class="badge bg-info fs-6">{{ $photographerVisit->photos_taken }} photos</span></dd>
                            @endif

                            @if($photographerVisit->check_out_ip_address)
                            <dt class="col-sm-4">IP Address</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_out_ip_address }}</dd>
                            @endif

                            @if($photographerVisit->check_out_location)
                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">
                                <a href="https://www.google.com/maps?q={{ $photographerVisit->check_out_location }}" target="_blank" class="text-decoration-none">
                                    {{ $photographerVisit->check_out_location }} <i class="ri-external-link-line"></i>
                                </a>
                            </dd>
                            @endif

                            @if($photographerVisit->check_out_metadata)
                            <dt class="col-sm-4">Location Accuracy</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_out_metadata['location_accuracy'] ?? '-' }} meters</dd>

                            <dt class="col-sm-4">Location Source</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-secondary">{{ strtoupper($photographerVisit->check_out_metadata['location_source'] ?? 'Unknown') }}</span>
                            </dd>
                            @endif

                            @if($photographerVisit->work_summary)
                            <dt class="col-sm-4">Work Summary</dt>
                            <dd class="col-sm-8">
                                <div class="alert alert-info mb-0">{{ $photographerVisit->work_summary }}</div>
                            </dd>
                            @endif

                            @if($photographerVisit->check_out_device_info)
                            <dt class="col-sm-4">Device Info</dt>
                            <dd class="col-sm-8"><small class="text-muted">{{ Str::limit($photographerVisit->check_out_device_info, 60) }}</small></dd>
                            @endif

                            @if($photographerVisit->check_out_remarks)
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->check_out_remarks }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        @if($photographerVisit->check_out_photo)
                        <div>
                            <label class="form-label fw-bold">Check-Out Photo</label>
                            <div class="border rounded p-2">
                                <img src="{{ asset('storage/' . $photographerVisit->check_out_photo) }}" 
                                     alt="Check-out Photo" 
                                     class="img-fluid rounded" 
                                     style="max-height: 300px; cursor: pointer;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Metadata -->
        @if($photographerVisit->metadata && count($photographerVisit->metadata) > 0)
        <div class="card panel-card border-info border-top" data-panel-card>
            <div class="card-header">
                <h4 class="card-title mb-0">Additional Metadata</h4>
            </div>
            <div class="card-body">
                <pre class="mb-0">{{ json_encode($photographerVisit->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
