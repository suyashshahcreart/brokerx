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
                                <span class="badge bg-{{ $color }} text-uppercase">{{ $photographerVisit->status }}</span>
                            </dd>

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
        @if($photographerVisit->checkIn)
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
                            <dd class="col-sm-8">{{ $photographerVisit->checkIn->checked_in_at->format('d M Y, h:i A') }}</dd>

                            @if($photographerVisit->checkIn->ip_address)
                            <dt class="col-sm-4">IP Address</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkIn->ip_address }}</dd>
                            @endif

                            @if($photographerVisit->checkIn->location)
                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkIn->location }}</dd>
                            @endif

                            @if($photographerVisit->checkIn->remarks)
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkIn->remarks }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        @if($photographerVisit->checkIn->photo)
                        <div>
                            <label class="form-label fw-bold">Check-In Photo</label>
                            <div class="border rounded p-2">
                                <img src="{{ $photographerVisit->checkIn->photo_url }}" alt="Check-in Photo" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Check-Out Details -->
        @if($photographerVisit->checkOut)
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
                            <dd class="col-sm-8">{{ $photographerVisit->checkOut->checked_out_at->format('d M Y, h:i A') }}</dd>

                            @if($photographerVisit->checkIn && $photographerVisit->checkOut)
                            <dt class="col-sm-4">Duration</dt>
                            <dd class="col-sm-8">
                                @php
                                    $duration = $photographerVisit->getDuration();
                                    $hours = floor($duration / 60);
                                    $minutes = $duration % 60;
                                @endphp
                                <strong>{{ $hours > 0 ? "{$hours} hours " : "" }}{{ $minutes }} minutes</strong>
                            </dd>
                            @endif

                            <dt class="col-sm-4">Photos Taken</dt>
                            <dd class="col-sm-8"><span class="badge bg-info">{{ $photographerVisit->checkOut->photos_taken }}</span></dd>

                            @if($photographerVisit->checkOut->ip_address)
                            <dt class="col-sm-4">IP Address</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkOut->ip_address }}</dd>
                            @endif

                            @if($photographerVisit->checkOut->location)
                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkOut->location }}</dd>
                            @endif

                            @if($photographerVisit->checkOut->work_summary)
                            <dt class="col-sm-4">Work Summary</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkOut->work_summary }}</dd>
                            @endif

                            @if($photographerVisit->checkOut->remarks)
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8">{{ $photographerVisit->checkOut->remarks }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        @if($photographerVisit->checkOut->photo)
                        <div>
                            <label class="form-label fw-bold">Check-Out Photo</label>
                            <div class="border rounded p-2">
                                <img src="{{ $photographerVisit->checkOut->photo_url }}" alt="Check-out Photo" class="img-fluid rounded" style="max-height: 300px;">
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
