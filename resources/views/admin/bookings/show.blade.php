@extends('admin.layouts.vertical', ['title' => 'Booking Details', 'subTitle' => 'Property'])

@section('css')
<!-- Font Awesome for dynamic icons from database -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Pill and Chip Styles - Read-only version */
    .top-pill, .chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 18px;
        margin: 2px;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        background-color: #fff;
        font-size: 13px;
        font-weight: 500;
        user-select: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .top-pill.active, .chip.active {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border-color: #0d6efd;
        color: #fff;
        box-shadow: 0 3px 6px rgba(13, 110, 253, 0.3);
    }
    
    .top-pill i, .chip i {
        margin-right: 6px;
        font-size: 16px;
    }
    
    .top-pill.active i, .chip.active i {
        color: #fff !important;
    }
    
    .d-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    
    .section-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        margin-top: 12px;
        color: #2c3e50;
    }
    
    #propertyTypeContainer .top-pill {
        padding: 10px 22px;
        font-size: 14px;
        font-weight: 600;
        min-width: 140px;
    }
    
    #ownerTypeContainer .top-pill {
        min-width: 120px;
    }
    
    .card.border.bg-light-subtle {
        border: 1px solid #e3e6f0 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .card-header.bg-primary-subtle {
        background: linear-gradient(135deg, #e7f1ff 0%, #d3e5ff 100%) !important;
        border-bottom: 2px solid #0d6efd !important;
    }
    
    .card-header.bg-success-subtle {
        background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%) !important;
        border-bottom: 2px solid #198754 !important;
    }
    
    .card-header.bg-warning-subtle {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%) !important;
        border-bottom: 2px solid #ffc107 !important;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 14px;
        color: #2c3e50;
        margin-bottom: 16px;
    }
    
    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }
    
    /* Action Sidebar Styles */
    .sticky-top {
        position: sticky;
        z-index: 10;
    }
    
    .card-header.bg-dark {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
    }
    
    .form-select-sm {
        font-size: 12px;
        padding: 6px 10px;
    }
    
    .btn-sm {
        padding: 8px 12px;
        font-size: 13px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    
    /* Highlighted Info Cards */
    .card.bg-primary-subtle {
        background: linear-gradient(135deg, #e7f1ff 0%, #d3e5ff 100%) !important;
    }
    
    .card.bg-success-subtle {
        background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%) !important;
    }
    
    .card.bg-warning-subtle {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%) !important;
    }
    
    .card.bg-danger-subtle {
        background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%) !important;
    }
    
    .card.bg-info-subtle {
        background: linear-gradient(135deg, #cff4fc 0%, #9eeaf9 100%) !important;
    }
    
    .card.bg-secondary-subtle {
        background: linear-gradient(135deg, #e2e3e5 0%, #d3d4d6 100%) !important;
    }
    
    /* Height matching */
    .h-100 {
        height: 100% !important;
    }
    
    /* Avatar */
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    
    .fs-3 {
        font-size: 1.75rem !important;
    }
    
    /* Vertical rule separator */
    .vr {
        display: inline-block;
        align-self: stretch;
        width: 1px;
        min-height: 1em;
        background-color: currentColor;
        opacity: 0.25;
    }
    
    /* Schedule Modal Input Styling */
    #scheduleModal .input-group-text {
        border-radius: 0.375rem 0 0 0.375rem;
        transition: background-color 0.2s ease;
    }
    
    #scheduleModal .input-group-text:hover {
        background-color: #0b5ed7 !important;
    }
    
    #scheduleModal .input-group .form-control {
        border-left: 0;
    }
    
    #scheduleModal .input-group .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
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

        <div class="row">
            <!-- Main Content (col-9) -->
            <div class="col-lg-9">
            <!-- User Information -->
            <div class="mb-3">
                <div class="card border bg-light-subtle">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="ri-user-line fs-3 text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $booking->user?->firstname }} {{ $booking->user?->lastname }}</h5>
                                <div class="d-flex gap-3">
                                    <small class="text-muted"><i class="ri-mail-line me-1"></i>{{ $booking->user?->email ?? '-' }}</small>
                                    <small class="text-muted"><i class="ri-phone-line me-1"></i>{{ $booking->user?->mobile ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Property Details -->
                <div class="col-lg-6 mb-3">
                    <div class="card border bg-light-subtle h-100">
                        <div class="card-header bg-primary-subtle border-primary">
                            <h5 class="card-title mb-0"><i class="ri-building-line me-2"></i>Property Details</h5>
                        </div>
                        <div class="card-body">
                        @php
                            $currentPropertyType = $booking->propertyType->name ?? 'Residential';
                            $propertyTypeOrder = [
                                'Residential' => ['key' => 'res', 'icon' => 'ri-home-4-line', 'type' => 'ri'],
                                'Commercial'  => ['key' => 'com', 'icon' => 'ri-building-line', 'type' => 'ri'],
                                'Other'       => ['key' => 'oth', 'icon' => 'fa-ellipsis', 'type' => 'fa'],
                            ];
                        @endphp

                        <div class="row">
                            <div class="col-4">
                                <!-- Owner Type -->
                                <div class="mb-2">
                                    <div class="info-label">Owner Type</div>
                                    <div class="info-value">
                                        @if($booking->owner_type == 'Owner')
                                            <div class="top-pill active" style="cursor: default;">
                                                <i class="ri-user-line me-1"></i> Owner
                                            </div>
                                        @elseif($booking->owner_type == 'Broker')
                                            <div class="top-pill active" style="cursor: default;">
                                                <i class="ri-briefcase-line me-1"></i> Broker
                                            </div>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <!-- Property Type -->
                                <div class="mb-2">
                                    <div class="info-label">Property Type</div>
                                    <div class="info-value">
                                        @php
                                            $config = $propertyTypeOrder[$currentPropertyType] ?? null;
                                        @endphp
                                        @if($config)
                                            <div class="top-pill active" style="cursor: default;">
                                                @if($config['type'] === 'ri')
                                                    <i class="{{ $config['icon'] }} me-1"></i>
                                                @else
                                                    <i class="fa-solid {{ $config['icon'] }} me-1"></i>
                                                @endif
                                                {{ $currentPropertyType }}
                                            </div>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-label"> Sub Type</div>
                                <div class="info-value">
                                    <div class="top-pill active" style="cursor: default;">
                                        @if($booking->propertySubType?->icon)
                                            @php
                                                $iconClass = str_starts_with($booking->propertySubType->icon, 'fa-') 
                                                    ? "fa {$booking->propertySubType->icon}" 
                                                    : "fa-solid fa-{$booking->propertySubType->icon}";
                                            @endphp
                                            <i class="{{ $iconClass }} me-1"></i>
                                        @endif
                                        {{ $booking->propertySubType?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Furnish Type (if applicable) -->
                        @if($booking->furniture_type)
                        <div class="row">
                            <div class="col-6">
                                <div class="info-label">Furnish Type</div>
                                <div class="info-value">
                                    @if($booking->furniture_type == 'Furnished')
                                        <div class="chip active" style="cursor: default;">
                                            <i class="ri-sofa-line me-1"></i> Fully Furnished
                                        </div>
                                    @elseif($booking->furniture_type == 'Semi-Furnished')
                                        <div class="chip active" style="cursor: default;">
                                            <i class="ri-lightbulb-line me-1"></i> Semi Furnished
                                        </div>
                                    @elseif($booking->furniture_type == 'Unfurnished')
                                        <div class="chip active" style="cursor: default;">
                                            <i class="ri-door-line me-1"></i> Unfurnished
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Size (BHK) - For Residential -->
                        @if($booking->bhk_id && $currentPropertyType == 'Residential')
                            <div class="col-6">
                                <div class="info-label">Size (BHK / RK)</div>
                                <div class="info-value">
                                    <div class="chip active" style="cursor: default;">{{ $booking->bhk?->name }}</div>
                                </div>
                            </div>
                        @endif
                        </div>

                        <!-- Other Option Details -->
                        @if($booking->other_option_details)
                        <div class="row">
                            <div class="col-12">
                                <div class="info-label">Other Option Details</div>
                                <div class="info-value">
                                    <div class="alert alert-info border-info mb-0 py-2">
                                        <small>{{ $booking->other_option_details }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Area, Price, Date - HIGHLIGHTED -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card bg-primary-subtle border-primary mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">SUPER BUILT-UP AREA</small>
                                                <h4 class="mb-0 mt-1 text-primary"><i class="ri-ruler-line me-2"></i>{{ number_format($booking->area) }} sq ft</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success-subtle border-success mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">PRICE</small>
                                                <h4 class="mb-0 mt-1 text-success"><i class="ri-money-rupee-circle-line me-2"></i>₹{{ number_format($booking->price) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Billing Details -->
                        @if($booking->firm_name || $booking->gst_no)
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-light border py-2 mb-2" role="alert">
                                    <small class="d-block mb-1" style="font-weight: 600;"><i class="ri-briefcase-line me-1"></i> Company Billing Details</small>
                                    <div class="row">
                                        @if($booking->firm_name)
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 10px;">COMPANY NAME</small>
                                            <small class="d-block">{{ $booking->firm_name }}</small>
                                        </div>
                                        @endif
                                        @if($booking->gst_no)
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 10px;">GST NO</small>
                                            <small class="d-block">{{ $booking->gst_no }}</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Status Information - HIGHLIGHTED -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                @php
                                    $paymentColors = [
                                        'unpaid' => 'secondary',
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'info'
                                    ];
                                    $paymentColor = $paymentColors[$booking->payment_status] ?? 'secondary';
                                @endphp
                                <div class="card bg-{{ $paymentColor }}-subtle border-{{ $paymentColor }} mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="w-100">
                                                <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">PAYMENT STATUS</small>
                                                <h5 class="mb-0 mt-1 text-{{ $paymentColor }}">
                                                    <i class="ri-wallet-line me-2"></i>
                                                    <span class="badge bg-{{ $paymentColor }} badge-custom">{{ ucfirst($booking->payment_status) }}</span>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'cancelled' => 'danger',
                                        'completed' => 'primary'
                                    ];
                                    $statusColor = $statusColors[$booking->status] ?? 'secondary';
                                @endphp
                                <div class="card bg-{{ $statusColor }}-subtle border-{{ $statusColor }} mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="w-100">
                                                <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">BOOKING STATUS</small>
                                                <h5 class="mb-0 mt-1 text-{{ $statusColor }}">
                                                    <i class="ri-bookmark-line me-2"></i>
                                                    <span class="badge bg-{{ $statusColor }} badge-custom">{{ ucfirst($booking->status) }}</span>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tour Information (if exists) -->
                        @if($booking->tour_code || $booking->tour_final_link)
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="alert alert-success border-success py-2 mb-0" role="alert">
                                    <small class="d-block mb-1" style="font-weight: 600;"><i class="ri-camera-line me-1"></i> Tour Information</small>
                                    <div class="d-flex gap-3 align-items-center">
                                        @if($booking->tour_code)
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 10px;">TOUR CODE</small>
                                            <code style="font-size: 12px;">{{ $booking->tour_code }}</code>
                                        </div>
                                        @endif
                                        @if($booking->tour_final_link)
                                        <div>
                                            <a href="{{ $booking->tour_final_link }}" target="_blank" class="btn btn-sm btn-success">
                                                <i class="ri-external-link-line me-1"></i> View Tour
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        </div>
                    </div>
                </div>
                <!-- End Property Details col-6 -->

                <!-- Address Details -->
                <div class="col-lg-6 mb-3">
                    <div class="card border bg-light-subtle h-100">
                        <div class="card-header bg-success-subtle border-success">
                            <h5 class="card-title mb-0"><i class="ri-map-pin-line me-2"></i>Address Details</h5>
                        </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-label">House / Office No</div>
                                <div class="info-value">{{ $booking->house_no ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Building Name</div>
                                <div class="info-value">{{ $booking->building ?? '-' }}</div>
                            </div>
                        </div>

                        @if($booking->society_name || $booking->address_area || $booking->landmark)
                        <div class="row">
                            @if($booking->society_name)
                            <div class="col-{{ $booking->address_area || $booking->landmark ? '4' : '12' }}">
                                <div class="info-label">Society / Complex</div>
                                <div class="info-value">{{ $booking->society_name }}</div>
                            </div>
                            @endif
                            @if($booking->address_area)
                            <div class="col-{{ $booking->society_name && $booking->landmark ? '4' : ($booking->society_name || $booking->landmark ? '6' : '12') }}">
                                <div class="info-label">Area / Locality</div>
                                <div class="info-value">{{ $booking->address_area }}</div>
                            </div>
                            @endif
                            @if($booking->landmark)
                            <div class="col-{{ $booking->society_name && $booking->address_area ? '4' : ($booking->society_name || $booking->address_area ? '6' : '12') }}">
                                <div class="info-label">Landmark</div>
                                <div class="info-value">{{ $booking->landmark }}</div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-4">
                                <div class="info-label">City</div>
                                <div class="info-value">{{ $booking->city?->name ?? '-' }}</div>
                            </div>
                            <div class="col-4">
                                <div class="info-label">State</div>
                                <div class="info-value">{{ $booking->state?->name ?? '-' }}</div>
                            </div>
                            <div class="col-4">
                                <div class="info-label">PIN Code</div>
                                <div class="info-value">{{ $booking->pin_code ?? '-' }}</div>
                            </div>
                        </div>

                        @if($booking->full_address)
                        <div class="row">
                            <div class="col-12">
                                <div class="info-label">Full Address</div>
                                <div class="info-value">
                                    <div class="alert alert-light border mb-0 py-2">
                                        <small><i class="ri-road-map-line me-1"></i>{{ $booking->full_address }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Schedule Booking Date -->
                        @if($booking->booking_date)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card bg-warning-subtle border-warning mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2 text-warning">
                                                    <i class="ri-calendar-check-line me-1"></i> Schedule Booking Date
                                                </h6>
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 10px;">DATE</small>
                                                        <strong class="text-dark">{{ $booking->booking_date->format('d M, Y') }}</strong>
                                                        <small class="text-muted ms-1">({{ $booking->booking_date->format('l') }})</small>
                                                    </div>
                                                    <div class="vr"></div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 10px;">TIME</small>
                                                        <strong class="text-dark">{{ $booking->booking_date->format('h:i A') }}</strong>
                                                    </div>
                                                </div>
                                                @php
                                                    // Get the last reschedule activity using Spatie Activity Log
                                                    $rescheduleActivity = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\Booking::class)
                                                        ->where('subject_id', $booking->id)
                                                        ->where('description', 'Booking rescheduled')
                                                        ->latest()
                                                        ->first();
                                                @endphp
                                                @if($rescheduleActivity)
                                                <div class="border-top pt-2 mt-2">
                                                    <small class="text-muted">
                                                        <i class="ri-user-line me-1"></i>
                                                        Scheduled by <strong>{{ $rescheduleActivity->causer?->firstname }} {{ $rescheduleActivity->causer?->lastname }}</strong>
                                                        <span class="mx-1">•</span>
                                                        <i class="ri-time-line me-1"></i>
                                                        {{ $rescheduleActivity->created_at->diffForHumans() }}
                                                        <span class="mx-1">•</span>
                                                        <i class="ri-map-pin-line me-1"></i>
                                                        via <strong>Booking Show Page</strong>
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info border-info mb-0 py-2">
                                    <small>
                                        <i class="ri-information-line me-1"></i>
                                        <strong>No schedule date set.</strong> Click "Schedule Date" button in the sidebar to set a booking date.
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Address Details col-6 -->
            </div>
            <!-- End row for Property + Address -->

            <!-- Metadata - Full Width Below -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ri-user-add-line me-1"></i>
                                    Created by <strong>{{ $booking->creator?->firstname }} {{ $booking->creator?->lastname }}</strong> 
                                    on {{ $booking->created_at?->format('d M, Y h:i A') }}
                                </small>
                                <small class="text-muted">
                                    <i class="ri-time-line me-1"></i>
                                    Updated {{ $booking->updated_at?->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking History Timeline -->
            @if($booking->histories && $booking->histories->count() > 0)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-info-subtle border-info">
                            <h5 class="card-title mb-0">
                                <i class="ri-history-line me-2"></i>Booking History Timeline
                                <span class="badge bg-info ms-2">{{ $booking->histories->count() }} {{ Str::plural('Change', $booking->histories->count()) }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <style>
                                .timeline-center {
                                    position: relative;
                                    max-width: 100%;
                                    margin: 0 auto;
                                }
                                
                                .timeline-center::before {
                                    content: '';
                                    position: absolute;
                                    width: 3px;
                                    background: linear-gradient(to bottom, #e9ecef 0%, #cbd5e0 50%, #e9ecef 100%);
                                    top: 0;
                                    bottom: 0;
                                    left: 50%;
                                    margin-left: -1.5px;
                                }
                                
                                .timeline-item-wrapper {
                                    position: relative;
                                    margin-bottom: 40px;
                                    animation: fadeInUp 0.5s ease-out;
                                }
                                
                                @keyframes fadeInUp {
                                    from {
                                        opacity: 0;
                                        transform: translateY(20px);
                                    }
                                    to {
                                        opacity: 1;
                                        transform: translateY(0);
                                    }
                                }
                                
                                .timeline-content-left {
                                    width: calc(50% - 40px);
                                    float: left;
                                    text-align: right;
                                    padding-right: 30px;
                                }
                                
                                .timeline-content-right {
                                    width: calc(50% - 40px);
                                    float: right;
                                    text-align: left;
                                    padding-left: 30px;
                                }
                                
                                .timeline-card {
                                    background: #ffffff;
                                    border: 1px solid #e9ecef;
                                    border-radius: 8px;
                                    padding: 20px;
                                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                                    transition: all 0.3s ease;
                                    cursor: pointer;
                                    position: relative;
                                }
                                
                                .timeline-card:hover {
                                    transform: translateY(-5px);
                                    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
                                    border-color: #cbd5e0;
                                }
                                
                                .timeline-card-left {
                                    border-left: 4px solid;
                                }
                                
                                .timeline-card-right {
                                    border-right: 4px solid;
                                }
                                
                                .timeline-icon {
                                    position: absolute;
                                    width: 50px;
                                    height: 50px;
                                    left: 50%;
                                    transform: translateX(-50%);
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border: 4px solid #ffffff;
                                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                    z-index: 10;
                                    transition: all 0.3s ease;
                                }
                                
                                .timeline-item-wrapper:hover .timeline-icon {
                                    transform: translateX(-50%) scale(1.15) rotate(360deg);
                                }
                                
                                .timeline-arrow-left::after {
                                    content: '';
                                    position: absolute;
                                    right: -15px;
                                    top: 20px;
                                    border: 8px solid transparent;
                                    border-left-color: inherit;
                                }
                                
                                .timeline-arrow-right::after {
                                    content: '';
                                    position: absolute;
                                    left: -15px;
                                    top: 20px;
                                    border: 8px solid transparent;
                                    border-right-color: inherit;
                                }
                                
                                .timeline-badge-group {
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 8px;
                                    margin-bottom: 12px;
                                }
                                
                                .timeline-metadata-btn {
                                    transition: all 0.2s ease;
                                }
                                
                                .timeline-metadata-btn:hover {
                                    transform: translateY(-2px);
                                }
                                
                                .clearfix::after {
                                    content: "";
                                    display: table;
                                    clear: both;
                                }
                                
                                @media (max-width: 768px) {
                                    .timeline-center::before {
                                        left: 30px;
                                    }
                                    
                                    .timeline-content-left,
                                    .timeline-content-right {
                                        width: 100%;
                                        float: none;
                                        text-align: left;
                                        padding-left: 80px;
                                        padding-right: 0;
                                    }
                                    
                                    .timeline-icon {
                                        left: 30px;
                                    }
                                }
                            </style>
                            
                            <div class="timeline-center">
                                {{-- Center Vertical Line (now in CSS) --}}
                                @foreach($booking->histories as $history)
                                @php
                                    $statusColors = [
                                        'inquiry' => 'info',
                                        'pending' => 'warning',
                                        'schedul_pending' => 'warning',
                                        'schedul_accepted' => 'success',
                                        'schedul_decline' => 'danger',
                                        'reschedul_pending' => 'warning',
                                        'reschedul_accepted' => 'success',
                                        'reschedul_decline' => 'danger',
                                        'reschedul_blocked' => 'danger',
                                        'schedul_assign' => 'primary',
                                        'schedul_completed' => 'success',
                                        'tour_pending' => 'info',
                                        'tour_completed' => 'success',
                                        'tour_live' => 'success',
                                        'maintenance' => 'secondary',
                                        'expired' => 'dark',
                                        'confirmed' => 'success',
                                        'cancelled' => 'danger',
                                        'completed' => 'primary'
                                    ];
                                    $color = $statusColors[$history->to_status] ?? 'secondary';
                                    $bgColor = 'bg-' . $color . '-subtle';
                                    $textColor = 'text-' . $color;
                                    
                                    // Check if changed by customer or admin
                                    $isCustomer = $history->changedBy && $history->changedBy->roles && $history->changedBy->roles->first() && 
                                                  in_array(strtolower($history->changedBy->roles->first()->name), ['customer', 'user']);
                                @endphp
                                
                                <div class="timeline-item-wrapper clearfix" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                                    {{-- Center Icon --}}
                                    <div class="timeline-icon {{ $bgColor }}">
                                        <i class="ri-{{ $isCustomer ? 'user' : 'shield-user' }}-line fs-4 {{ $textColor }}"></i>
                                    </div>
                                    
                                    @if($isCustomer)
                                        {{-- Customer Action - Left Side --}}
                                        <div class="timeline-content-left">
                                            <div class="timeline-card timeline-card-left timeline-arrow-left" style="border-left-color: var(--bs-{{ $color }});">
                                                <div class="timeline-badge-group" style="justify-content: flex-end;">
                                                    @if($history->from_status)
                                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $history->from_status)) }}</span>
                                                        <i class="ri-arrow-right-line {{ $textColor }}"></i>
                                                    @endif
                                                    <span class="badge {{ $bgColor }} {{ $textColor }} fw-semibold">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
                                                </div>
                                                
                                                @if($history->notes)
                                                <p class="text-muted mb-3 small fst-italic" style="text-align: right;">"{{ $history->notes }}"</p>
                                                @endif
                                                
                                                <div class="mb-2" style="text-align: right;">
                                                    <div class="d-inline-flex align-items-center gap-2 mb-1">
                                                        <i class="ri-user-3-line {{ $textColor }}"></i>
                                                        <strong class="text-dark">
                                                            @if($history->changedBy)
                                                                {{ $history->changedBy->firstname . ' ' . $history->changedBy->lastname }}
                                                            @else
                                                                System
                                                            @endif
                                                        </strong>
                                                        @if($history->changedBy && $history->changedBy->roles && $history->changedBy->roles->first())
                                                            <span class="badge bg-light text-muted border">{{ ucfirst($history->changedBy->roles->first()->name) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-3 flex-wrap justify-content-end text-muted small">
                                                    <span>
                                                        <i class="ri-time-line me-1"></i>
                                                        {{ $history->created_at->format('d M, Y h:i A') }}
                                                    </span>
                                                    <span class="text-success">
                                                        ({{ $history->created_at->diffForHumans() }})
                                                    </span>
                                                    @if($history->ip_address)
                                                    <span>
                                                        <i class="ri-map-pin-line me-1"></i>
                                                        {{ $history->ip_address }}
                                                    </span>
                                                    @endif
                                                </div>
                                                
                                                @if($history->metadata)
                                                <div class="mt-3" style="text-align: right;">
                                                    <button class="btn btn-sm btn-outline-{{ $color }} timeline-metadata-btn" type="button" data-bs-toggle="collapse" data-bs-target="#metadata-{{ $history->id }}">
                                                        <i class="ri-file-list-3-line me-1"></i> View Metadata
                                                    </button>
                                                    <div class="collapse mt-3" id="metadata-{{ $history->id }}">
                                                        <div class="card border-{{ $color }} bg-light">
                                                            <div class="card-body p-3" style="text-align: left;">
                                                                <h6 class="{{ $textColor }} mb-2"><i class="ri-code-s-slash-line me-1"></i> Technical Details</h6>
                                                                <pre class="mb-0 small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($history->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- Admin/System Action - Right Side --}}
                                        <div class="timeline-content-right">
                                            <div class="timeline-card timeline-card-right timeline-arrow-right" style="border-right-color: var(--bs-{{ $color }});">
                                                <div class="timeline-badge-group">
                                                    @if($history->from_status)
                                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $history->from_status)) }}</span>
                                                        <i class="ri-arrow-right-line {{ $textColor }}"></i>
                                                    @endif
                                                    <span class="badge {{ $bgColor }} {{ $textColor }} fw-semibold">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
                                                </div>
                                                
                                                @if($history->notes)
                                                <p class="text-muted mb-3 small fst-italic">"{{ $history->notes }}"</p>
                                                @endif
                                                
                                                <div class="mb-2">
                                                    <div class="d-inline-flex align-items-center gap-2 mb-1">
                                                        <i class="ri-shield-user-line {{ $textColor }}"></i>
                                                        <strong class="text-dark">
                                                            @if($history->changedBy)
                                                                {{ $history->changedBy->firstname . ' ' . $history->changedBy->lastname }}
                                                            @else
                                                                System
                                                            @endif
                                                        </strong>
                                                        @if($history->changedBy && $history->changedBy->roles && $history->changedBy->roles->first())
                                                            <span class="badge bg-light text-muted border">{{ ucfirst($history->changedBy->roles->first()->name) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-3 flex-wrap text-muted small">
                                                    <span>
                                                        <i class="ri-time-line me-1"></i>
                                                        {{ $history->created_at->format('d M, Y h:i A') }}
                                                    </span>
                                                    <span class="text-success">
                                                        ({{ $history->created_at->diffForHumans() }})
                                                    </span>
                                                    @if($history->ip_address)
                                                    <span>
                                                        <i class="ri-map-pin-line me-1"></i>
                                                        {{ $history->ip_address }}
                                                    </span>
                                                    @endif
                                                </div>
                                                
                                                @if($history->metadata)
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-outline-{{ $color }} timeline-metadata-btn" type="button" data-bs-toggle="collapse" data-bs-target="#metadata-{{ $history->id }}">
                                                        <i class="ri-file-list-3-line me-1"></i> View Metadata
                                                    </button>
                                                    <div class="collapse mt-3" id="metadata-{{ $history->id }}">
                                                        <div class="card border-{{ $color }} bg-light">
                                                            <div class="card-body p-3">
                                                                <h6 class="{{ $textColor }} mb-2"><i class="ri-code-s-slash-line me-1"></i> Technical Details</h6>
                                                                <pre class="mb-0 small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($history->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            </div>
            <!-- End col-9 -->
            
            <script>
                // Add smooth scroll reveal animation to timeline items
                document.addEventListener('DOMContentLoaded', function() {
                    const observerOptions = {
                        threshold: 0.1,
                        rootMargin: '0px 0px -50px 0px'
                    };
                    
                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }
                        });
                    }, observerOptions);
                    
                    document.querySelectorAll('.timeline-item-wrapper').forEach((item, index) => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(30px)';
                        item.style.transition = `all 0.6s ease-out ${index * 0.1}s`;
                        observer.observe(item);
                    });
                });
            </script>

            <!-- Action Sidebar (col-3) -->
            <div class="col-lg-3">
                <!-- Quick Actions Card -->
                <div class="card border mb-3 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="card-title mb-0"><i class="ri-flashlight-line me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body p-2">
                        <!-- Payment Status -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted" style="font-size: 11px;">PAYMENT STATUS</label>
                            <select class="form-select form-select-sm" id="quickPaymentStatus" onchange="updatePaymentStatus(this.value)">
                                <option value="unpaid" {{ $booking->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $booking->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>

                        <!-- Booking Status -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted" style="font-size: 11px;">BOOKING STATUS</label>
                            <select class="form-select form-select-sm" id="quickBookingStatus" onchange="updateBookingStatus(this.value)">
                                <option value="inquiry" {{ $booking->status == 'inquiry' ? 'selected' : '' }}>Inquiry</option>
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="schedul_pending" {{ $booking->status == 'schedul_pending' ? 'selected' : '' }}>Schedule Pending</option>
                                <option value="schedul_accepted" {{ $booking->status == 'schedul_accepted' ? 'selected' : '' }}>Schedule Accepted</option>
                                <option value="schedul_decline" {{ $booking->status == 'schedul_decline' ? 'selected' : '' }}>Schedule Declined</option>
                                <option value="reschedul_pending" {{ $booking->status == 'reschedul_pending' ? 'selected' : '' }}>Reschedule Pending</option>
                                <option value="reschedul_accepted" {{ $booking->status == 'reschedul_accepted' ? 'selected' : '' }}>Reschedule Accepted</option>
                                <option value="reschedul_decline" {{ $booking->status == 'reschedul_decline' ? 'selected' : '' }}>Reschedule Declined</option>
                                <option value="reschedul_blocked" {{ $booking->status == 'reschedul_blocked' ? 'selected' : '' }}>Reschedule Blocked</option>
                                <option value="schedul_assign" {{ $booking->status == 'schedul_assign' ? 'selected' : '' }}>Schedule Assigned</option>
                                <option value="schedul_completed" {{ $booking->status == 'schedul_completed' ? 'selected' : '' }}>Schedule Completed</option>
                                <option value="tour_pending" {{ $booking->status == 'tour_pending' ? 'selected' : '' }}>Tour Pending</option>
                                <option value="tour_completed" {{ $booking->status == 'tour_completed' ? 'selected' : '' }}>Tour Completed</option>
                                <option value="tour_live" {{ $booking->status == 'tour_live' ? 'selected' : '' }}>Tour Live</option>
                                <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="maintenance" {{ $booking->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ $booking->status == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>

                        <hr class="my-3">
                        
                        <!-- Status Quick Actions -->
                        {{-- <div class="mb-3">
                            <label class="form-label fw-semibold text-muted" style="font-size: 11px;">QUICK STATUS ACTIONS</label>
                            <div class="d-grid gap-2">
                                @if($booking->status == 'inquiry')
                                    <button class="btn btn-sm btn-outline-primary" onclick="changeStatusWithNote('pending', 'Convert Inquiry to Pending')">
                                        <i class="ri-arrow-right-circle-line me-1"></i> Convert to Pending
                                    </button>
                                @endif
                                
                                @if($booking->status == 'pending')
                                    <button class="btn btn-sm btn-outline-success" onclick="changeStatusWithNote('schedul_pending', 'Mark as Schedule Pending')">
                                        <i class="ri-calendar-check-line me-1"></i> Mark Schedule Pending
                                    </button>
                                @endif
                                
                                @if(in_array($booking->status, ['schedul_accepted', 'reschedul_accepted']))
                                    <button class="btn btn-sm btn-outline-primary" onclick="changeStatusWithNote('schedul_assign', 'Assign Schedule')">
                                        <i class="ri-user-add-line me-1"></i> Assign to Agent
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="changeStatusWithNote('tour_pending', 'Tour Pending')">
                                        <i class="ri-map-pin-line me-1"></i> Start Tour Process
                                    </button>
                                @endif
                                
                                @if($booking->status == 'schedul_assign')
                                    <button class="btn btn-sm btn-outline-success" onclick="changeStatusWithNote('schedul_completed', 'Mark Schedule as Completed')">
                                        <i class="ri-check-double-line me-1"></i> Complete Schedule
                                    </button>
                                @endif
                                
                                @if($booking->status == 'tour_pending')
                                    <button class="btn btn-sm btn-outline-success" onclick="changeStatusWithNote('tour_live', 'Tour is Now Live')">
                                        <i class="ri-live-line me-1"></i> Start Live Tour
                                    </button>
                                @endif
                                
                                @if($booking->status == 'tour_live')
                                    <button class="btn btn-sm btn-outline-success" onclick="changeStatusWithNote('tour_completed', 'Tour Completed Successfully')">
                                        <i class="ri-checkbox-circle-line me-1"></i> Complete Tour
                                    </button>
                                @endif
                                
                                @if(in_array($booking->status, ['tour_completed', 'schedul_completed']))
                                    <button class="btn btn-sm btn-outline-warning" onclick="changeStatusWithNote('maintenance', 'Move to Maintenance')">
                                        <i class="ri-tools-line me-1"></i> Maintenance
                                    </button>
                                @endif
                                
                                @if(!in_array($booking->status, ['cancelled', 'expired']))
                                    <button class="btn btn-sm btn-outline-danger" onclick="changeStatusWithNote('cancelled', 'Cancel Booking')">
                                        <i class="ri-close-circle-line me-1"></i> Cancel Booking
                                    </button>
                                @endif
                            </div>
                        </div>

                        <hr class="my-3"> --}}

                        <!-- Schedule Date -->
                        <div class="mb-3">
                            <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                <i class="ri-calendar-check-line me-1"></i> Schedule Date
                            </button>
                        </div>

                        <!-- Assign QR -->
                        <div class="mb-3">
                            <button class="btn btn-outline-success btn-sm w-100" onclick="assignQR()">
                                <i class="ri-qr-code-line me-1"></i> Assign QR Code
                            </button>
                        </div>

                        <!-- Accept/Decline Schedule (if pending) -->
                        @if(in_array($booking->status, ['schedul_pending', 'reschedul_pending']))
                            <hr class="my-3">
                            
                            <div class="card border-warning mb-3">
                                <div class="card-header bg-warning-subtle border-warning py-2">
                                    <h6 class="mb-0 text-warning">
                                        <i class="ri-alert-line me-1"></i> 
                                        {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }} Approval Required
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    @if($booking->booking_date)
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">REQUESTED DATE</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-calendar-line text-primary"></i>
                                                <strong class="text-dark">{{ $booking->booking_date->format('d M, Y') }}</strong>
                                                <small class="text-muted">({{ $booking->booking_date->format('l') }})</small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($booking->booking_notes)
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">CUSTOMER NOTES</small>
                                            <div class="alert alert-light border mb-0 py-2">
                                                <small><i class="ri-message-3-line me-1"></i>{{ $booking->booking_notes }}</small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success btn-sm" onclick="acceptScheduleFromShow()">
                                            <i class="ri-check-line me-1"></i> Accept {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="declineScheduleFromShow()">
                                            <i class="ri-close-line me-1"></i> Decline {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <hr class="my-3">

                        <!-- Edit Button -->
                        <div class="mb-2">
                            <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-info btn-sm w-100">
                                <i class="ri-edit-line me-1"></i> Edit Booking
                            </a>
                        </div>

                        <!-- Delete Button -->
                        {{-- <div class="mb-0">
                            <button class="btn btn-danger btn-sm w-100" onclick="deleteBooking()">
                                <i class="ri-delete-bin-line me-1"></i> Delete Booking
                            </button>
                        </div> --}}
                    </div>
                </div>

                <!-- Booking Summary Card -->
                <div class="card border mb-3">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0"><i class="ri-file-info-line me-2"></i>Summary</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-2">
                            <small class="text-muted d-block" style="font-size: 10px;">BOOKING ID</small>
                            <strong class="d-block">#{{ $booking->id }}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <small class="text-muted d-block" style="font-size: 10px;">CREATED</small>
                            <small class="d-block">{{ $booking->created_at?->format('d M, Y') }}</small>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <small class="text-muted d-block" style="font-size: 10px;">AREA</small>
                            <strong class="d-block">{{ number_format($booking->area) }} sq ft</strong>
                        </div>
                        <hr class="my-2">
                        <div class="mb-0">
                            <small class="text-muted d-block" style="font-size: 10px;">PRICE</small>
                            <strong class="d-block text-success">₹{{ number_format($booking->price) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End col-3 -->
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedule Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="mb-3">
                        <label class="form-label">Current Booking Date</label>
                        <div id="current-booking-date" class="form-control-plaintext text-primary mb-2">
                            {{ $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'Not set' }}
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Schedule Mode</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="schedule_mode" id="schedule-mode-default" value="default" checked>
                                    <label class="form-check-label" for="schedule-mode-default">Default</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="schedule_mode" id="schedule-mode-any" value="any">
                                    <label class="form-check-label" for="schedule-mode-any">Pick Any Day</label>
                                </div>
                            </div>
                        </div>
                        <label for="schedule-date" class="form-label">Select Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white" id="calendar-icon-trigger" style="cursor: pointer;" title="Open calendar">
                                <i class="ri-calendar-line"></i>
                            </span>
                            <input type="text" class="form-control" id="schedule-date" name="schedule_date" placeholder="Click to select date" required autocomplete="off">
                        </div>
                    </div>
                    <input type="hidden" id="schedule-booking-id" name="booking_id" value="{{ $booking->id }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="scheduleSubmitBtn">Schedule</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const bookingId = {{ $booking->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const baseUrl = '{{ url("/") }}';
    const apiBaseUrl = '{{ url("/api") }}';

    // Update Payment Status
    async function updatePaymentStatus(status) {
        const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        
        const result = await Swal.fire({
            title: 'Update Payment Status?',
            html: `
                <p>Change payment status to <strong class="text-primary">"${statusLabel}"</strong></p>
                <div class="mb-3">
                    <label class="form-label text-start d-block">Add Notes (Optional):</label>
                    <textarea id="payment-status-notes" class="form-control" rows="3" placeholder="Enter notes about this payment status change..."></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                return {
                    notes: document.getElementById('payment-status-notes').value
                };
            }
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}/update-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ 
                        payment_status: status,
                        notes: result.value.notes || `Payment status changed to ${statusLabel}`
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        html: `<p class="mb-2">Payment status has been changed to <strong class="text-success">${statusLabel}</strong></p><p class="text-muted small">History entry created automatically</p>`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    throw new Error(data.message || 'Failed to update');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update payment status'
                });
                // Revert select
                document.getElementById('quickPaymentStatus').value = '{{ $booking->payment_status }}';
            }
        } else {
            // Revert select
            document.getElementById('quickPaymentStatus').value = '{{ $booking->payment_status }}';
        }
    }

    // Update Booking Status
    async function updateBookingStatus(status) {
        const result = await Swal.fire({
            title: 'Update Booking Status?',
            html: `
                <p>Change booking status to <strong>"${status.replace(/_/g, ' ').toUpperCase()}"</strong></p>
                <div class="mb-3">
                    <label class="form-label text-start d-block">Add Notes (Optional):</label>
                    <textarea id="status-notes" class="form-control" rows="3" placeholder="Enter notes about this status change..."></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                return {
                    notes: document.getElementById('status-notes').value
                };
            }
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}/update-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ 
                        status: status,
                        notes: result.value.notes || `Status changed to ${status.replace(/_/g, ' ')}`
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Booking status updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to update');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update booking status'
                });
                // Revert select
                document.getElementById('quickBookingStatus').value = '{{ $booking->status }}';
            }
        } else {
            // Revert select
            document.getElementById('quickBookingStatus').value = '{{ $booking->status }}';
        }
    }

    // Change Status with Note (for Quick Action Buttons)
    async function changeStatusWithNote(newStatus, defaultNote) {
        const statusLabel = newStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        const result = await Swal.fire({
            title: `Change to ${statusLabel}?`,
            html: `
                <div class="mb-3">
                    <p class="text-muted mb-3">You are about to change the booking status to <strong class="text-primary">${statusLabel}</strong></p>
                    <label class="form-label text-start d-block fw-semibold">Add Notes (Optional):</label>
                    <textarea id="quick-status-notes" class="form-control" rows="3" placeholder="Enter notes about this change...">${defaultNote}</textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-check-line me-1"></i> Confirm Change',
            cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            preConfirm: () => {
                return {
                    notes: document.getElementById('quick-status-notes').value || defaultNote
                };
            }
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}/update-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ 
                        status: newStatus,
                        notes: result.value.notes
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        html: `<p class="mb-2">Booking status has been changed to <strong class="text-success">${statusLabel}</strong></p><p class="text-muted small">History entry created automatically</p>`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    throw new Error(data.message || 'Failed to update status');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: error.message || 'Failed to update booking status',
                    confirmButtonText: 'OK'
                });
            }
        }
    }

    // Schedule Date Modal with Flatpickr
    let holidays = [];
    let flatpickrInstance = null;
    let lastSelectedDate = '{{ optional($booking->booking_date)->format("Y-m-d") }}' || null;
    let lastDayLimit = 30;

    function fetchHolidaysAndInitPicker(selectedDate) {
        fetch(`${apiBaseUrl}/holidays`)
            .then(response => response.json())
            .then(data => {
                holidays = (data.holidays || []).map(h => h.date);
                let dayLimit = 30;
                if (data.day_limit && data.day_limit.value) {
                    dayLimit = parseInt(data.day_limit.value, 10) || 30;
                }
                lastDayLimit = dayLimit;
                initFlatpickr(selectedDate, dayLimit);
            })
            .catch(error => {
                console.error('Failed to fetch holidays:', error);
                initFlatpickr(selectedDate, 30);
            });
    }

    function initFlatpickr(selectedDate, dayLimit = 30, mode = 'default') {
        if (flatpickrInstance) flatpickrInstance.destroy();
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        let maxDate = null;
        let disable = [];
        
        if (mode === 'default') {
            const max = new Date();
            max.setDate(today.getDate() + dayLimit);
            maxDate = max.toISOString().split('T')[0];
            disable = holidays;
        }
        
        flatpickrInstance = flatpickr('#schedule-date', {
            dateFormat: 'Y-m-d',
            minDate: minDate,
            maxDate: maxDate,
            disable: disable,
            defaultDate: selectedDate || null,
            onChange: function (selectedDates, dateStr) {
                if (mode === 'default' && holidays.includes(dateStr)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Holiday',
                        text: 'Selected date is a holiday. Please choose another date.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    flatpickrInstance.clear();
                }
            }
        });
    }

    // Handle schedule mode change
    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'schedule_mode') {
            const mode = e.target.value;
            if (mode === 'any') {
                initFlatpickr(lastSelectedDate, 0, 'any');
            } else {
                initFlatpickr(lastSelectedDate, lastDayLimit, 'default');
            }
        }
    });

    // Initialize Flatpickr when modal opens
    document.getElementById('scheduleModal').addEventListener('show.bs.modal', function () {
        document.getElementById('schedule-mode-default').checked = true;
        fetchHolidaysAndInitPicker(lastSelectedDate);
    });

    // Make calendar icon clickable to open date picker
    document.getElementById('calendar-icon-trigger').addEventListener('click', function() {
        if (flatpickrInstance) {
            flatpickrInstance.open();
        }
    });

    // Schedule submit button
    document.getElementById('scheduleSubmitBtn').addEventListener('click', function () {
        const date = document.getElementById('schedule-date').value;
        if (!date) {
            document.getElementById('schedule-date').classList.add('is-invalid');
            return;
        }
        document.getElementById('schedule-date').classList.remove('is-invalid');

        fetch(`${baseUrl}/admin/bookings/${bookingId}/reschedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ schedule_date: date })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Booking rescheduled successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.reload(), 2000);
            } else {
                throw new Error(data.message || 'Failed to reschedule booking.');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to reschedule booking.'
            });
        });
    });

    // Assign QR Code
    async function assignQR() {
        Swal.fire({
            title: 'Assign QR Code',
            text: 'This feature will open the QR assignment interface',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Go to QR Page',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to QR assignment page or open modal
                window.location.href = '/admin/qrs?booking_id=' + bookingId;
            }
        });
    }

    // Delete Booking
    async function deleteBooking() {
        const result = await Swal.fire({
            title: 'Delete Booking?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Booking deleted successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.href = `${baseUrl}/admin/bookings`;
                } else {
                    throw new Error(data.message || 'Failed to delete');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to delete booking'
                });
            }
        }
    }

    // Accept Schedule from Show Page
    async function acceptScheduleFromShow() {
        const requestedDate = '{{ $booking->booking_date ? $booking->booking_date->format("F j, Y") : "Not specified" }}';
        const customerNotes = '{{ $booking->booking_notes ?? "" }}';
        const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';
        
        const htmlContent = `
            <div class="text-start mb-3">
                <div class="border-bottom pb-2 mb-2">
                    <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                    <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                </div>
                ${customerNotes ? `
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Customer Notes:</strong></small>
                        <div class="alert alert-info py-2 mb-0"><small>${customerNotes}</small></div>
                    </div>
                ` : ''}
                <div>
                    <small class="text-muted d-block mb-1"><strong>Admin Notes (Optional):</strong></small>
                </div>
            </div>
        `;

        const result = await Swal.fire({
            title: 'Accept Schedule?',
            html: htmlContent,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Accept',
            cancelButtonText: 'Cancel',
            input: 'textarea',
            inputPlaceholder: 'Add admin notes (optional)...',
            inputAttributes: {
                maxlength: 500
            },
            width: '600px'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/pending-schedules/${bookingId}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ notes: result.value || null })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Accepted!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to accept schedule');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to accept schedule'
                });
            }
        }
    }

    // Decline Schedule from Show Page
    async function declineScheduleFromShow() {
        const requestedDate = '{{ $booking->booking_date ? $booking->booking_date->format("F j, Y") : "Not specified" }}';
        const customerNotes = '{{ $booking->booking_notes ?? "" }}';
        const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';
        
        const htmlContent = `
            <div class="text-start mb-3">
                <div class="border-bottom pb-2 mb-2">
                    <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                    <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                </div>
                ${customerNotes ? `
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Customer Notes:</strong></small>
                        <div class="alert alert-info py-2 mb-0"><small>${customerNotes}</small></div>
                    </div>
                ` : ''}
                <div>
                    <small class="text-muted d-block mb-1"><strong>Reason for Decline:</strong> <span class="text-danger">*</span></small>
                </div>
            </div>
        `;

        const result = await Swal.fire({
            title: 'Decline Schedule?',
            html: htmlContent,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Decline',
            cancelButtonText: 'Cancel',
            input: 'textarea',
            inputPlaceholder: 'Enter reason for declining...',
            inputAttributes: {
                maxlength: 500,
                required: true
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'You must provide a reason!'
                }
            },
            width: '600px'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${baseUrl}/admin/pending-schedules/${bookingId}/decline`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: result.value })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Declined!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to decline schedule');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to decline schedule'
                });
            }
        }
    }
</script>
@endsection
