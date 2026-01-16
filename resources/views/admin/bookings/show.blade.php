@extends('admin.layouts.vertical', ['title' => 'Booking Details', 'subTitle' => 'Property'])

@section('css')
    <!-- Font Awesome for dynamic icons from database -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Pill and Chip Styles - Read-only version */
        .top-pill,
        .chip {
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .top-pill.active,
        .chip.active {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border-color: #0d6efd;
            color: #fff;
            box-shadow: 0 3px 6px rgba(13, 110, 253, 0.3);
        }

        .top-pill i,
        .chip i {
            margin-right: 6px;
            font-size: 16px;
        }

        .top-pill.active i,
        .chip.active i {
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
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
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">#{{ $booking->id }} </li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Booking #{{ $booking->id }} ({{ $booking->tour_code }})</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-primary']"
                        :merge="false" icon="solar:arrow-left-broken" />
                    
                    @if($booking->tours()->exists() && auth()->user()->can('tour_manager_edit'))
                           <a href="{{ route('admin.tour-manager.upload', $booking) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="Upload & Manage Tour Assets">
                            <iconify-icon icon="solar:upload-minimalistic-broken" class="align-middle me-1"></iconify-icon> Upload Tour
                        </a>
                    @endif

                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-primary"><iconify-icon icon="solar:pen-2-broken" class="align-middle me-1"></iconify-icon> Edit</a>
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
                                        <div
                                            class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="ri-user-line fs-3 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">{{ $booking->user?->firstname }} {{ $booking->user?->lastname }}
                                        </h5>
                                        <div class="d-flex gap-3">
                                            <small class="text-muted"><i
                                                    class="ri-id-card-line me-1"></i>{{ $booking->user?->id ?? '-' }}</small>
                                            
                                            <small class="text-muted"><i
                                                    class="ri-phone-line me-1"></i>{{ $booking->user?->mobile ?? '-' }}</small>
                                            @if($booking->user?->email)
                                            <small class="text-muted"><i
                                                class="ri-mail-line me-1"></i>{{ $booking->user?->email ?? '-' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Metadata Integrated to Right Side -->
                                    <div class="text-end">
                                        <div class="d-block mb-1">
                                            <small class="text-muted">
                                                <i class="ri-user-add-line me-1"></i>
                                                Created by <strong>{{ $booking->creator?->firstname }}
                                                    {{ $booking->creator?->lastname }}</strong>
                                            </small>
                                        </div>
                                        <div class="d-block mb-1">
                                            <small class="text-muted">
                                                on {{ $booking->created_at?->format('d M, Y h:i A') }}
                                            </small>
                                        </div>
                                        <div class="d-block">
                                            <small class="text-muted">
                                                <i class="ri-time-line me-1"></i>
                                                Updated {{ $booking->updated_at?->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Property Details -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="card border bg-light-subtle h-100">
                                <div class="card-header bg-primary-subtle border-primary">
                                    <h5 class="card-title mb-0"><i class="ri-building-line me-2"></i>Property Details</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $currentPropertyType = $booking->propertyType->name ?? 'Residential';
                                        $propertyTypeOrder = [
                                            'Residential' => ['key' => 'res', 'icon' => 'ri-home-4-line', 'type' => 'ri'],
                                            'Commercial' => ['key' => 'com', 'icon' => 'ri-building-line', 'type' => 'ri'],
                                            'Other' => ['key' => 'oth', 'icon' => 'fa-ellipsis', 'type' => 'fa'],
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
                                    <div class="row">
                                        @if($booking->furniture_type)
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
                                                    <div class="chip active" style="cursor: default;">{{ $booking->bhk?->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Other Option Details -->
                                    @if($booking->other_option_details)
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="info-label">Other Option Details</div>
                                                <div class="info-value mb-2">
                                                    <div class="alert alert-info border-info mb-0 py-2">
                                                        <small>{{ $booking->other_option_details }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Billing Details -->
                                    @if($booking->firm_name || $booking->gst_no)
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-light border mb-2" role="alert">
                                                    <small class="d-block mb-1" style="font-weight: 600;"><i
                                                            class="ri-briefcase-line me-1"></i> Company Billing Details</small>
                                                    <div class="row">
                                                        @if($booking->firm_name)
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 10px;">COMPANY
                                                                    NAME</small>
                                                                <small class="d-block">{{ $booking->firm_name }}</small>
                                                            </div>
                                                        @endif
                                                        @if($booking->gst_no)
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 10px;">GST
                                                                    NO</small>
                                                                <small class="d-block">{{ $booking->gst_no }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Area, Price, Date - HIGHLIGHTED -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-primary-subtle border-primary mb-2">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <small class="text-muted d-block"
                                                                style="font-size: 11px; font-weight: 600;">SUPER BUILT-UP
                                                                AREA</small>
                                                            <h4 class="mb-0 mt-1 text-primary"><i
                                                                    class="ri-ruler-line me-2"></i>{{ number_format($booking->area) }}
                                                                sq ft</h4>
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
                                                            <small class="text-muted d-block"
                                                                style="font-size: 11px; font-weight: 600;">PRICE</small>
                                                            <h4 class="mb-0 mt-1 text-success"><i
                                                                    class="ri-money-rupee-circle-line me-2"></i>₹{{ number_format($booking->price) }}
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <!-- Status Information - HIGHLIGHTED -->
                                    <div class="row">
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
                                                            <small class="text-muted d-block"
                                                                style="font-size: 11px; font-weight: 600;">PAYMENT
                                                                STATUS</small>
                                                            <h5 class="mb-0 mt-1 text-{{ $paymentColor }}">
                                                                <i class="ri-wallet-line me-2"></i>
                                                                <span
                                                                    class="badge bg-{{ $paymentColor }} badge-custom">{{ ucfirst($booking->payment_status) }}</span>
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
                                                            <small class="text-muted d-block"
                                                                style="font-size: 11px; font-weight: 600;">BOOKING
                                                                STATUS</small>
                                                            <h5 class="mb-0 mt-1 text-{{ $statusColor }}">
                                                                <i class="ri-bookmark-line me-2"></i>
                                                                <span
                                                                    class="badge bg-{{ $statusColor }} badge-custom">{{ ucfirst($booking->status) }}</span>
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
                                                    <small class="d-block mb-1" style="font-weight: 600;"><i
                                                            class="ri-camera-line me-1"></i> Tour Information</small>
                                                    <div class="d-flex gap-3 align-items-center">
                                                        @if($booking->tour_code)
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 10px;">TOUR
                                                                    CODE</small>
                                                                <code style="font-size: 12px;">{{ $booking->tour_code }}</code>
                                                            </div>
                                                        @endif
                                                        @if($booking->tour_final_link)
                                                            <div>
                                                                <a href="{{ $booking->tour_final_link }}" target="_blank"
                                                                    class="btn btn-sm btn-success">
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
                        <div class="col-lg-6 col-md-6 mb-3">
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
                                                <div
                                                    class="col-{{ $booking->society_name && $booking->landmark ? '4' : ($booking->society_name || $booking->landmark ? '6' : '12') }}">
                                                    <div class="info-label">Area / Locality</div>
                                                    <div class="info-value">{{ $booking->address_area }}</div>
                                                </div>
                                            @endif
                                            @if($booking->landmark)
                                                <div
                                                    class="col-{{ $booking->society_name && $booking->address_area ? '4' : ($booking->society_name || $booking->address_area ? '6' : '12') }}">
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
                                                        <small><i
                                                                class="ri-road-map-line me-1"></i>{{ $booking->full_address }}</small>
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
                                                                    <i class="ri-calendar-check-line me-1"></i> Schedule Booking
                                                                    Date
                                                                </h6>
                                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                                    <div>
                                                                        <small class="text-muted d-block"
                                                                            style="font-size: 10px;">DATE</small>
                                                                        <strong
                                                                            class="text-dark">{{ $booking->booking_date->format('d M, Y') }}</strong>
                                                                        <small
                                                                            class="text-muted ms-1">({{ $booking->booking_date->format('l') }})</small>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-muted d-block"
                                                                            style="font-size: 10px;">STATUS</small>
                                                                        <strong
                                                                            class="text-dark">{{ $booking->status }}</strong>
                                                                    </div>
                                                                    @if($booking->booking_time)
                                                                        <div class="vr"></div>
                                                                        <div>
                                                                            <small class="text-muted d-block"
                                                                                style="font-size: 10px;">TIME</small>
                                                                            <strong
                                                                                class="text-dark">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</strong>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @if($booking->booking_notes)
                                                                    <div class="mt-2 pt-2 border-top">
                                                                        <small class="text-muted d-block mb-1"
                                                                            style="font-size: 10px; font-weight: 600;">CUSTOMER
                                                                            NOTES</small>
                                                                        <div class="alert alert-light border mb-0 py-2">
                                                                            <small><i
                                                                                    class="ri-message-3-line me-1"></i>{{ $booking->booking_notes }}</small>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                @php
                                                                    // Get the last reschedule activity using Spatie Activity Log
                                                                    $rescheduleActivity = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\Booking::class)
                                                                        ->where('subject_id', $booking->id)
                                                                        ->where('description', 'Booking rescheduled')
                                                                        ->latest()
                                                                        ->first();
                                                                @endphp
                                                                @php
                                                                    // Get photographer assignee (user with photographer role)
                                                                    $photographerAssignee = $booking->assignees->first(function ($assignee) {
                                                                        return $assignee->user && $assignee->user->hasRole('photographer');
                                                                    });
                                                                    $photographer = $photographerAssignee?->user;
                                                                    $isPhotographerAssigned = $photographer && $photographerAssignee;
                                                                @endphp

                                                                @if($isPhotographerAssigned)
                                                                    <div class="mt-3 pt-3 border-top">
                                                                        <div class="alert alert-success mb-0 py-2"
                                                                            style="background-color: #d4edda; border-color: #c3e6cb;">
                                                                            <div class="d-flex align-items-start mb-2">
                                                                                <i class="ri-user-check-line me-2 mt-1"
                                                                                    style="color: #28a745;"></i>
                                                                                <div class="flex-grow-1">
                                                                                    <strong class="d-block mb-1"
                                                                                        style="color: #155724; font-size: 0.9rem;">Photographer
                                                                                        Assigned</strong>
                                                                                    <div class="small" style="color: #155724;">
                                                                                        <div class="mb-1">
                                                                                            <i class="ri-user-line me-1"></i>
                                                                                            <strong>Name:</strong>
                                                                                            {{ $photographer->firstname }}
                                                                                            {{ $photographer->lastname }}
                                                                                        </div>
                                                                                        <div class="mb-1">
                                                                                            <i class="ri-phone-line me-1"></i>
                                                                                            <strong>Phone:</strong>
                                                                                            <a href="tel:{{ $photographer->mobile }}"
                                                                                                class="text-decoration-none"
                                                                                                style="color: #155724;">
                                                                                                {{ $photographer->mobile }}
                                                                                            </a>
                                                                                        </div>
                                                                                        @if($photographerAssignee->date)
                                                                                            <div class="mb-1">
                                                                                                <i
                                                                                                    class="ri-calendar-check-line me-1"></i>
                                                                                                <strong>Scheduled Date:</strong>
                                                                                                {{ \Carbon\Carbon::parse($photographerAssignee->date)->format('d M, Y') }}
                                                                                            </div>
                                                                                        @endif
                                                                                        @if($photographerAssignee->time)
                                                                                            <div class="mb-1">
                                                                                                <i class="ri-time-line me-1"></i>
                                                                                                <strong>Time:</strong>
                                                                                                {{ \Carbon\Carbon::parse($photographerAssignee->time)->format('h:i A') }}
                                                                                            </div>
                                                                                        @elseif($booking->booking_time)
                                                                                            <div class="mb-1">
                                                                                                <i class="ri-time-line me-1"></i>
                                                                                                <strong>Time:</strong>
                                                                                                {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}
                                                                                            </div>
                                                                                        @endif
                                                                                        @if($photographerAssignee->created_at)
                                                                                            <div class="mb-0">
                                                                                                <i
                                                                                                    class="ri-calendar-event-line me-1"></i>
                                                                                                <strong>Assigned:</strong>
                                                                                                {{ \Carbon\Carbon::parse($photographerAssignee->created_at)->format('d M, Y h:i A') }}
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($rescheduleActivity)
                                                                    <div class="border-top pt-2 mt-2">
                                                                        <small class="text-muted">
                                                                            <i class="ri-user-line me-1"></i>
                                                                            Scheduled by
                                                                            <strong>{{ $rescheduleActivity->causer?->firstname }}
                                                                                {{ $rescheduleActivity->causer?->lastname }}</strong>
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
                                                        <strong>No schedule date set.</strong> Click "Schedule Date" button in
                                                        the sidebar to set a booking date.
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

                    <!-- Metadata moved to top -->

                    <!-- Booking History Timeline -->
                    @if($booking->histories && $booking->histories->count() > 0)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-header bg-info-subtle border-info">
                                        <h5 class="card-title mb-0">
                                            <i class="ri-history-line me-2"></i>Booking History Timeline
                                            <span class="badge bg-info ms-2">{{ $booking->histories->count() }}
                                                {{ Str::plural('Change', $booking->histories->count()) }}</span>
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
                                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                                                transition: all 0.3s ease;
                                                cursor: pointer;
                                                position: relative;
                                            }

                                            .timeline-card:hover {
                                                transform: translateY(-5px);
                                                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
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
                                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

                                                <div class="timeline-item-wrapper clearfix"
                                                    style="animation-delay: {{ $loop->index * 0.1 }}s;">
                                                    {{-- Center Icon --}}
                                                    <div class="timeline-icon {{ $bgColor }}">
                                                        <i
                                                            class="ri-{{ $isCustomer ? 'user' : 'shield-user' }}-line fs-4 {{ $textColor }}"></i>
                                                    </div>

                                                    @if($isCustomer)
                                                        {{-- Customer Action - Left Side --}}
                                                        <div class="timeline-content-left">
                                                            <div class="timeline-card timeline-card-left timeline-arrow-left"
                                                                style="border-left-color: var(--bs-{{ $color }});">
                                                                <div class="timeline-badge-group" style="justify-content: flex-end;">
                                                                    @if($history->from_status)
                                                                        <span
                                                                            class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $history->from_status)) }}</span>
                                                                        <i class="ri-arrow-right-line {{ $textColor }}"></i>
                                                                    @endif
                                                                    <span
                                                                        class="badge {{ $bgColor }} {{ $textColor }} fw-semibold">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
                                                                </div>

                                                                @if($history->notes)
                                                                    <p class="text-muted mb-3 small fst-italic" style="text-align: right;">
                                                                        "{{ $history->notes }}"</p>
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
                                                                            <span
                                                                                class="badge bg-light text-muted border">{{ ucfirst($history->changedBy->roles->first()->name) }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="d-flex gap-3 flex-wrap justify-content-end text-muted small">
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
                                                                        <button
                                                                            class="btn btn-sm btn-outline-{{ $color }} timeline-metadata-btn"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#metadata-{{ $history->id }}">
                                                                            <i class="ri-file-list-3-line me-1"></i> View Metadata
                                                                        </button>
                                                                        <div class="collapse mt-3" id="metadata-{{ $history->id }}">
                                                                            <div class="card border-{{ $color }} bg-light">
                                                                                <div class="card-body p-3" style="text-align: left;">
                                                                                    <h6 class="{{ $textColor }} mb-2"><i
                                                                                            class="ri-code-s-slash-line me-1"></i> Technical
                                                                                        Details</h6>
                                                                                    <pre class="mb-0 small"
                                                                                        style="max-height: 300px; overflow-y: auto;">{{ json_encode($history->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
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
                                                            <div class="timeline-card timeline-card-right timeline-arrow-right"
                                                                style="border-right-color: var(--bs-{{ $color }});">
                                                                <div class="timeline-badge-group">
                                                                    @if($history->from_status)
                                                                        <span
                                                                            class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $history->from_status)) }}</span>
                                                                        <i class="ri-arrow-right-line {{ $textColor }}"></i>
                                                                    @endif
                                                                    <span
                                                                        class="badge {{ $bgColor }} {{ $textColor }} fw-semibold">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
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
                                                                            <span
                                                                                class="badge bg-light text-muted border">{{ ucfirst($history->changedBy->roles->first()->name) }}</span>
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
                                                                        <button
                                                                            class="btn btn-sm btn-outline-{{ $color }} timeline-metadata-btn"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#metadata-{{ $history->id }}">
                                                                            <i class="ri-file-list-3-line me-1"></i> View Metadata
                                                                        </button>
                                                                        <div class="collapse mt-3" id="metadata-{{ $history->id }}">
                                                                            <div class="card border-{{ $color }} bg-light">
                                                                                <div class="card-body p-3">
                                                                                    <h6 class="{{ $textColor }} mb-2"><i
                                                                                            class="ri-code-s-slash-line me-1"></i> Technical
                                                                                        Details</h6>
                                                                                    <pre class="mb-0 small"
                                                                                        style="max-height: 300px; overflow-y: auto;">{{ json_encode($history->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
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
                    document.addEventListener('DOMContentLoaded', function () {
                        const observerOptions = {
                            threshold: 0.1,
                            rootMargin: '0px 0px -50px 0px'
                        };

                        const observer = new IntersectionObserver(function (entries) {
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
                    @if($hasAnyQuickActionPermission ?? false)
                    <div class="card border mb-3 ">
                        <div class="card-header bg-dark text-white">
                            <h5 class="card-title mb-0"><i class="ri-flashlight-line me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body p-2">
                            <!-- Payment Status -->
                            @if($canUpdatePaymentStatus ?? false)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted" style="font-size: 11px;">PAYMENT
                                    STATUS</label>
                                <select class="form-select form-select-sm" id="quickPaymentStatus"
                                    onchange="updatePaymentStatus(this.value)">
                                    <option value="unpaid" {{ $booking->payment_status == 'unpaid' ? 'selected' : '' }}>
                                        Unpaid</option>
                                    <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                    <option value="failed" {{ $booking->payment_status == 'failed' ? 'selected' : '' }}>
                                        Failed</option>
                                    <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>
                                        Refunded                                    </option>
                                </select>
                            </div>
                            @endif

                            <!-- Booking Status -->
                            @if($canUpdateStatus ?? false)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted" style="font-size: 11px;">BOOKING
                                    STATUS</label>
                                <select class="form-select form-select-sm" id="quickBookingStatus"
                                    onchange="updateBookingStatus(this.value)">
                                    <option value="inquiry" {{ $booking->status == 'inquiry' ? 'selected' : '' }}>Inquiry
                                    </option>
                                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="schedul_pending"
                                        {{ $booking->status == 'schedul_pending' ? 'selected' : '' }}>Schedule Pending
                                    </option>
                                    <option value="schedul_accepted"
                                        {{ $booking->status == 'schedul_accepted' ? 'selected' : '' }}>Schedule Accepted
                                    </option>
                                    <option value="schedul_decline"
                                        {{ $booking->status == 'schedul_decline' ? 'selected' : '' }}>Schedule Declined
                                    </option>
                                    <option value="reschedul_pending"
                                        {{ $booking->status == 'reschedul_pending' ? 'selected' : '' }}>Reschedule Pending
                                    </option>
                                    <option value="reschedul_accepted"
                                        {{ $booking->status == 'reschedul_accepted' ? 'selected' : '' }}>Reschedule Accepted
                                    </option>
                                    <option value="reschedul_decline"
                                        {{ $booking->status == 'reschedul_decline' ? 'selected' : '' }}>Reschedule Declined
                                    </option>
                                    <option value="reschedul_blocked"
                                        {{ $booking->status == 'reschedul_blocked' ? 'selected' : '' }}>Reschedule Blocked
                                    </option>
                                    <option value="schedul_assign"
                                        {{ $booking->status == 'schedul_assign' ? 'selected' : '' }}>Schedule Assigned
                                    </option>
                                    <option value="schedul_completed"
                                        {{ $booking->status == 'schedul_completed' ? 'selected' : '' }}>Schedule Completed
                                    </option>
                                    <option value="tour_pending" {{ $booking->status == 'tour_pending' ? 'selected' : '' }}>
                                        Tour Pending</option>
                                    <option value="tour_completed"
                                        {{ $booking->status == 'tour_completed' ? 'selected' : '' }}>Tour Completed</option>
                                    <option value="tour_live" {{ $booking->status == 'tour_live' ? 'selected' : '' }}>Tour
                                        Live</option>
                                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="maintenance" {{ $booking->status == 'maintenance' ? 'selected' : '' }}>
                                        Maintenance</option>
                                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                    <option value="expired" {{ $booking->status == 'expired' ? 'selected' : '' }}>Expired
                                    </option>
                                </select>
                            </div>
                            @endif

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
                            @if($canSchedule ?? false)
                            <div class="mb-3">
                                <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#scheduleModal">
                                    <i class="ri-calendar-check-line me-1"></i> Schedule Date
                                </button>
                            </div>
                            @endif


                            <!-- Accept/Decline Schedule (if pending) -->
                            @if(in_array($booking->status, ['schedul_pending', 'reschedul_pending']) && ($canApproval ?? false))
                                <hr class="my-3">

                                <div class="card border-warning mb-3">
                                    <div class="card-header bg-warning-subtle border-warning py-2">
                                        <h6 class="mb-0 text-warning">
                                            <i class="ri-alert-line me-1"></i>
                                            {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }} Approval
                                            Required
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        @if($booking->booking_date)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1"
                                                    style="font-size: 10px; font-weight: 600;">REQUESTED DATE</small>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="ri-calendar-line text-primary"></i>
                                                    <strong
                                                        class="text-dark">{{ $booking->booking_date->format('d M, Y') }}</strong>
                                                    <small class="text-muted">({{ $booking->booking_date->format('l') }})</small>
                                                </div>
                                            </div>
                                        @endif

                                        @if($booking->booking_notes)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1"
                                                    style="font-size: 10px; font-weight: 600;">CUSTOMER NOTES</small>
                                                <div class="alert alert-light border mb-0 py-2">
                                                    <small><i
                                                            class="ri-message-3-line me-1"></i>{{ $booking->booking_notes }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-sm" onclick="acceptScheduleFromShow()">
                                                <i class="ri-check-line me-1"></i> Accept
                                                {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="declineScheduleFromShow()">
                                                <i class="ri-close-line me-1"></i> Decline
                                                {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Booking Assignees (if schedule accepted) -->
                            @if(in_array($booking->status, ['schedul_accepted', 'reschedul_accepted']) && ($canManageAssignees ?? false))
                                <hr class="my-3">

                                <div class="card border-info mb-3">
                                    <div class="card-header bg-info-subtle border-info py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="ri-user-add-line me-1"></i> Booking Assignees
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        @php
                                            $photographerAssignee = $booking->assignees->first(function ($assignee) {
                                                return $assignee->user && $assignee->user->hasRole('photographer');
                                            });
                                        @endphp

                                        @if($photographerAssignee)
                                            <div class="alert alert-success mb-3 py-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <strong class="d-block mb-1">Assigned Photographer</strong>
                                                        <small class="d-block mb-1">
                                                            <i
                                                                class="ri-user-line me-1"></i>{{ $photographerAssignee->user->firstname }}
                                                            {{ $photographerAssignee->user->lastname }}
                                                        </small>
                                                        @if($photographerAssignee->date)
                                                            <small class="d-block mb-1">
                                                                <i class="ri-calendar-line me-1"></i>Date:
                                                                {{ \Carbon\Carbon::parse($photographerAssignee->date)->format('d M, Y') }}
                                                            </small>
                                                        @endif
                                                        @if($photographerAssignee->time)
                                                            <small class="d-block">
                                                                <i class="ri-time-line me-1"></i>Time:
                                                                {{ \Carbon\Carbon::parse($photographerAssignee->time)->format('h:i A') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted small mb-3">No photographer assigned yet. Click the button below to
                                                assign one.</p>
                                        @endif

                                        @if($canManageAssignees ?? false)
                                        <button class="btn btn-info btn-sm w-100" data-bs-toggle="modal"
                                            data-bs-target="#assignBookingModal">
                                            <i class="ri-user-add-line me-1"></i> Assign Photographer
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <hr class="my-3">

                            <!-- Edit Button -->
                            @can('booking_edit')
                            <div class="mb-2">
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-info btn-sm w-100">
                                    <i class="ri-edit-line me-1"></i> Edit Booking
                                </a>
                            </div>
                            @endcan

                            <!-- Delete Button -->
                            {{-- <div class="mb-0">
                                <button class="btn btn-danger btn-sm w-100" onclick="deleteBooking()">
                                    <i class="ri-delete-bin-line me-1"></i> Delete Booking
                                </button>
                            </div> --}}
                        </div>
                    </div>
                    @endif

                    <!-- QR Code Information -->
                    @if($booking && $booking->qr)
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0"><i class="ri-qr-code-line me-2"></i>QR Code</h6>
                            </div>
                            <div class="card-body text-center p-3">
                                <div class="mb-3">
                                    @if($booking->qr->image)
                                        <img src="{{ asset('storage/' . $booking->qr->image) }}" alt="QR Code" class="img-fluid"
                                            style="max-width: 200px;">
                                    @elseif($booking->qr->qr_link)
                                        <div class="qr-code-container">
                                            {!! $booking->qr->qr_code_image !!}
                                        </div>
                                    @elseif($booking->qr->code)
                                        @php
                                            // Generate QR code from code if qr_link doesn't exist
                                            $qrUrl = 'https://qr.proppik.com/' . $booking->qr->code;
                                            $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)
                                                ->format('svg')
                                                ->color(40, 41, 115)
                                                ->generate($qrUrl);
                                        @endphp
                                        <div class="qr-code-container">
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="ri-qr-code-line fs-3"></i>
                                            <p class="mb-0 mt-2 small">QR Code not generated yet</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="row text-start">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block" style="font-size: 10px;">QR NAME</small>
                                        <strong class="d-block">{{ $booking->qr->name ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block" style="font-size: 10px;">QR CODE</small>
                                        <code class="d-block">{{ $booking->qr->code }}</code>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <small class="text-muted d-block" style="font-size: 10px;">QR LINK</small>
                                        <a href="https://qr.proppik.com/{{ $booking->qr->code }}" target="_blank" class="text-break small">
                                            https://qr.proppik.com/{{ $booking->qr->code }}
                                            <i class="ri-external-link-line ms-1"></i>
                                        </a>
                                    </div>
                                    @if($booking->qr->qr_link)
                                        <div class="col-12 mb-2">
                                            <small class="text-muted d-block" style="font-size: 10px;">LIVE LINK</small>
                                            <a href="{{ $booking->getTourLiveUrl() }}" target="_blank" class="text-truncate d-block small"
                                                style="max-width: 100%;">
                                                <code>{{ Str::limit($booking->getTourLiveUrl(), 50) }}</code>
                                                <i class="ri-external-link-line ms-1"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                @if($booking->qr->image)
                                    <div class="mt-3">
                                        <a href="{{ asset('storage/' . $booking->qr->image) }}" download class="btn btn-primary btn-sm w-100">
                                            <i class="ri-download-line me-1"></i> Download QR
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0"><i class="ri-qr-code-line me-2"></i>QR Code</h6>
                            </div>
                            <div class="card-body text-center p-3">
                                <div class="alert alert-info mb-0">
                                    <i class="ri-qr-code-line fs-3"></i>
                                    <p class="mb-0 mt-2 small">QR Code not assigned to this booking yet</p>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                        <input class="form-check-input" type="radio" name="schedule_mode"
                                            id="schedule-mode-default" value="default" checked>
                                        <label class="form-check-label" for="schedule-mode-default">Default</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="schedule_mode"
                                            id="schedule-mode-any" value="any">
                                        <label class="form-check-label" for="schedule-mode-any">Pick Any Day</label>
                                    </div>
                                </div>
                            </div>
                            <label for="schedule-date" class="form-label">Select Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white" id="calendar-icon-trigger"
                                    style="cursor: pointer;" title="Open calendar">
                                    <i class="ri-calendar-line"></i>
                                </span>
                                <input type="text" class="form-control" id="schedule-date" name="schedule_date"
                                    placeholder="Click to select date" required autocomplete="off">
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

    <!-- Assign Booking to Photographer Modal -->
    <div class="modal fade" id="assignBookingModal" tabindex="-1" aria-labelledby="assignBookingModalLabel"
        aria-hidden="true"
        data-photographer-from="{{ \App\Models\Setting::where('name', 'photographer_available_from')->value('value') ?? '08:00' }}"
        data-photographer-to="{{ \App\Models\Setting::where('name', 'photographer_available_to')->value('value') ?? '21:00' }}"
        data-photographer-duration="{{ \App\Models\Setting::where('name', 'photographer_working_duration')->value('value') ?? '60' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignBookingModalLabel">Assign Booking to Photographer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignBookingForm" method="POST" action="{{ route('admin.booking-assignees.store') }}">
                    @csrf
                    <div class="modal-body">
                        <!-- Booking Details Section -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2">Booking Details</h6>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <strong>Customer Name:</strong>
                                    <p id="modalCustomer" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Pin Code:</strong>
                                    <p id="modalPincode" class="mb-1">-</p>
                                </div>
                                <div class="col-md-12">
                                    <strong>Address:</strong>
                                    <p id="modalAddress" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>City:</strong>
                                    <p id="modalCity" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>State:</strong>
                                    <p id="modalState" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Photographer Select -->
                        <div class="mb-3 mt-3">
                            <label for="assignPhotographer" class="form-label">Select Photographer <span
                                    class="text-danger">*</span></label>
                            <select id="assignPhotographer" name="user_id" class="form-select" required>
                                <option value="">-- Select Photographer --</option>
                                @foreach ($photographers ?? [] as $photographer)
                                    <option value="{{ $photographer->id }}">{{ $photographer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Assignment Details Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="modalDate" class="form-label">Booking Date</label>
                                <input type="date" id="modalDate" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="assignTime" class="form-label">Assign Time <span
                                        class="text-danger">*</span></label>
                                <select id="assignTime" name="time" class="form-select" disabled required>
                                    <option value="">Select a time</option>
                                </select>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="slotMode" id="slotModeAvailable"
                                            value="available" checked>
                                        <label class="form-check-label" for="slotModeAvailable">Available slots
                                            (default)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="slotMode" id="slotModeAny"
                                            value="any">
                                        <label class="form-check-label" for="slotModeAny">Pick any</label>
                                    </div>
                                </div>
                                <div id="assignTimeHelper" class="form-text text-muted small">Select a photographer first to
                                    see available slots from the API, or choose "Pick any" to ignore conflicts.</div>
                            </div>
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
                    const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}/change-status`, {
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
                    const response = await fetch(`${baseUrl}/admin/bookings/${bookingId}/change-status`, {
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
        document.getElementById('calendar-icon-trigger').addEventListener('click', function () {
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
            const customerNotes = '{{ addslashes($booking->booking_notes ?? "") }}';
            const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            const htmlContent = `
                <div class="text-start mb-3">
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                        <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                    </div>
                    ${customerNotes && customerNotes.trim() ? `
                        <div class="mb-3">
                            <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>
                            <div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">
                                <div class="d-flex align-items-start">
                                    <i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                    <div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(customerNotes)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    <div>
                        <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Admin Notes (Optional):</strong></label>
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
            const customerNotes = '{{ addslashes($booking->booking_notes ?? "") }}';
            const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            const htmlContent = `
                <div class="text-start mb-3">
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                        <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                    </div>
                    ${customerNotes && customerNotes.trim() ? `
                        <div class="mb-3">
                            <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>
                            <div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">
                                <div class="d-flex align-items-start">
                                    <i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                    <div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(customerNotes)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    <div>
                        <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Reason for Decline:</strong> <span class="text-danger">*</span></label>
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

        // Handle assignment form submission
        document.addEventListener('DOMContentLoaded', function () {
            const assignForm = document.getElementById('assignBookingForm');
            if (assignForm) {
                assignForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const bookingId = {{ $booking->id }};
                    const userId = document.getElementById('assignPhotographer').value;
                    const time = document.getElementById('assignTime').value;

                    if (!userId || !time) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Required Fields',
                            text: 'Please select a photographer and set a time'
                        });
                        return;
                    }

                    // Client-side double check: ensure selected time is within allowed range and aligned to slots
                    const assignTimeInput = document.getElementById('assignTime');
                    const modalEl = document.getElementById('assignBookingModal');
                    const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
                    const availableTo = modalEl?.dataset?.photographerTo || '21:00';
                    const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

                    function toMinutesLocal(t) {
                        const parts = (t || '').split(':'); if (parts.length < 2) return null; return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                    }

                    const timeMins = toMinutesLocal(time);
                    const fromMins = toMinutesLocal(availableFrom);
                    const toMins = toMinutesLocal(availableTo);

                    if (timeMins === null) {
                        Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'Please choose a valid time.' });
                        return;
                    }

                    if (toMins < fromMins) {
                        Swal.fire({ icon: 'warning', title: 'No Available Slot', text: 'No available slots configured. Please update settings.' });
                        return;
                    }

                    if (timeMins < fromMins || timeMins > toMins) {
                        Swal.fire({ icon: 'warning', title: 'Time Not Allowed', text: `Selected time is outside allowed photographer availability (${availableFrom} — ${availableTo}).` });
                        return;
                    }

                    // Disable submit button to prevent double submission
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';

                    const formData = new FormData();
                    formData.append('booking_id', bookingId);
                    formData.append('user_id', userId);
                    formData.append('time', time);
                    formData.append('_token', csrfToken);

                    const storeUrl = this.getAttribute('action');

                    fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => Promise.reject(err));
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Close modal
                            const modalElement = document.getElementById('assignBookingModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Booking assigned successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page to show updated assignment
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            let errorMessage = 'Failed to assign booking';
                            if (error.message) {
                                errorMessage = error.message;
                            } else if (error.errors) {
                                errorMessage = Object.values(error.errors).flat().join(', ');
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        })
                        .finally(() => {
                            // Re-enable submit button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Assign';
                        });
                });
            }

            // Assign Booking to Photographer Modal - Slot loading functionality
            function initializeAssignModal() {
                const assignPhotographerEl = document.getElementById('assignPhotographer');
                const assignTimeEl = document.getElementById('assignTime');
                const helper = document.getElementById('assignTimeHelper');
                const slotModeAvailable = document.getElementById('slotModeAvailable');
                const slotModeAny = document.getElementById('slotModeAny');

                const setHelper = (msg) => { if (helper) helper.textContent = msg; };
                const resetSelect = () => { assignTimeEl.disabled = true; assignTimeEl.innerHTML = '<option value="">Select a time</option>'; assignTimeEl.value = ''; };

                setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
                resetSelect();
                if (slotModeAvailable) slotModeAvailable.checked = true;
                if (slotModeAny) slotModeAny.checked = false;

                const modalEl = document.getElementById('assignBookingModal');
                const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
                const availableTo = modalEl?.dataset?.photographerTo || '21:00';
                const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

                function toMinutes(t) {
                    const parts = (t || '').split(':');
                    if (parts.length < 2) return null;
                    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                }

                function formatHM(m) {
                    const h = Math.floor(m / 60).toString().padStart(2, '0');
                    const mm = (m % 60).toString().padStart(2, '0');
                    return `${h}:${mm}`;
                }

                function formatDisplay(m) {
                    const hours = Math.floor(m / 60);
                    const minutes = m % 60;
                    const period = hours >= 12 ? 'PM' : 'AM';
                    let h12 = hours % 12;
                    if (h12 === 0) h12 = 12;
                    return `${h12}:${minutes.toString().padStart(2, '0')} ${period}`;
                }

                const fromM = toMinutes(availableFrom);
                const toM = toMinutes(availableTo);
                const slotStep = 15;

                const getSlotMode = () => (slotModeAny?.checked ? 'any' : 'available');

                function buildAllSlots() {
                    assignTimeEl.innerHTML = '<option value="">Select a time</option>';
                    if (toM < fromM) return;
                    for (let t = fromM; t <= toM; t += slotStep) {
                        const candidateEnd = t + (workingDuration || 60);
                        if (candidateEnd > toM) continue;
                        const opt = document.createElement('option');
                        opt.value = formatHM(t);
                        opt.textContent = formatDisplay(t);
                        assignTimeEl.appendChild(opt);
                    }
                    if (assignTimeEl.options.length > 1) {
                        assignTimeEl.disabled = false;
                        assignTimeEl.value = assignTimeEl.options[1].value;
                    } else {
                        assignTimeEl.disabled = true;
                    }
                }

                function loadSlots() {
                    if (!assignPhotographerEl.value) {
                        resetSelect();
                        setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
                        return;
                    }

                    if (toM < fromM) {
                        resetSelect();
                        setHelper('No available slots for photographers. Please update settings.');
                        return;
                    }

                    const dateVal = document.getElementById('modalDate').value;
                    if (!dateVal) {
                        resetSelect();
                        setHelper('Please select a booking date first.');
                        return;
                    }

                    if (getSlotMode() === 'any') {
                        buildAllSlots();
                        setHelper(`Pick any slot between ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
                        return;
                    }

                    setHelper('Loading photographer slots...');
                    assignTimeEl.disabled = true;
                    assignTimeEl.innerHTML = '<option value="">Loading...</option>';

                    fetch(`${apiBaseUrl}/booking-assignees/slots?date=${encodeURIComponent(dateVal)}&user_id=${encodeURIComponent(assignPhotographerEl.value)}`, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 403) {
                                setHelper('Forbidden to view slots for selected user.');
                                resetSelect();
                                return Promise.reject({ message: 'Forbidden' });
                            }
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(json => {
                        if (!json || json.success === false) {
                            setHelper(json?.message || 'Failed to load slots');
                            resetSelect();
                            return;
                        }

                        const duration = workingDuration || 60;
                        const occupiedIntervals = [];

                        (json.data || []).forEach(s => {
                            if (!s.time) return;
                            const parts = s.time.split(':');
                            if (parts.length < 2) return;
                            const start = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                            const end = start + duration;
                            occupiedIntervals.push({ start, end });
                        });

                        assignTimeEl.innerHTML = '<option value="">Select a time</option>';
                        for (let t = fromM; t <= toM; t += slotStep) {
                            const candidateStart = t;
                            const candidateEnd = t + duration;
                            if (candidateEnd > toM) continue;

                            let overlaps = false;
                            for (const occ of occupiedIntervals) {
                                if (candidateStart < occ.end && candidateEnd > occ.start) {
                                    overlaps = true;
                                    break;
                                }
                            }

                            if (overlaps) continue;

                            const opt = document.createElement('option');
                            opt.value = formatHM(t);
                            opt.textContent = formatDisplay(t);
                            assignTimeEl.appendChild(opt);
                        }

                        if (assignTimeEl.options.length <= 1) {
                            resetSelect();
                            setHelper('No available slots on this date for selected photographer.');
                        } else {
                            assignTimeEl.disabled = false;
                            if (!assignTimeEl.value && assignTimeEl.options.length > 1) {
                                assignTimeEl.value = assignTimeEl.options[1].value;
                            }
                            assignTimeEl.focus();
                            setHelper(`Available slots: ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
                        }
                    })
                    .catch(err => {
                        console.error('Error loading slots:', err);
                        setHelper(err?.message || 'Failed to load slots.');
                        resetSelect();
                    });
                }

                assignPhotographerEl.onchange = loadSlots;
                if (slotModeAvailable) slotModeAvailable.onchange = loadSlots;
                if (slotModeAny) slotModeAny.onchange = loadSlots;
            }

            // Initialize when modal is shown
            const assignModalEl = document.getElementById('assignBookingModal');
            if (assignModalEl) {
                assignModalEl.addEventListener('show.bs.modal', function () {
                    const dateInput = document.getElementById('modalDate');
                    dateInput.value = '{{ $booking->booking_date ? $booking->booking_date->format("Y-m-d") : "" }}';
                    initializeAssignModal();
                });
            }
        });
    </script>
@endsection