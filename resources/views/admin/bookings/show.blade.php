@extends('admin.layouts.vertical', ['title' => 'Booking Details', 'subTitle' => 'Property'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $booking->id }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Booking #{{ $booking->id }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-primary"><i class="ri-edit-line me-1"></i> Edit</a>
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header">
                <h4 class="card-title mb-0">Overview</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h5 class="mb-3">Primary</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">User</dt>
                            <dd class="col-sm-8">{{ $booking->user?->firstname }} {{ $booking->user?->lastname }} ({{ $booking->user?->email }})</dd>

                            <dt class="col-sm-4">Type</dt>
                            <dd class="col-sm-8">{{ $booking->propertyType?->name }} / {{ $booking->propertySubType?->name }}</dd>

                            <dt class="col-sm-4">BHK</dt>
                            <dd class="col-sm-8">{{ $booking->bhk?->name ?? '-' }}</dd>

                            <dt class="col-sm-4">Furniture</dt>
                            <dd class="col-sm-8">{{ $booking->furniture_type ?? '-' }}</dd>

                            @if($booking->other_option_details)
                            <dt class="col-sm-4">Other Option Details</dt>
                            <dd class="col-sm-8">{{ $booking->other_option_details }}</dd>
                            @endif

                            @if($booking->firm_name)
                            <dt class="col-sm-4">Firm Name</dt>
                            <dd class="col-sm-8">{{ $booking->firm_name }}</dd>
                            @endif

                            @if($booking->gst_no)
                            <dt class="col-sm-4">GST No</dt>
                            <dd class="col-sm-8">{{ $booking->gst_no }}</dd>
                            @endif

                            <dt class="col-sm-4">Area</dt>
                            <dd class="col-sm-8">{{ number_format($booking->area) }} sq ft</dd>

                            <dt class="col-sm-4">Price</dt>
                            <dd class="col-sm-8">₹ {{ number_format($booking->price) }}</dd>
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="mb-3">Status</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Booking Date</dt>
                            <dd class="col-sm-8">{{ optional($booking->booking_date)->format('Y-m-d') ?? '-' }}</dd>

                            <dt class="col-sm-4">Payment</dt>
                            <dd class="col-sm-8"><span class="badge bg-info">{{ $booking->payment_status }}</span></dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8"><span class="badge bg-secondary">{{ $booking->status }}</span></dd>

                            <dt class="col-sm-4">Created By</dt>
                            <dd class="col-sm-8">{{ $booking->creator?->firstname }} {{ $booking->creator?->lastname }}</dd>

                            @if($booking->tour_code)
                            <dt class="col-sm-4">Tour Code</dt>
                            <dd class="col-sm-8">{{ $booking->tour_code }}</dd>
                            @endif

                            @if($booking->tour_final_link)
                            <dt class="col-sm-4">Tour Final Link</dt>
                            <dd class="col-sm-8"><a href="{{ $booking->tour_final_link }}" target="_blank" class="text-primary">{{ $booking->tour_final_link }}</a></dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Address</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="mb-2"><strong>House No:</strong> {{ $booking->house_no ?? '-' }}</div>
                        <div class="mb-2"><strong>Building:</strong> {{ $booking->building ?? '-' }}</div>
                        <div class="mb-2"><strong>Society:</strong> {{ $booking->society_name ?? '-' }}</div>
                        <div class="mb-2"><strong>Area:</strong> {{ $booking->address_area ?? '-' }}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-2"><strong>Landmark:</strong> {{ $booking->landmark ?? '-' }}</div>
                        <div class="mb-2"><strong>City:</strong> {{ $booking->city?->name ?? '-' }}</div>
                        <div class="mb-2"><strong>State:</strong> {{ $booking->state?->name ?? '-' }}</div>
                        <div class="mb-2"><strong>PIN:</strong> {{ $booking->pin_code ?? '-' }}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-2"><strong>Full Address:</strong><br>{{ $booking->full_address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
