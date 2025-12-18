@extends('frontend.layouts.base', ['title' => 'Booking Details - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/setup_page.css') }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/booking_show_page.css') }}">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<!-- Booking Details Hero (New Theme style, matches policy pages) -->
<section class="py-5 bg-primary text-white mt-5">
    <div class="container pt-5 pb-2">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <p class="text-uppercase fw-bold small mb-2">Booking</p>
                <h1 class="display-5 fw-bold mb-3">Booking Details</h1>
                <p class="lead mb-0">View your booking status, schedule, and payment information.</p>
            </div>
        </div>
    </div>
</section>

<div class="page bg-setup-form py-4">
    <div class="container">
        

        <!-- Booking Header -->
        <div class="booking-header text-center mb-4">
            <div class="container position-relative" style="z-index: 1;">
                <div class="mb-3">
                    <i class="fa-solid fa-receipt" style="font-size: 3rem; opacity: 0.9; margin-bottom: 1rem; display: block;"></i>
                    <h2 class="mb-2" style="font-weight: 700; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">Booking #{{ $booking->id }}</h2>
                </div>
                <div class="mb-4 d-flex flex-wrap justify-content-center gap-3">
                    @php
                        $status = $booking->status ?? 'pending';
                        $statusDisplay = $statusText ?? ucfirst(str_replace('_', ' ', $status));
                        
                        // Add count for pending/declined/blocked statuses
                        if ($isBlocked) {
                            $statusDisplay = 'Blocked (' . $attemptCount . '/' . $maxAttempts . ')';
                        } elseif (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                            $statusDisplay = $statusText . ' (' . $attemptCount . '/' . $maxAttempts . ')';
                        } elseif (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                            $statusDisplay = $statusText . ' (' . $attemptCount . '/' . $maxAttempts . ')';
                        }
                    @endphp
                    <span class="status-badge status-{{ str_replace(' ', '_', strtolower($status)) }}">
                        <i class="fa-solid fa-circle-check me-2"></i>{{ $statusDisplay }}
                    </span>
                    @if($booking->payment_status)
                        <span class="status-badge status-{{ $booking->payment_status }}">
                            <i class="fa-solid fa-credit-card me-2"></i>Payment: {{ ucfirst($booking->payment_status) }}
                        </span>
                    @endif
                </div>
                <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                    <div>
                        <i class="fa-solid fa-calendar-plus me-2"></i>
                        <span class="opacity-90">Created: {{ $booking->created_at->format('F d, Y') }}</span>
                    </div>
                    @if($booking->updated_at && $booking->updated_at != $booking->created_at)
                        <div>
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>
                            <span class="opacity-90">Updated: {{ $booking->updated_at->format('F d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Property Details -->
            <div class="col-md-6">
                <div class="info-card property">
                    <div class="section-header">
                        <div class="card-icon">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Property Details</h5>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-4">
                            <div class="info-label">Owner Type</div>
                            <div class="info-value">{{ $booking->owner_type ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Company Name</div>
                            <div class="info-value">{{ $booking->firm_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">GST No</div>
                            <div class="info-value">{{ $booking->gst_no ?? '-' }}</div>
                        </div>
                    </div>

                    @php
                        $propertyTypeName = $booking->propertyType?->name ?? '';
                        $isCommercial = strtolower($propertyTypeName) === 'commercial';
                    @endphp

                    @if($isCommercial)
                        {{-- Commercial: Property Type | Property Sub Type | Furnish Type in one row --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <div class="info-label">Property Type</div>
                                <div class="info-value">{{ $booking->propertyType?->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Property Sub Type</div>
                                <div class="info-value">{{ $booking->propertySubType?->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Furnish Type</div>
                                <div class="info-value">{{ $booking->furniture_type ?? '-' }}</div>
                            </div>
                        </div>
                    @else
                        {{-- Residential/Other: Property Type | Property Sub Type in one row, Furnish Type | Size in another row --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <div class="info-label">Property Type</div>
                                <div class="info-value">{{ $booking->propertyType?->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Property Sub Type</div>
                                <div class="info-value">{{ $booking->propertySubType?->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <div class="info-label">Furnish Type</div>
                                <div class="info-value">{{ $booking->furniture_type ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Size (BHK / RK)</div>
                                <div class="info-value">{{ $booking->bhk?->name ?? '-' }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="info-label">Super Built-up Area</div>
                    <div class="info-value">{{ number_format($booking->area ?? 0) }} sq. ft.</div>
                </div>
            </div>

            <!-- Address Details -->
            <div class="col-md-6">
                <div class="info-card address">
                    <div class="section-header">
                        <div class="card-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Address Details</h5>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <div class="info-label">House/Office No.</div>
                            <div class="info-value">{{ $booking->house_no ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Building/Society</div>
                            <div class="info-value">{{ $booking->building ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <div class="info-label">City</div>
                            <div class="info-value">{{ $booking->city?->name ?? 'Ahmedabad' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Pincode</div>
                            <div class="info-value">{{ $booking->pin_code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="info-label">Full Address</div>
                    <div class="info-detail">{{ $booking->full_address ?? '-' }}</div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="col-md-6">
                <div class="info-card payment">
                    <div class="section-header">
                        <div class="card-icon">
                            <i class="fa-solid fa-indian-rupee-sign"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Pricing & Payment</h5>
                    </div>
                    <div class="price-display d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <div>₹{{ number_format($priceToShow, 0) }}</div>
                        <span class="status-badge status-{{ $booking->payment_status ?? 'pending' }}">
                            {{ ucfirst($booking->payment_status ?? 'pending') }}
                        </span>
                    </div>
                    @if($booking->cashfree_order_id)
                        <div class="info-label">Order ID</div>
                        <div class="info-value">{{ $booking->cashfree_order_id }}</div>
                    @endif
                    @if($booking->cashfree_reference_id)
                        <div class="info-label">Reference ID</div>
                        <div class="info-value">{{ $booking->cashfree_reference_id }}</div>
                    @endif
                    @if($booking->cashfree_payment_method)
                        <div class="info-label">Payment Method</div>
                        <div class="info-value">{{ ucfirst($booking->cashfree_payment_method) }}</div>
                    @endif
                    @if($booking->cashfree_payment_at)
                        <div class="info-label">Payment Date</div>
                        <div class="info-value">{{ $booking->cashfree_payment_at->format('F d, Y h:i A') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                    @if($booking->payment_status !== 'paid' && $booking->isReadyForPayment())
                                        <button type="button" class="btn btn-success action-btn" onclick="initiatePayment({{ $booking->id }})">
                                            <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                                        </button>
                                    @endif
                                    
                                    @if($booking->payment_status === 'paid')
                                        <button type="button" class="btn btn-outline-success action-btn" onclick="downloadReceipt({{ $booking->id }})">
                                            <i class="fa-solid fa-download me-2"></i>Download Receipt
                                        </button>
                                    @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Details -->
            <div class="col-md-6">
                <div class="info-card schedule">
                    <div class="section-header">
                        <div class="card-icon">
                            <i class="fa-solid fa-calendar"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Schedule & Status</h5>
                    </div>
                    
                    @php
                        $status = $booking->status ?? 'pending';
                        $isPaymentPaid = ($booking->payment_status ?? 'pending') === 'paid';
                        $showScheduledDate = $booking->booking_date && !in_array($status, ['schedul_decline', 'reschedul_decline', 'reschedul_blocked']);
                    @endphp
                    
                    @if($isPaymentPaid)
                        @if($isBlocked)
                            <div class="alert alert-danger py-2 mb-3" role="alert">
                                <small class="d-block mb-1"><i class="fa-solid fa-ban me-1"></i><strong>Scheduling Blocked</strong></small>
                                <small class="text-muted d-block mb-2">Maximum attempts reached ({{ $attemptCount }}/{{ $maxAttempts }})</small>
                                @php
                                    $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                @endphp
                                <small class="d-block">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                </small>
                            </div>
                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                            <div class="mb-3">
                                <div class="info-label">Status</div>
                                <div class="info-value text-warning">
                                    <i class="fa-solid fa-clock me-1"></i>Awaiting Admin Approval
                                </div>
                            </div>
                            <div class="row g-3 mb-2">
                                @if($showScheduledDate)
                                    <div class="col-md-6">
                                        <div class="info-label">Requested Date</div>
                                        <div class="info-value">{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}</div>
                                    </div>
                                @endif
                                <div class="{{ $showScheduledDate ? 'col-md-6' : 'col-md-12' }}">
                                    <div class="info-label">Attempt</div>
                                    <div class="info-value">Attempt {{ $attemptCount }} of {{ $maxAttempts }}</div>
                                </div>
                            </div>
                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                            <div class="mb-3">
                                <div class="info-label">Status</div>
                                <div class="info-value text-danger">
                                    <i class="fa-solid fa-times-circle me-1"></i>Schedule Declined
                                </div>
                            </div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <div class="info-label">Attempt</div>
                                    <div class="info-value">Attempt {{ $attemptCount }} of {{ $maxAttempts }}</div>
                                </div>
                            </div>
                            @if($declineReason)
                                <div class="alert alert-danger py-3 mb-3" role="alert" style="border-left: 4px solid #dc3545;">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-exclamation-triangle me-2 mt-1" style="color: #dc3545; font-size: 1.1rem;"></i>
                                        <div>
                                            <strong class="d-block mb-2" style="color: #721c24;">Reason for Decline</strong>
                                            <p class="mb-0 small" style="color: #721c24; line-height: 1.6;">
                                                {{ $declineReason }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($attemptCount >= $maxAttempts)
                                <div class="alert alert-danger py-2 mb-3" role="alert">
                                    @php
                                        $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                    @endphp
                                    <small class="d-block">
                                        <i class="fa-solid fa-ban me-1"></i>
                                        {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                    </small>
                                </div>
                            @else
                                <div class="mb-3">
                                    <small class="text-warning">
                                        <i class="fa-solid fa-info-circle me-1"></i>You can request again ({{ $maxAttempts - $attemptCount }} {{ ($maxAttempts - $attemptCount) == 1 ? 'attempt' : 'attempts' }} left)
                                    </small>
                                </div>
                            @endif
                        @elseif($status === 'confirmed')
                            {{-- Payment paid and status is confirmed - show action required message --}}
                            <div class="alert alert-warning py-3 mb-3" role="alert" style="border-left: 4px solid #ffc107;">
                                <div class="d-flex align-items-start">
                                    <i class="fa-solid fa-exclamation-circle me-2 mt-1" style="color: #ffc107; font-size: 1.1rem;"></i>
                                    <div>
                                        <strong class="d-block mb-2" style="color: #856404;">Action Required</strong>
                                        <p class="mb-2 small" style="color: #856404; line-height: 1.6;">
                                            Your booking has been confirmed! Please select your schedule appointment so our team can visit your property on this date for tour and photography.
                                        </p>
                                        @if($showScheduledDate)
                                            <p class="mb-0 small" style="color: #856404; line-height: 1.6;">
                                                <i class="fa-solid fa-calendar-check me-1"></i>Scheduled Date: <strong>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}</strong>
                                            </p>
                                        @else
                                            <p class="mb-0 small" style="color: #856404; line-height: 1.6;">
                                                <i class="fa-solid fa-arrow-right me-1"></i>Use the "Schedule" button to select your preferred date.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($showScheduledDate)
                                <div class="mb-3">
                                    <div class="info-label">Scheduled Date</div>
                                    <div class="info-value text-success">
                                        <i class="fa-solid fa-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}
                                    </div>
                                </div>
                            @endif
                        @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted']) && $showScheduledDate)
                            @php
                                // Get photographer assignee (user with photographer role)
                                $photographerAssignee = $booking->assignees->first(function($assignee) {
                                    return $assignee->user && $assignee->user->hasRole('photographer');
                                });
                                $photographer = $photographerAssignee?->user;
                                $isPhotographerAssigned = $photographer && $photographerAssignee;
                            @endphp
                            
                            @if($isPhotographerAssigned)
                                {{-- Photographer Assigned - Show photographer details --}}
                                <div class="alert alert-success py-3 mb-3" role="alert" style="border-left: 4px solid #28a745; background-color: #d4edda;">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-user-check me-2 mt-1" style="color: #28a745; font-size: 1.1rem;"></i>
                                        <div class="flex-grow-1">
                                            <strong class="d-block mb-2" style="color: #155724;">Photographer Assigned</strong>
                                            <div class="row g-2 mb-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="fa-solid fa-user me-2" style="color: #155724;"></i>
                                                        <strong style="color: #155724;">Name:</strong>
                                                        <span class="ms-2" style="color: #155724;">{{ $photographer->firstname }} {{ $photographer->lastname }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="fa-solid fa-phone me-2" style="color: #155724;"></i>
                                                        <strong style="color: #155724;">Phone:</strong>
                                                        <a href="tel:{{ $photographer->mobile }}" class="ms-2 text-decoration-none" style="color: #155724;">
                                                            {{ $photographer->mobile }}
                                                        </a>
                                                    </div>
                                                </div>
                                                @if($photographerAssignee->date)
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="fa-solid fa-calendar-check me-2" style="color: #155724;"></i>
                                                            <strong style="color: #155724;">Scheduled Date:</strong>
                                                            <span class="ms-2" style="color: #155724;">{{ \Carbon\Carbon::parse($photographerAssignee->date)->format('F d, Y') }}</span>
                                                        </div>
                                                    </div>
                                                    @if($photographerAssignee->time)
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <i class="fa-solid fa-clock me-2" style="color: #155724;"></i>
                                                                <strong style="color: #155724;">Time:</strong>
                                                                <span class="ms-2" style="color: #155724;">{{ \Carbon\Carbon::parse($photographerAssignee->time)->format('h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    @elseif($booking->booking_time)
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <i class="fa-solid fa-clock me-2" style="color: #155724;"></i>
                                                                <strong style="color: #155724;">Time:</strong>
                                                                <span class="ms-2" style="color: #155724;">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-success" style="border-color: #c3e6cb !important;">
                                                <p class="mb-0 small" style="color: #155724; line-height: 1.6;">
                                                    <i class="fa-solid fa-info-circle me-1"></i>
                                                    <strong>Note:</strong> Please make sure you are available on the scheduled date and around the specified time. The photographer will visit your property for tour and photography.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Info message for accepted status - waiting for photographer assignment --}}
                                <div class="alert alert-info py-3 mb-3" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #d1ecf1;">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-clock me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                        <div>
                                            <strong class="d-block mb-2" style="color: #0c5460;">Photographer Assignment in Progress</strong>
                                            <p class="mb-0 small" style="color: #055160; line-height: 1.6;">
                                                Your booking date has been accepted! In a short time, a photographer will be assigned and a specific time will be set for when the photographer visits your property to start your property tour photography. Please wait for the admin to assign the photographer and schedule the visit time.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value text-success">
                                    <i class="fa-solid fa-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}
                                </div>
                            </div>
                            
                            {{-- Admin notes only visible to admin users (not shown to customers) --}}
                        @elseif($status === 'schedul_assign' && $showScheduledDate)
                            @php
                                // Get photographer assignee (user with photographer role)
                                $photographerAssignee = $booking->assignees->first(function($assignee) {
                                    return $assignee->user && $assignee->user->hasRole('photographer');
                                });
                                $photographer = $photographerAssignee?->user;
                                $isPhotographerAssigned = $photographer && $photographerAssignee;
                                
                                // Get assigned time from assignee or booking
                                $assignedTime = $photographerAssignee?->time ? \Carbon\Carbon::parse($photographerAssignee->time)->format('h:i A') : ($booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') : null);
                                $assignedDate = $photographerAssignee?->date ? \Carbon\Carbon::parse($photographerAssignee->date)->format('F d, Y') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') : null);
                                
                                // Get support contact info
                                $supportEmail = \App\Models\Setting::where('name', 'support_email')->value('value') ?? 'support@proppik.in';
                                $supportPhone = \App\Models\Setting::where('name', 'support_phone')->value('value') ?? '+91-XXXXXXXXXX';
                            @endphp
                            
                            @if($isPhotographerAssigned)
                                {{-- Photographer Assigned - Show assignment details with message --}}
                                <div class="alert alert-success py-3 mb-3" role="alert" style="border-left: 4px solid #28a745; background-color: #d4edda;">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-user-check me-2 mt-1" style="color: #28a745; font-size: 1.1rem;"></i>
                                        <div class="flex-grow-1">
                                            <strong class="d-block mb-2" style="color: #155724;">Photographer Assigned</strong>
                                            <div class="row g-2 mb-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="fa-solid fa-user me-2" style="color: #155724;"></i>
                                                        <strong style="color: #155724;">Photographer Name:</strong>
                                                        <span class="ms-2" style="color: #155724;">{{ $photographer->firstname }} {{ $photographer->lastname }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="fa-solid fa-phone me-2" style="color: #155724;"></i>
                                                        <strong style="color: #155724;">Phone:</strong>
                                                        <a href="tel:{{ $photographer->mobile }}" class="ms-2 text-decoration-none" style="color: #155724;">
                                                            {{ $photographer->mobile }}
                                                        </a>
                                                    </div>
                                                </div>
                                                @if($assignedDate)
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="fa-solid fa-calendar-check me-2" style="color: #155724;"></i>
                                                            <strong style="color: #155724;">Date:</strong>
                                                            <span class="ms-2" style="color: #155724;">{{ $assignedDate }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($assignedTime)
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="fa-solid fa-clock me-2" style="color: #155724;"></i>
                                                            <strong style="color: #155724;">Time:</strong>
                                                            <span class="ms-2" style="color: #155724;">{{ $assignedTime }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-success" style="border-color: #c3e6cb !important;">
                                                <p class="mb-2 small" style="color: #155724; line-height: 1.6;">
                                                    <i class="fa-solid fa-info-circle me-1"></i>
                                                    <strong>Important:</strong> Your booking has been assigned to photographer <strong>{{ $photographer->firstname }} {{ $photographer->lastname }}</strong> (Phone: <a href="tel:{{ $photographer->mobile }}" class="text-decoration-none" style="color: #155724;">{{ $photographer->mobile }}</a>). 
                                                    @if($assignedTime)
                                                        The photographer will visit your property around <strong>{{ $assignedTime }}</strong>.
                                                    @endif
                                                </p>
                                                <p class="mb-0 small" style="color: #155724; line-height: 1.6;">
                                                    Please make sure you are available at your property location at the specified time. If you have any doubts or questions, please contact the photographer directly or our support team:
                                                    <br><i class="fa-solid fa-phone me-1"></i><strong>Support Phone:</strong> <a href="tel:{{ $supportPhone }}" class="text-decoration-none" style="color: #155724;">{{ $supportPhone }}</a>
                                                    <br><i class="fa-solid fa-envelope me-1"></i><strong>Support Email:</strong> <a href="mailto:{{ $supportEmail }}" class="text-decoration-none" style="color: #155724;">{{ $supportEmail }}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value text-success">
                                    <i class="fa-solid fa-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}
                                </div>
                            </div>
                            
                            {{-- Admin notes only visible to admin users (not shown to customers) --}}
                            @php
                                $isAdmin = Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'));
                            @endphp
                            @if($adminNotes && $isAdmin)
                                <div class="alert alert-secondary py-3 mb-3" role="alert" style="border-left: 4px solid #6c757d;">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-user-shield me-2 mt-1" style="color: #6c757d; font-size: 1.1rem;"></i>
                                        <div>
                                            <strong class="d-block mb-2" style="color: #383d41;">Admin Notes</strong>
                                            <p class="mb-0 small" style="color: #383d41; line-height: 1.6;">
                                                {{ $adminNotes }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @elseif($showScheduledDate)
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value text-success">
                                    <i class="fa-solid fa-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="info-label">Status</div>
                                <div class="info-value text-muted">
                                    <i class="fa-solid fa-calendar me-1"></i>Not Scheduled
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Payment not paid - show informational message --}}
                        <div class="alert alert-info py-3 mb-3" role="alert" style="border-left: 4px solid #0dcaf0;">
                            <div class="d-flex align-items-start">
                                <i class="fa-solid fa-info-circle me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                <div>
                                    <strong class="d-block mb-2" style="color: #0c5460;">Action Required</strong>
                                    <p class="mb-2 small" style="color: #055160; line-height: 1.6;">
                                        Please make payment to confirm your order. Once payment is completed, you can schedule a date for our team to visit your property for tour and photography.
                                    </p>
                                    <p class="mb-0 small" style="color: #055160; line-height: 1.6;">
                                        <i class="fa-solid fa-arrow-right me-1"></i>Complete the payment to proceed with scheduling.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        @if($booking->booking_date)
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}</div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value text-muted">Not Scheduled</div>
                            </div>
                        @endif
                    @endif
                    
                    @if($booking->booking_notes)
                        <div class="mb-3">
                            <div class="info-label">Notes</div>
                            <div class="info-detail">{{ $booking->booking_notes }}</div>
                        </div>
                    @endif
                    
                    <div class="row mt-3 pt-3 border-top">
                        <div class="col-md-6">
                            <div class="info-label">Current Status</div>
                            <div class="info-value mt-1">
                                @php
                                    $statusDisplay = $statusText ?? ucfirst(str_replace('_', ' ', $status));
                                    if ($isBlocked) {
                                        $statusDisplay = 'Blocked (' . $attemptCount . '/' . $maxAttempts . ')';
                                    } elseif (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                                        $statusDisplay = $statusText . ' (' . $attemptCount . '/' . $maxAttempts . ')';
                                    } elseif (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                                        $statusDisplay = $statusText . ' (' . $attemptCount . '/' . $maxAttempts . ')';
                                    }
                                @endphp
                                <span class="status-badge status-{{ str_replace(' ', '_', strtolower($status)) }}">
                                    {{ $statusDisplay }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                
                                
                                @if($booking->payment_status === 'paid')
                                    @php
                                        $scheduledDate = $booking->booking_date;
                                    @endphp
                                    @if($isBlocked)
                                        {{-- Blocked - show contact admin button --}}
                                        <button type="button" class="btn btn-dark action-btn" disabled>
                                            <i class="fa-solid fa-ban me-2"></i>Contact Admin
                                        </button>
                                    @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                        {{-- Waiting for admin approval - disabled button --}}
                                        <button type="button" class="btn btn-secondary action-btn" disabled>
                                            <i class="fa-solid fa-clock me-2"></i>Awaiting Approval
                                        </button>
                                    @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                        {{-- Schedule declined - allow new request if not at limit --}}
                                        @if($attemptCount < $maxAttempts)
                                            <button type="button" class="btn btn-warning action-btn" onclick="openScheduleModal()">
                                                <i class="fa-solid fa-calendar-plus me-2"></i>Request Again ({{ $maxAttempts - $attemptCount }} left)
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-dark action-btn" disabled>
                                                <i class="fa-solid fa-ban me-2"></i>No Attempts Left
                                            </button>
                                        @endif
                                    @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted', 'confirmed']) && $showScheduledDate)
                                        {{-- Schedule approved - can reschedule --}}
                                        <button type="button" class="btn btn-outline-primary action-btn" onclick="openScheduleModal()">
                                            <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                                        </button>
                                    @elseif(!$scheduledDate)
                                        {{-- No schedule yet --}}
                                        <button type="button" class="btn btn-primary action-btn" onclick="openScheduleModal()">
                                            <i class="fa-solid fa-calendar-plus me-2"></i>Schedule
                                        </button>
                                    @else
                                        {{-- Has schedule --}}
                                        <button type="button" class="btn btn-outline-primary action-btn" onclick="openScheduleModal()">
                                            <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                                        </button>
                                    @endif
                                @endif

                            </div>

                        </div>                    
                    </div>
                </div>
            </div>

            <!-- Tour Details (if available) -->
            @if($booking->payment_status === 'paid' && $booking->tour_code && $booking->tour_final_link)
            <div class="col-md-12">
                <div class="info-card tour">
                    <div class="section-header">
                        <div class="card-icon">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Tour Details</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Tour Code</div>
                            <div class="info-value">{{ $booking->tour_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Tour Link</div>
                            <div class="info-detail">
                                <a href="{{ $booking->tour_final_link }}" target="_blank" class="btn btn-primary action-btn">
                                    <i class="fa-solid fa-external-link me-2"></i>View Tour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Booking History -->
            @if($history->count() > 0)
            <div class="col-md-12">
                <div class="info-card">
                    <div class="section-header">
                        <div class="card-icon" style="background:#e3f2fd; color:#1f395a;">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Booking History</h5>
                    </div>
                    <div class="history-timeline">
                        @foreach($history as $item)
                        <div class="history-item">
                            <div class="history-date">{{ $item->created_at->format('M d, Y h:i A') }}</div>
                            <div class="history-status">
                                @if($item->from_status && $item->to_status)
                                    {{ ucfirst(str_replace('_', ' ', $item->from_status)) }} → {{ ucfirst(str_replace('_', ' ', $item->to_status)) }}
                                @elseif($item->to_status)
                                    {{ ucfirst(str_replace('_', ' ', $item->to_status)) }}
                                @endif
                            </div>
                            @if($item->notes)
                                <div class="history-notes">{{ $item->notes }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="col-md-12">
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    @if($booking->payment_status !== 'paid' && $booking->isReadyForPayment())
                        <button type="button" class="btn btn-success action-btn" onclick="initiatePayment({{ $booking->id }})">
                            <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                        </button>
                    @endif
                    
                    @if($booking->payment_status === 'paid')
                        @php
                            $scheduledDate = $booking->booking_date;
                        @endphp
                        @if($isBlocked)
                            {{-- Blocked - show contact admin button --}}
                            <button type="button" class="btn btn-dark action-btn" disabled>
                                <i class="fa-solid fa-ban me-2"></i>Contact Admin
                            </button>
                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                            {{-- Waiting for admin approval - disabled button --}}
                            <button type="button" class="btn btn-secondary action-btn" disabled>
                                <i class="fa-solid fa-clock me-2"></i>Awaiting Approval
                            </button>
                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                            {{-- Schedule declined - allow new request if not at limit --}}
                            @if($attemptCount < $maxAttempts)
                                <button type="button" class="btn btn-warning action-btn" onclick="openScheduleModal()">
                                    <i class="fa-solid fa-calendar-plus me-2"></i>Request Again ({{ $maxAttempts - $attemptCount }} left)
                                </button>
                            @else
                                <button type="button" class="btn btn-dark action-btn" disabled>
                                    <i class="fa-solid fa-ban me-2"></i>No Attempts Left
                                </button>
                            @endif
                        @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted', 'confirmed']) && $showScheduledDate)
                            {{-- Schedule approved - can reschedule --}}
                            <button type="button" class="btn btn-outline-primary action-btn" onclick="openScheduleModal()">
                                <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                            </button>
                        @elseif(!$scheduledDate)
                            {{-- No schedule yet --}}
                            <button type="button" class="btn btn-primary action-btn" onclick="openScheduleModal()">
                                <i class="fa-solid fa-calendar-plus me-2"></i>Schedule
                            </button>
                        @else
                            {{-- Has schedule --}}
                            <button type="button" class="btn btn-outline-primary action-btn" onclick="openScheduleModal()">
                                <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                            </button>
                        @endif
                    @else
                        {{-- Payment not paid - show Edit button --}}
                        <button type="button" class="btn btn-warning action-btn" onclick="openEditModal({{ $booking->id }})">
                            <i class="fa-solid fa-edit me-2"></i>Edit Booking
                        </button>
                    @endif
                    
                    @if($booking->payment_status === 'paid')
                        <button type="button" class="btn btn-outline-success action-btn" onclick="downloadReceipt({{ $booking->id }})">
                            <i class="fa-solid fa-download me-2"></i>Download Receipt
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Date Modal -->
<div class="modal fade pp-modal" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Booking Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Blocked Message (Max Attempts Reached) --}}
                @php
                    $status = $booking->status ?? 'pending';
                    $isBlocked = $isBlocked || ($attemptCount >= $maxAttempts);
                    $adminEmail = \App\Models\Setting::where('name', 'support_email')->value('value') ?? 'support@proppik.in';
                    $adminPhone = \App\Models\Setting::where('name', 'support_phone')->value('value') ?? '+91-XXXXXXXXXX';
                @endphp
                @if($isBlocked)
                    <div class="alert alert-danger py-3 mb-3 pp-alert pp-alert--danger" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-ban me-2 mt-1 pp-alert__icon"></i>
                            <div>
                                <strong class="d-block mb-2">Maximum Attempts Reached</strong>
                                <p class="mb-2 small">
                                    You have lost all your attempts ({{ $attemptCount }}/{{ $maxAttempts }}). You have now lost this booking.
                                </p>
                                <p class="mb-2 small">
                                    <strong>Please create a new booking to start the process again.</strong>
                                </p>
                                <p class="mb-0 small">
                                    If you have any doubts or queries, please contact the administration department:<br>
                                    <i class="fa-solid fa-phone me-1"></i><strong>Phone:</strong> {{ $adminPhone }}<br>
                                    <i class="fa-solid fa-envelope me-1"></i><strong>Email:</strong> {{ $adminEmail }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                {{-- Reschedule Warning Message (Only if not blocked) --}}
                @php
                    $isReschedule = in_array($status, ['schedul_accepted', 'reschedul_accepted']);
                    $oldDate = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') : null;
                @endphp
                @if(!$isBlocked && $isReschedule && $oldDate)
                    <div class="alert alert-warning py-3 mb-3 pp-alert pp-alert--warning" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-exclamation-triangle me-2 mt-1 pp-alert__icon"></i>
                            <div>
                                <strong class="d-block mb-2">Reschedule Warning</strong>
                                <p class="mb-2 small">
                                    <strong>Current Accepted Date:</strong> {{ $oldDate }}
                                </p>
                                <p class="mb-2 small">
                                    If you change this date, it will count as a new attempt. You have already completed <strong>{{ $attemptCount }}</strong> of <strong>{{ $maxAttempts }}</strong> attempts.
                                </p>
                                @if($attemptCount >= $maxAttempts - 1)
                                    <p class="mb-0 small fw-semibold">
                                        <i class="fa-solid fa-ban me-1"></i><strong>Warning:</strong> This is your last attempt! If you reschedule and this attempt reaches the maximum limit ({{ $maxAttempts }}), you will lose this booking and will need to create a new booking to start the process again.
                                    </p>
                                @else
                                    <p class="mb-0 small">
                                        <i class="fa-solid fa-info-circle me-1"></i>You have <strong>{{ $maxAttempts - $attemptCount }}</strong> attempt(s) remaining. If you reach the maximum limit ({{ $maxAttempts }}), you will lose this booking and will need to create a new booking to start the process again.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <form id="scheduleForm">
                    <div class="mb-3">
                        <label class="form-label">Select Date <span class="text-danger">*</span></label>
                        <div class="date-input-group {{ $isBlocked ? 'is-disabled' : '' }}">
                            <input type="text" class="form-control" id="scheduleDate" placeholder="Select a date" required readonly @if($isBlocked) disabled @endif>
                            <i class="fa-regular fa-calendar date-icon {{ $isBlocked ? 'is-disabled' : '' }}" id="scheduleDateIcon"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="scheduleNotes" rows="3" placeholder="Any additional notes..." @if($isBlocked) disabled @endif></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveScheduleBtn" onclick="saveSchedule()" @if($isBlocked) disabled @endif>Save Schedule</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade pp-modal" id="editBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-edit me-2"></i>Edit Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBookingForm">
                    <input type="hidden" id="editBookingId">
                    
                    <!-- Property Details Section -->
                    <div class="card mb-3 pp-card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-building me-2"></i>Property Details</h6>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="editChoiceOwnerType" name="owner_type">
                            <input type="hidden" id="editChoiceResType" name="residential_property_type">
                            <input type="hidden" id="editChoiceResFurnish" name="residential_furnish">
                            <input type="hidden" id="editChoiceResSize" name="residential_size">
                            <input type="hidden" id="editChoiceComType" name="commercial_property_type">
                            <input type="hidden" id="editChoiceComFurnish" name="commercial_furnish">
                            <input type="hidden" id="editChoiceOthLooking" name="other_looking">
                            <input type="hidden" id="editMainPropertyType" name="main_property_type" value="Residential">

                            <div class="mb-3" id="editOwnerTypeContainer">
                                <div class="section-title">Owner Type <span class="text-danger">*</span></div>
                                <div class="d-flex gap-2">
                                    <div class="top-pill" data-group="editOwnerType" data-value="Owner" onclick="editTopPillClick(this)">Owner</div>
                                    <div class="top-pill" data-group="editOwnerType" data-value="Broker" onclick="editTopPillClick(this)">Broker</div>
                                </div>
                                <div id="err-editOwnerType" class="error">Owner Type is required.</div>
                            </div>
                            

                            <!-- PROPERTY TYPE TAB -->
                            <div class="mb-3" id="editPropertyTypeContainer">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    @foreach($types ?? [] as $type)
                                        @php
                                            $map = [
                                                'Residential' => 'res',
                                                'Commercial'  => 'com',
                                                'Other'       => 'oth',
                                            ];
                                            $tabKey = $map[$type->name] ?? strtolower(substr(preg_replace('/\s+/', '', $type->name), 0, 3));
                                        @endphp
                                        <div
                                            class="top-pill"
                                            id="editPill{{ \Illuminate\Support\Str::studly($type->name) }}"
                                            data-value="{{ $type->name }}"
                                            data-type-id="{{ $type->id ?? '' }}"
                                            onclick="editHandlePropertyTabChange('{{ $tabKey }}')"
                                        >
                                            @if(!empty($type->icon))
                                                <i class="fa {{ $type->icon }}"></i>
                                            @endif
                                            {{ $type->name }}
                                        </div>
                                    @endforeach
                                </div>
                                <div id="err-editPropertyType" class="error">Property Type is required.</div>
                            </div>

                            <!-- RESIDENTIAL TAB -->
                            <div id="editTabRes" style="display:none;">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-wrap gap-2 mb-3" id="editResTypesContainer">
                                    @php
                                        $residentialType = ($types ?? collect())->firstWhere('name', 'Residential');
                                        $residentialSubTypes = $residentialType ? $residentialType->subTypes : [];
                                    @endphp
                                    @forelse($residentialSubTypes as $subType)
                                        <div class="top-pill m-1" data-group="editResType" data-value="{{ $subType->name }}" onclick="editSelectCard(this)">
                                            @if($subType->icon)
                                                <i class="fa {{ $subType->icon }}"></i>
                                            @endif
                                            {{ $subType->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted">No residential types available</div>
                                    @endforelse
                                </div>
                                <div id="err-editResType" class="error">Property Sub Type is required.</div>
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3 edit-res-furnish-container">
                                    <div class="chip" data-group="editResFurnish" data-value="Fully Furnished" onclick="editSelectChip(this)"><i class="bi bi-sofa"></i> Fully Furnished</div>
                                    <div class="chip" data-group="editResFurnish" data-value="Semi Furnished" onclick="editSelectChip(this)"><i class="bi bi-lamp"></i> Semi Furnished</div>
                                    <div class="chip" data-group="editResFurnish" data-value="Unfurnished" onclick="editSelectChip(this)"><i class="bi bi-door-closed"></i> Unfurnished</div>
                                </div>
                                <div id="err-editResFurnish" class="error">Furnish Type is required.</div>
                                <div class="section-title">Size (BHK / RK) <span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3 edit-res-size-container">
                                    @forelse($bhk ?? [] as $bhkItem)
                                    <div class="chip" data-group="editResSize" data-value="{{ $bhkItem->id }}" onclick="editSelectChip(this)">{{ $bhkItem->name }}</div>
                                    @empty
                                        <div class="chip" data-group="editResSize" data-value="null" onclick="editSelectChip(this)">Not Found</div>
                                    @endforelse
                                </div>
                                <div id="err-editResSize" class="error">Size (BHK / RK) is required.</div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="editResArea" name="residential_area" class="form-control" type="number" min="1" placeholder="e.g., 1200" oninput="updateEditPrice()" />
                                    <div id="err-editResArea" class="error">Super Built-up Area is required and must be greater than 0.</div>
                                </div>
                            </div>

                            <!-- COMMERCIAL TAB -->
                            <div id="editTabCom" style="display:none;">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="editComTypesContainer">
                                    @php
                                        $commercialType = ($types ?? collect())->firstWhere('name', 'Commercial');
                                        $commercialSubTypes = $commercialType ? $commercialType->subTypes : [];
                                    @endphp
                                    @forelse($commercialSubTypes as $subType)
                                        <div class="top-pill" data-group="editComType" data-value="{{ $subType->name }}" onclick="editSelectCard(this)">
                                            @if($subType->icon)
                                                <i class="fa {{ $subType->icon }}"></i>
                                            @endif
                                            {{ $subType->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted">No commercial types available</div>
                                    @endforelse
                                </div>
                                <div id="err-editComType" class="error">Property Sub Type is required.</div>
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3 edit-com-furnish-container">
                                    <div class="chip" data-group="editComFurnish" data-value="Fully Furnished" onclick="editSelectChip(this)">Fully Furnished</div>
                                    <div class="chip" data-group="editComFurnish" data-value="Semi Furnished" onclick="editSelectChip(this)">Semi Furnished</div>
                                    <div class="chip" data-group="editComFurnish" data-value="Unfurnished" onclick="editSelectChip(this)">Unfurnished</div>
                                </div>
                                <div id="err-editComFurnish" class="error">Furnish Type is required.</div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="editComArea" name="commercial_area" class="form-control" type="number" min="1" placeholder="e.g., 2000" oninput="updateEditPrice()" />
                                    <div id="err-editComArea" class="error">Super Built-up Area is required and must be greater than 0.</div>
                                </div>
                            </div>

                            <!-- OTHER TAB -->
                            <div id="editTabOth" style="display:none;">
                                <div class="section-title">Looking For<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="editOthTypesContainer">
                                    @php
                                        $otherType = ($types ?? collect())->firstWhere('name', 'Other');
                                        $otherSubTypes = $otherType ? $otherType->subTypes : [];
                                    @endphp
                                    @forelse($otherSubTypes as $subType)
                                        <div class="top-pill" data-group="editOthLooking" data-value="{{ $subType->name }}" onclick="editTopPillClick(this)">
                                            @if($subType->icon)
                                                <i class="fa {{ $subType->icon }}"></i>
                                            @endif
                                            {{ $subType->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted">No other types available</div>
                                    @endforelse
                                </div>
                                <div id="err-editOthLooking" class="error">Please select an option or enter Other option.</div>
                                <div class="mb-3">
                                    <div class="section-title">Other Option Details</div>
                                    <textarea id="editOthDesc" name="other_option_details" class="form-control" rows="2" placeholder="Enter other option details"></textarea>
                                    <div id="err-editOthDesc" class="error">Other option is required if none of the options are selected.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="editOthArea" name="other_area" class="form-control" type="number" min="1" placeholder="e.g., 1500" oninput="updateEditPrice()" />
                                    <div id="err-editOthArea" class="error">Super Built-up Area is required and must be greater than 0.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="section-title">Firm Name</div>
                                    <input id="editFirmName" name="firm_name" class="form-control" type="text" placeholder="Enter firm name (optional)" />
                                </div>
                                <div class="col-md-6">
                                    <div class="section-title">GST No</div>
                                    <input id="editGstNo" name="gst_no" class="form-control" type="text" placeholder="Enter GST number (optional)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Details Section -->
                    <div class="card mb-3 pp-card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-location-dot me-2"></i>Address Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">House/Office No. <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editHouseNo" required>
                                    <div id="err-editHouseNo" class="error">House / Office No. is required.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Building/Society Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editBuilding" required>
                                    <div id="err-editBuilding" class="error">Society / Building Name is required.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editPincode" maxlength="6" required>
                                    <div id="err-editPincode" class="error">Pincode is required and must be a valid 6-digit number.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editCity" required>
                                        <option value="Ahmedabad" selected>Ahmedabad</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="editState" value="Gujarat" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="editFullAddress" rows="3" required></textarea>
                                    <div id="err-editFullAddress" class="error">Full address is required.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Required Notice (if payment pending) -->
                    <div class="d-none">
                        <div class="card mb-3 pp-card" id="editPaymentRequiredCard" style="display:none;">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0"><i class="fa-solid fa-exclamation-triangle me-2"></i>Payment Required</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-3" role="alert">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Make payment first, then schedule your deals.</strong> Please complete the payment to proceed with scheduling.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editPaymentPrice" readonly>
                                    <small class="text-muted">Calculated based on area (sq. ft.)</small>
                                </div>
                                <button type="button" class="btn btn-success w-100" id="editMakePaymentBtn" onclick="initiatePaymentFromEdit()" disabled>
                                    <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                                </button>
                                <small class="text-muted d-block text-center mt-2">Secure payment via Cashfree</small>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Details Section (only if payment is done) -->
                    <div class="card mb-3 pp-card" id="editScheduleDetailsCard">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-calendar me-2"></i>Schedule Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Select Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="editScheduledDate" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="editScheduleNotes" rows="3" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBookingEdit()">
                    <i class="fa-solid fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade pp-modal" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-credit-card me-2"></i>Payment Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentModalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Preparing payment gateway...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade pp-modal" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="pp-success-icon">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h5 class="mb-3" id="successTitle">Success!</h5>
                <p class="muted-small mb-3" id="successMessage">Operation completed successfully.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Download Modal (with iframe) -->
<div class="modal fade pp-modal" id="receiptDownloadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="padding:10px !important;">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-receipt me-2"></i>Download Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeReceiptModal()"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <iframe id="receiptIframe" src="" style="width: 100%; height: 100%; border: none; min-height: 80vh;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap 5 bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Schedule modal handlers
            const scheduleModalEl = document.getElementById('scheduleModal');
            const scheduleInput = document.getElementById('scheduleDate');
            const scheduleIcon = document.getElementById('scheduleDateIcon');
            let schedulePicker = null;
            let iconBound = false;

            // Fetch holidays and available days from API
            async function fetchHolidaysAndAvailableDays() {
                try {
                    const response = await fetch('{{ url("/api/holidays") }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to fetch holidays');
                    }
                    
                    const data = await response.json();
                    const holidays = (data.holidays || []).map(h => h.date);
                    const availableDays = data.day_limit && data.day_limit.value ? parseInt(data.day_limit.value, 10) : 30;
                    const disabledDates = data.disabled_dates || []; // Dates that have reached per day booking limit
                    
                    return { holidays, availableDays, disabledDates };
                } catch (error) {
                    console.error('Error fetching holidays:', error);
                    // Return defaults on error
                    return { holidays: [], availableDays: 30, disabledDates: [] };
                }
            }

            // Initialize Flatpickr with holidays and date restrictions
            async function initScheduleDatePicker(existingDate = null) {
                // Destroy existing instance if any
                if (schedulePicker) {
                    schedulePicker.destroy();
                    schedulePicker = null;
                }
                
                if (!scheduleInput) return;
                
                // Fetch holidays and available days, then initialize
                const { holidays, availableDays, disabledDates } = await fetchHolidaysAndAvailableDays();
                
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Calculate max date (today + available days)
                const maxDate = new Date(today);
                maxDate.setDate(today.getDate() + availableDays);
                
                // Combine holidays and disabled dates (dates at booking limit)
                const allDisabledDates = [...holidays, ...disabledDates];
                
                // Initialize Flatpickr
                schedulePicker = flatpickr(scheduleInput, {
                    dateFormat: 'Y-m-d',
                    minDate: today,
                    maxDate: maxDate,
                    disable: allDisabledDates,
                    defaultDate: existingDate || null,
                    allowInput: false,
                    clickOpens: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        // Validate that selected date is not a holiday
                        if (holidays.includes(dateStr)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Holiday Selected',
                                    text: 'The selected date is a holiday. Please choose another date.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                            instance.clear();
                        }
                        // Validate that selected date is not at booking limit
                        else if (disabledDates.includes(dateStr)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Date Fully Booked',
                                    text: 'This date has reached the maximum number of bookings. Please choose another date.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                            instance.clear();
                        }
                    }
                });

                // Bind icon click to open picker (once)
                if (scheduleIcon && !iconBound) {
                    scheduleIcon.addEventListener('click', () => {
                        if (!schedulePicker) {
                            initScheduleDatePicker(scheduleInput.value || null);
                        } else {
                            schedulePicker.open();
                        }
                        scheduleInput?.focus();
                    });
                    iconBound = true;
                }
            }

            window.openScheduleModal = function() {
                const modal = new bootstrap.Modal(scheduleModalEl);
                // Preload existing date/notes
                const existingDate = "{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '' }}";
                scheduleInput.value = existingDate;
                document.getElementById('scheduleNotes').value = "{{ $booking->booking_notes ?? '' }}";
                
                // Store original date and notes for comparison
                scheduleModalEl.dataset.originalDate = existingDate;
                scheduleModalEl.dataset.originalStatus = "{{ $booking->status ?? 'pending' }}";
                scheduleModalEl.dataset.originalNotes = "{{ $booking->booking_notes ?? '' }}";
                
                // Check if blocked (max attempts reached or status is reschedul_blocked)
                const isBlocked = {{ $isBlocked ? 'true' : 'false' }} || {{ $attemptCount >= $maxAttempts ? 'true' : 'false' }};
                
                // Get save button
                const saveScheduleBtn = document.getElementById('saveScheduleBtn');
                
                // If blocked, disable inputs and button
                if (isBlocked) {
                    if (scheduleInput) {
                        scheduleInput.disabled = true;
                        scheduleInput.style.cursor = 'not-allowed';
                        scheduleInput.style.opacity = '0.6';
                    }
                    if (scheduleIcon) {
                        scheduleIcon.style.cursor = 'not-allowed';
                        scheduleIcon.style.opacity = '0.5';
                    }
                    const scheduleNotesInput = document.getElementById('scheduleNotes');
                    if (scheduleNotesInput) {
                        scheduleNotesInput.disabled = true;
                        scheduleNotesInput.style.cursor = 'not-allowed';
                        scheduleNotesInput.style.opacity = '0.6';
                    }
                    if (saveScheduleBtn) {
                        saveScheduleBtn.disabled = true;
                        saveScheduleBtn.style.cursor = 'not-allowed';
                        saveScheduleBtn.style.opacity = '0.6';
                    }
                } else {
                    // Not blocked - enable inputs and button
                    if (scheduleInput) {
                        scheduleInput.disabled = false;
                        scheduleInput.style.cursor = '';
                        scheduleInput.style.opacity = '';
                    }
                    if (scheduleIcon) {
                        scheduleIcon.style.cursor = 'pointer';
                        scheduleIcon.style.opacity = '';
                    }
                    const scheduleNotesInput = document.getElementById('scheduleNotes');
                    if (scheduleNotesInput) {
                        scheduleNotesInput.disabled = false;
                        scheduleNotesInput.style.cursor = '';
                        scheduleNotesInput.style.opacity = '';
                    }
                    if (saveScheduleBtn) {
                        saveScheduleBtn.disabled = false;
                        saveScheduleBtn.style.cursor = '';
                        saveScheduleBtn.style.opacity = '';
                    }
                    
                    // Initialize Flatpickr after modal is shown (only if not blocked)
                    setTimeout(() => {
                        initScheduleDatePicker(existingDate || null);
                    }, 100);
                }
                
                modal.show();
            };

            // Clean up Flatpickr when modal is closed
            if (scheduleModalEl) {
                scheduleModalEl.addEventListener('hidden.bs.modal', function () {
                    if (schedulePicker) {
                        schedulePicker.destroy();
                        schedulePicker = null;
                    }
                    // Clear the input
                    if (scheduleInput) {
                        scheduleInput.value = '';
                    }
                });
            }
            
            // Check if we should auto-open schedule modal from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const openSchedule = urlParams.get('open_schedule');
            if (openSchedule === '1') {
                // Remove the parameter from URL without reload
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
                // Open schedule modal after a short delay to ensure page is loaded
                setTimeout(() => {
                    if (typeof window.openScheduleModal === 'function') {
                        window.openScheduleModal();
                    }
                }, 500);
            }

            window.saveSchedule = async function() {
                // Check if blocked
                const isBlocked = {{ $isBlocked ? 'true' : 'false' }} || {{ $attemptCount >= $maxAttempts ? 'true' : 'false' }};
                if (isBlocked) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Action Not Allowed',
                            text: 'You have reached the maximum number of attempts. Please create a new booking.'
                        });
                    } else {
                        alert('You have reached the maximum number of attempts. Please create a new booking.');
                    }
                    return;
                }
                
                const dateVal = scheduleInput.value;
                const notesVal = document.getElementById('scheduleNotes').value.trim();
                if (!dateVal) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'warning',
                            title: 'Validation Error',
                            text: 'Please select a date'
                        });
                    } else {
                        alert('Please select a schedule date.');
                    }
                    return;
                }
                
                // Check if this is a reschedule (status is schedul_accepted or reschedul_accepted)
                const originalStatus = scheduleModalEl.dataset.originalStatus || '';
                const originalDate = scheduleModalEl.dataset.originalDate || '';
                const isReschedule = ['schedul_accepted', 'reschedul_accepted'].includes(originalStatus);
                const dateChanged = originalDate && dateVal !== originalDate;
                
                // If reschedule and date changed, show confirmation
                if (isReschedule && dateChanged) {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Confirm Date Change',
                        html: `
                            <p>You have changed the date from <strong>${new Date(originalDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</strong> to <strong>${new Date(dateVal).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>.</p>
                            <p>This will count as a new attempt and may affect your booking status.</p>
                            <p><strong>Are you sure you want to proceed?</strong></p>
                        `,
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Change Date',
                        cancelButtonText: 'Cancel'
                    });
                    
                    if (!result.isConfirmed) {
                        return; // User cancelled
                    }
                }
                
                // Determine if we should only update notes (date not changed in reschedule)
                const updateNotesOnly = isReschedule && !dateChanged;
                
                try {
                    const resp = await fetch('{{ route("frontend.setup.update-booking") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            booking_id: {{ $booking->id }},
                            scheduled_date: dateVal,
                            notes: notesVal || null,
                            booking_notes: notesVal || null,
                            update_notes_only: updateNotesOnly
                        })
                    });
                    const data = await resp.json();
                    if (resp.ok && data.success) {
                        const modal = bootstrap.Modal.getInstance(scheduleModalEl);
                        if (modal) modal.hide();
                        
                        if (typeof Swal !== 'undefined') {
                            const message = updateNotesOnly ? 'Notes updated successfully.' : data.message || 'Booking schedule has been updated successfully.';
                            await Swal.fire({
                                icon: 'success',
                                title: updateNotesOnly ? 'Notes Updated!' : 'Schedule Updated!',
                                text: message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        setTimeout(() => window.location.reload(), 300);
                    } else {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to update schedule. Please try again.'
                            });
                        } else {
                            alert(data.message || 'Failed to update schedule. Please try again.');
                        }
                    }
                } catch (e) {
                    console.error(e);
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Network error. Please try again.'
                        });
                    } else {
                        alert('Network error. Please try again.');
                    }
                }
            };
        });

        // ========== EDIT BOOKING MODAL FUNCTIONS ==========
        
        // SweetAlert Helper Function
        async function showSweetAlert(icon, title, message, html = false) {
            if (typeof Swal !== 'undefined') {
                const config = {
                    icon: icon,
                    title: title,
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'sweetalert-popup',
                        title: 'sweetalert-title',
                        content: 'sweetalert-content',
                        confirmButton: 'sweetalert-confirm-btn'
                    },
                    buttonsStyling: true,
                    allowOutsideClick: true,
                    allowEscapeKey: true
                };
                
                if (icon === 'error') {
                    config.confirmButtonColor = '#dc3545';
                    config.iconColor = '#dc3545';
                    config.width = '500px';
                    if (html) {
                        const formattedMessage = message.split('<br>').map(err => {
                            return `<div style="display: flex; align-items: flex-start;">
                                <span style="color: #dc3545; margin-right: 8px; font-weight: 600;">•</span>
                                <span style="flex: 1; color: #333;">${err.replace(/^•\s*/, '')}</span>
                            </div>`;
                        }).join('');
                        config.html = `<div style="text-align: left; padding: 10px 0; max-height: 400px; overflow-y: auto;">${formattedMessage}</div>`;
                    } else {
                        config.html = `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">${message}</div>`;
                    }
                } else if (html) {
                    config.html = `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">${message}</div>`;
                } else {
                    config.text = message;
                }
                
                return Swal.fire(config);
            } else {
                const cleanMessage = message.replace(/<br>/g, '\n').replace(/• /g, '- ');
                alert(cleanMessage);
                return Promise.resolve();
            }
        }

        // Error handling functions
        function editShowFieldError(fieldId, errorId, message) {
            const field = document.getElementById(fieldId);
            const errorEl = document.getElementById(errorId);
            if (field) {
                field.classList.add('is-invalid');
            }
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
                errorEl.classList.add('show');
            }
        }

        function editHideFieldError(fieldId, errorId) {
            const field = document.getElementById(fieldId);
            const errorEl = document.getElementById(errorId);
            if (field) {
                field.classList.remove('is-invalid');
            }
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.classList.remove('show');
            }
        }

        function editMarkFieldValid(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }

        function editShowPillContainerError(containerId, errorId, message) {
            const container = document.getElementById(containerId);
            const errorEl = document.getElementById(errorId);
            if (container) {
                container.classList.add('has-error');
            }
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
                errorEl.classList.add('show');
            }
        }

        function editHidePillContainerError(containerId, errorId) {
            const container = document.getElementById(containerId);
            const errorEl = document.getElementById(errorId);
            if (container) {
                container.classList.remove('has-error');
            }
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.classList.remove('show');
            }
        }

        function editClearAllFieldErrors() {
            document.querySelectorAll('#editBookingForm .error').forEach(el => {
                el.style.display = 'none';
                el.classList.remove('show');
            });
            document.querySelectorAll('#editBookingForm .form-control, #editBookingForm .form-select, #editBookingForm textarea').forEach(el => {
                el.classList.remove('is-invalid', 'is-valid');
            });
            document.querySelectorAll('#editBookingForm .has-error').forEach(el => {
                el.classList.remove('has-error');
            });
        }

        // Edit Modal Selection Functions
        function editTopPillClick(dom) {
            const group = dom.dataset.group;
            document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
            dom.classList.add('active');
            if (group === 'editOwnerType') {
                document.getElementById('editChoiceOwnerType').value = dom.dataset.value;
                editHidePillContainerError('editOwnerTypeContainer', 'err-editOwnerType');
                editUpdatePaymentButtonState();
            } else if (group === 'editOthLooking') {
                document.getElementById('editChoiceOthLooking').value = dom.dataset.value;
                editHidePillContainerError('editOthTypesContainer', 'err-editOthLooking');
                editHideFieldError('editOthDesc', 'err-editOthDesc');
                editUpdatePaymentButtonState();
            }
        }

        function editSelectCard(dom) {
            const group = dom.dataset.group;
            document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
            dom.classList.add('active');
            const v = dom.dataset.value;
            if (group === 'editResType') {
                document.getElementById('editChoiceResType').value = v;
                editHidePillContainerError('editResTypesContainer', 'err-editResType');
                editUpdatePaymentButtonState();
            }
            if (group === 'editComType') {
                document.getElementById('editChoiceComType').value = v;
                editHidePillContainerError('editComTypesContainer', 'err-editComType');
                editUpdatePaymentButtonState();
            }
        }

        function editSelectChip(dom) {
            const group = dom.dataset.group;
            document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
            dom.classList.add('active');
            const v = dom.dataset.value;
            if (group === 'editResFurnish') {
                document.getElementById('editChoiceResFurnish').value = v;
                editHidePillContainerError('edit-res-furnish-container', 'err-editResFurnish');
                editUpdatePaymentButtonState();
            }
            if (group === 'editResSize') {
                document.getElementById('editChoiceResSize').value = v;
                editHidePillContainerError('edit-res-size-container', 'err-editResSize');
                editUpdatePaymentButtonState();
            }
            if (group === 'editComFurnish') {
                document.getElementById('editChoiceComFurnish').value = v;
                editHidePillContainerError('edit-com-furnish-container', 'err-editComFurnish');
                editUpdatePaymentButtonState();
            }
        }

        // Track active property tab in edit modal
        let editActivePropertyTab = null;

        // Check if any property data has been filled in edit modal
        function editHasPropertyDataFilled() {
            if (!editActivePropertyTab) return false;
            
            if (editActivePropertyTab === 'res') {
                const resType = document.getElementById('editChoiceResType')?.value;
                const resFurnish = document.getElementById('editChoiceResFurnish')?.value;
                const resSize = document.getElementById('editChoiceResSize')?.value;
                const resArea = document.getElementById('editResArea')?.value?.trim();
                return !!(resType || resFurnish || resSize || resArea);
            } else if (editActivePropertyTab === 'com') {
                const comType = document.getElementById('editChoiceComType')?.value;
                const comFurnish = document.getElementById('editChoiceComFurnish')?.value;
                const comArea = document.getElementById('editComArea')?.value?.trim();
                return !!(comType || comFurnish || comArea);
            } else if (editActivePropertyTab === 'oth') {
                const othLooking = document.getElementById('editChoiceOthLooking')?.value;
                const othDesc = document.getElementById('editOthDesc')?.value?.trim();
                const othArea = document.getElementById('editOthArea')?.value?.trim();
                return !!(othLooking || othDesc || othArea);
            }
            
            return false;
        }

        // Check if address data has been filled in edit modal
        function editHasAddressDataFilled() {
            const h = document.getElementById('editHouseNo')?.value?.trim();
            const b = document.getElementById('editBuilding')?.value?.trim();
            const p = document.getElementById('editPincode')?.value?.trim();
            const f = document.getElementById('editFullAddress')?.value?.trim();
            return !!(h || b || p || f);
        }

        // Check if all required property data is filled
        function editIsPropertyStepCompleted() {
            if (!editActivePropertyTab) return false;

            const ownerType = document.getElementById('editChoiceOwnerType')?.value;
            if (!ownerType) return false;

            if (editActivePropertyTab === 'res') {
                const resType = document.getElementById('editChoiceResType')?.value;
                const resFurnish = document.getElementById('editChoiceResFurnish')?.value;
                const resSize = document.getElementById('editChoiceResSize')?.value;
                const resArea = document.getElementById('editResArea')?.value?.trim();
                return !!(resType && resFurnish && resSize && resArea && Number(resArea) > 0);
            } else if (editActivePropertyTab === 'com') {
                const comType = document.getElementById('editChoiceComType')?.value;
                const comFurnish = document.getElementById('editChoiceComFurnish')?.value;
                const comArea = document.getElementById('editComArea')?.value?.trim();
                return !!(comType && comFurnish && comArea && Number(comArea) > 0);
            } else if (editActivePropertyTab === 'oth') {
                const oLooking = document.getElementById('editChoiceOthLooking')?.value;
                const oDesc = document.getElementById('editOthDesc')?.value?.trim();
                const oArea = document.getElementById('editOthArea')?.value?.trim();
                const hasSelection = Boolean(oLooking);
                const hasOther = Boolean(oDesc);
                return !!(hasSelection || hasOther) && !!(oArea && Number(oArea) > 0);
            }

            return false;
        }

        // Check if all required address data is filled
        function editIsAddressStepCompleted() {
            const h = document.getElementById('editHouseNo')?.value?.trim();
            const b = document.getElementById('editBuilding')?.value?.trim();
            const p = document.getElementById('editPincode')?.value?.trim();
            const f = document.getElementById('editFullAddress')?.value?.trim();
            
            return !!(h && b && p && /^[0-9]{6}$/.test(p) && f);
        }

        // Check if booking is ready for payment
        function editIsReadyForPayment() {
            return editIsPropertyStepCompleted() && editIsAddressStepCompleted();
        }

        // Update payment button state based on validation
        function editUpdatePaymentButtonState() {
            const paymentBtn = document.getElementById('editMakePaymentBtn');
            if (!paymentBtn) return;

            const isReady = editIsReadyForPayment();
            
            if (isReady) {
                paymentBtn.disabled = false;
                paymentBtn.classList.remove('btn-secondary');
                paymentBtn.classList.add('btn-success');
            } else {
                paymentBtn.disabled = true;
                paymentBtn.classList.remove('btn-success');
                paymentBtn.classList.add('btn-secondary');
            }
        }

        async function editHandlePropertyTabChange(key) {
            if (!key) {
                document.getElementById('editTabRes').style.display = 'none';
                document.getElementById('editTabCom').style.display = 'none';
                document.getElementById('editTabOth').style.display = 'none';
                ['editPillResidential', 'editPillCommercial', 'editPillOther'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.remove('active');
                });
                editActivePropertyTab = null;
                editHidePillContainerError('editPropertyTypeContainer', 'err-editPropertyType');
                return;
            }

            if (editActivePropertyTab && editActivePropertyTab !== key && (editHasPropertyDataFilled() || editHasAddressDataFilled())) {
                const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
                const currentType = typeMap[editActivePropertyTab] || 'Current';
                const newType = typeMap[key] || 'New';
                
                let messageParts = [];
                messageParts.push(`You are changing Property Type from <strong>${currentType}</strong> to <strong>${newType}</strong>.<br><br>`);
                
                if (editHasPropertyDataFilled()) {
                    messageParts.push(`This will clear the following property details:<br>
                        • Property Sub Type<br>
                        • Furnish Type<br>
                        • Size (BHK/RK)<br>
                        • Super Built-up Area<br>`);
                }
                
                if (editHasAddressDataFilled()) {
                    if (editHasPropertyDataFilled()) {
                        messageParts.push(`<br>This will also clear the following address details:<br>
                            • House / Office No.<br>
                            • Society / Building Name<br>
                            • Pincode<br>
                            • Full Address<br>`);
                    } else {
                        messageParts.push(`This will clear the following address details:<br>
                            • House / Office No.<br>
                            • Society / Building Name<br>
                            • Pincode<br>
                            • Full Address<br>`);
                    }
                }
                
                messageParts.push(`<br><strong>Note:</strong> Your billing details (Company Name, GST No) will be preserved.`);
                
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Change Property Type?',
                    html: `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">
                        ${messageParts.join('')}
                    </div>`,
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Change It',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'sweetalert-popup',
                        title: 'sweetalert-title',
                        content: 'sweetalert-content',
                        confirmButton: 'sweetalert-confirm-btn'
                    },
                    buttonsStyling: true,
                    allowOutsideClick: true,
                    allowEscapeKey: true
                });
                
                if (!result.isConfirmed) {
                    return;
                }
            }

            // Clear related fields when property type changes
            document.querySelectorAll('[data-group="editResType"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[data-group="editComType"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResType').value = '';
            document.getElementById('editChoiceComType').value = '';
            
            document.querySelectorAll('[data-group="editResFurnish"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[data-group="editComFurnish"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResFurnish').value = '';
            document.getElementById('editChoiceComFurnish').value = '';
            
            document.querySelectorAll('[data-group="editResSize"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResSize').value = '';
            
            document.querySelectorAll('[data-group="editOthLooking"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceOthLooking').value = '';
            
            document.getElementById('editOthDesc').value = '';
            
            document.getElementById('editResArea').value = '';
            document.getElementById('editComArea').value = '';
            document.getElementById('editOthArea').value = '';
            
            document.getElementById('editHouseNo').value = '';
            document.getElementById('editBuilding').value = '';
            document.getElementById('editPincode').value = '';
            document.getElementById('editFullAddress').value = '';
            
            document.getElementById('editTabRes').style.display = 'none';
            document.getElementById('editTabCom').style.display = 'none';
            document.getElementById('editTabOth').style.display = 'none';
            
            ['editPillResidential', 'editPillCommercial', 'editPillOther'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('active');
            });
            
            if (key === 'res') {
                document.getElementById('editTabRes').style.display = 'block';
                const pill = document.getElementById('editPillResidential');
                if (pill) pill.classList.add('active');
            } else if (key === 'com') {
                document.getElementById('editTabCom').style.display = 'block';
                const pill = document.getElementById('editPillCommercial');
                if (pill) pill.classList.add('active');
            } else if (key === 'oth') {
                document.getElementById('editTabOth').style.display = 'block';
                const pill = document.getElementById('editPillOther');
                if (pill) pill.classList.add('active');
            }
            
            editActivePropertyTab = key;
            
            const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
            document.getElementById('editMainPropertyType').value = typeMap[key] || 'Residential';
            
            editHidePillContainerError('editPropertyTypeContainer', 'err-editPropertyType');
            
            updateEditPrice();
            
            editUpdatePaymentButtonState();
        }
        
        // Price settings from server
        let dashboardPriceSettings = {
            basePrice: {{ $priceSettings['base_price'] ?? 599 }},
            baseArea: {{ $priceSettings['base_area'] ?? 1500 }},
            extraArea: {{ $priceSettings['extra_area'] ?? 500 }},
            extraAreaPrice: {{ $priceSettings['extra_area_price'] ?? 200 }}
        };
        
        // Calculate price based on area
        function calculatePriceFromArea(area) {
            const areaVal = parseInt(area) || 0;
            if (areaVal <= 0) return 0;
            
            const baseArea = dashboardPriceSettings.baseArea || 1500;
            const basePrice = dashboardPriceSettings.basePrice || 599;
            const extraArea = dashboardPriceSettings.extraArea || 500;
            const extraAreaPrice = dashboardPriceSettings.extraAreaPrice || 200;
            
            let price = basePrice;
            if (areaVal > baseArea) {
                const extra = areaVal - baseArea;
                const blocks = Math.ceil(extra / extraArea);
                price += blocks * extraAreaPrice;
            }
            return price;
        }
        
        // Update price field when area changes
        function updateEditPrice() {
            const tabResVisible = document.getElementById('editTabRes').style.display !== 'none';
            const tabComVisible = document.getElementById('editTabCom').style.display !== 'none';
            const tabOthVisible = document.getElementById('editTabOth').style.display !== 'none';
            
            let area = 0;
            if (tabResVisible) {
                area = parseFloat(document.getElementById('editResArea').value) || 0;
            } else if (tabComVisible) {
                area = parseFloat(document.getElementById('editComArea').value) || 0;
            } else if (tabOthVisible) {
                area = parseFloat(document.getElementById('editOthArea').value) || 0;
            }
            
            const calculatedPrice = calculatePriceFromArea(area);
            
            const paymentPriceField = document.getElementById('editPaymentPrice');
            if (paymentPriceField) {
                paymentPriceField.value = calculatedPrice;
            }
            
            editUpdatePaymentButtonState();
        }
        
        // Lock/unlock property and address fields based on payment status
        function lockEditFieldsForPayment(paymentStatus) {
            const isPaid = paymentStatus === 'paid';
            
            const propertyCard = document.querySelector('#editBookingForm .card:first-of-type .card-body');
            if (propertyCard) {
                propertyCard.querySelectorAll('.top-pill, .chip').forEach(el => {
                    if (isPaid) {
                        el.style.pointerEvents = 'none';
                        el.style.opacity = '0.6';
                        el.style.cursor = 'not-allowed';
                    } else {
                        el.style.pointerEvents = '';
                        el.style.opacity = '';
                        el.style.cursor = '';
                    }
                });
                
                propertyCard.querySelectorAll('input, textarea, select').forEach(el => {
                    el.disabled = isPaid;
                    if (isPaid) {
                        el.style.backgroundColor = '#f5f5f5';
                        el.style.cursor = 'not-allowed';
                    } else {
                        el.style.backgroundColor = '';
                        el.style.cursor = '';
                    }
                });
            }
            
            const addressCard = document.querySelector('#editBookingForm .card:nth-of-type(2) .card-body');
            if (addressCard) {
                addressCard.querySelectorAll('input, textarea, select').forEach(el => {
                    el.disabled = isPaid;
                    if (isPaid) {
                        el.style.backgroundColor = '#f5f5f5';
                        el.style.cursor = 'not-allowed';
                    } else {
                        el.style.backgroundColor = '';
                        el.style.cursor = '';
                    }
                });
            }
            
            if (isPaid) {
                let propertyNotice = document.getElementById('editPropertyLockNotice');
                if (!propertyNotice && propertyCard) {
                    propertyNotice = document.createElement('div');
                    propertyNotice.id = 'editPropertyLockNotice';
                    propertyNotice.className = 'alert alert-warning mb-3';
                    propertyNotice.innerHTML = '<i class="fa-solid fa-lock me-2"></i><strong>Locked:</strong> Property details cannot be changed after payment is completed.';
                    propertyCard.insertBefore(propertyNotice, propertyCard.firstChild);
                }
                
                let addressNotice = document.getElementById('editAddressLockNotice');
                if (!addressNotice && addressCard) {
                    addressNotice = document.createElement('div');
                    addressNotice.id = 'editAddressLockNotice';
                    addressNotice.className = 'alert alert-warning mb-3';
                    addressNotice.innerHTML = '<i class="fa-solid fa-lock me-2"></i><strong>Locked:</strong> Address details cannot be changed after payment is completed.';
                    addressCard.insertBefore(addressNotice, addressCard.firstChild);
                }
            } else {
                const propertyNotice = document.getElementById('editPropertyLockNotice');
                if (propertyNotice) propertyNotice.remove();
                
                const addressNotice = document.getElementById('editAddressLockNotice');
                if (addressNotice) addressNotice.remove();
            }
        }

        function editSetGroupValue(group, value) {
            if (!group) return;
            document.querySelectorAll(`[data-group="${group}"]`).forEach(node => {
                if (value && node.dataset.value == value) node.classList.add('active');
                else node.classList.remove('active');
            });
        }

        // Open Edit Modal
        async function openEditModal(bookingId) {
            const modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
            
            document.getElementById('editBookingId').value = bookingId;
            
            document.getElementById('editBookingForm').reset();
            
            document.querySelectorAll('[data-group^="edit"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[id^="editChoice"], [id^="editMain"], [id^="editRes"], [id^="editCom"], [id^="editOth"]').forEach(el => {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') el.value = '';
            });
            
            editClearAllFieldErrors();
            
            editHandlePropertyTabChange(null);
            editActivePropertyTab = null;
            
            const paymentBtn = document.getElementById('editMakePaymentBtn');
            if (paymentBtn) {
                paymentBtn.disabled = true;
                paymentBtn.classList.remove('btn-success');
                paymentBtn.classList.add('btn-secondary');
            }
            
            modal.show();
            
            try {
                const response = await fetch('{{ route("frontend.setup.summary") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ booking_id: bookingId })
                });
                
                if (!response.ok) {
                    let errorText = '';
                    try {
                        errorText = await response.text();
                        console.error('API Error Response:', errorText);
                    } catch (e) {
                        console.error('Error reading response:', e);
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                let result;
                try {
                    result = await response.json();
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    throw new Error('Invalid response format from server');
                }
                
                if (!result || !result.success) {
                    throw new Error(result?.message || 'Failed to load booking details');
                }
                
                if (result.booking) {
                    const b = result.booking;
                    
                    if (b.owner_type) {
                        editSetGroupValue('editOwnerType', b.owner_type);
                        document.getElementById('editChoiceOwnerType').value = b.owner_type;
                    }
                    
                    const propertyType = (b.property_type || 'Residential').toLowerCase();
                    let tabKey = 'res';
                    if (propertyType === 'commercial') tabKey = 'com';
                    else if (propertyType === 'other') tabKey = 'oth';
                    
                    editHandlePropertyTabChange(tabKey);
                    editActivePropertyTab = tabKey;
                    
                    if (b.property_sub_type) {
                        if (tabKey === 'res') {
                            editSetGroupValue('editResType', b.property_sub_type);
                            document.getElementById('editChoiceResType').value = b.property_sub_type;
                        } else if (tabKey === 'com') {
                            editSetGroupValue('editComType', b.property_sub_type);
                            document.getElementById('editChoiceComType').value = b.property_sub_type;
                        } else if (tabKey === 'oth') {
                            editSetGroupValue('editOthLooking', b.property_sub_type);
                            document.getElementById('editChoiceOthLooking').value = b.property_sub_type;
                        }
                    }
                    
                    if (b.furniture_type) {
                        if (tabKey === 'res') {
                            editSetGroupValue('editResFurnish', b.furniture_type);
                            document.getElementById('editChoiceResFurnish').value = b.furniture_type;
                        } else if (tabKey === 'com') {
                            editSetGroupValue('editComFurnish', b.furniture_type);
                            document.getElementById('editChoiceComFurnish').value = b.furniture_type;
                        }
                    }
                    
                    if (tabKey === 'res') {
                        if (b.bhk_id) {
                            editSetGroupValue('editResSize', b.bhk_id.toString());
                            document.getElementById('editChoiceResSize').value = b.bhk_id.toString();
                        } else if (b.bhk) {
                            const allBhkChips = document.querySelectorAll('[data-group="editResSize"]');
                            for (let chip of allBhkChips) {
                                if (chip.textContent.trim() === b.bhk) {
                                    editSetGroupValue('editResSize', chip.dataset.value);
                                    document.getElementById('editChoiceResSize').value = chip.dataset.value;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (b.area) {
                        if (tabKey === 'res') {
                            document.getElementById('editResArea').value = b.area;
                        } else if (tabKey === 'com') {
                            document.getElementById('editComArea').value = b.area;
                        } else if (tabKey === 'oth') {
                            document.getElementById('editOthArea').value = b.area;
                        }
                    }
                    
                    if (b.other_option_details && tabKey === 'oth') {
                        document.getElementById('editOthDesc').value = b.other_option_details;
                    }
                    
                    if (b.firm_name) {
                        document.getElementById('editFirmName').value = b.firm_name;
                    }
                    if (b.gst_no) {
                        document.getElementById('editGstNo').value = b.gst_no;
                    }
                    
                    document.getElementById('editHouseNo').value = b.house_number || '';
                    document.getElementById('editBuilding').value = b.building_name || '';
                    document.getElementById('editPincode').value = b.pincode || '';
                    document.getElementById('editCity').value = b.city || 'Ahmedabad';
                    document.getElementById('editFullAddress').value = b.full_address || '';
                    
                    const paymentStatus = b.payment_status || 'pending';
                    const paymentRequiredCard = document.getElementById('editPaymentRequiredCard');
                    const scheduleDetailsCard = document.getElementById('editScheduleDetailsCard');
                    
                    lockEditFieldsForPayment(paymentStatus);
                    
                    if (paymentStatus !== 'paid') {
                        paymentRequiredCard.style.display = 'block';
                        scheduleDetailsCard.style.display = 'none';
                        paymentRequiredCard.setAttribute('data-booking-id', b.id);
                        
                        const paymentPriceField = document.getElementById('editPaymentPrice');
                        if (paymentPriceField) {
                            const priceToShow = b.price || b.price_estimate || 0;
                            paymentPriceField.value = priceToShow;
                        }
                    } else {
                        paymentRequiredCard.style.display = 'none';
                        scheduleDetailsCard.style.display = 'block';
                        
                        if (b.scheduled_date) {
                            const date = new Date(b.scheduled_date);
                            document.getElementById('editScheduledDate').value = date.toISOString().split('T')[0];
                        } else {
                            document.getElementById('editScheduledDate').value = '';
                        }
                        const notesField = document.getElementById('editScheduleNotes');
                        if (notesField) {
                            notesField.value = b.notes || b.other_details || '';
                        }
                    }
                    
                    updateEditPrice();
                    
                    editUpdatePaymentButtonState();
                } else {
                    await showSweetAlert('error', 'Error', 'Failed to load booking details. Please try again.');
                    modal.hide();
                }
            } catch (error) {
                console.error('Error fetching booking:', error);
                await showSweetAlert('error', 'Error', 'Error loading booking details. Please try again.');
                modal.hide();
            }
        }

        // Save Booking Edit
        async function saveBookingEdit() {
            const bookingId = document.getElementById('editBookingId').value;
            
            if (!bookingId) {
                await showSweetAlert('error', 'Error', 'Booking ID is missing. Please refresh the page and try again.');
                return;
            }
            
            const paymentRequiredCard = document.getElementById('editPaymentRequiredCard');
            const isPaymentPaid = !paymentRequiredCard || paymentRequiredCard.style.display === 'none';
            
            if (isPaymentPaid) {
                const scheduledDate = document.getElementById('editScheduledDate') ? document.getElementById('editScheduledDate').value || null : null;
                const scheduleNotes = document.getElementById('editScheduleNotes') ? document.getElementById('editScheduleNotes').value.trim() : null;
                
                if (!scheduledDate) {
                    await showSweetAlert('warning', 'Validation Error', 'Please select a scheduled date.');
                    return;
                }
                
                const bookingData = {
                    booking_id: bookingId,
                    scheduled_date: scheduledDate,
                    notes: scheduleNotes || null,
                };
                
                try {
                    const response = await fetch('{{ route("frontend.setup.update-booking") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(bookingData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success || response.ok) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editBookingModal'));
                        modal.hide();
                        
                        document.getElementById('successTitle').textContent = 'Schedule Updated!';
                        document.getElementById('successMessage').textContent = 'Schedule details have been updated successfully.';
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        await showSweetAlert('error', 'Error', data.message || 'Failed to update schedule. Please try again.');
                    }
                } catch (error) {
                    console.error('Error updating schedule:', error);
                    await showSweetAlert('error', 'Error', 'Failed to update schedule. Please try again.');
                }
                return;
            }
            
            editClearAllFieldErrors();
            const errors = [];
            
            const editTabRes = document.getElementById('editTabRes');
            const editTabCom = document.getElementById('editTabCom');
            const editTabOth = document.getElementById('editTabOth');
            
            const tabResVisible = editTabRes && editTabRes.style.display !== 'none';
            const tabComVisible = editTabCom && editTabCom.style.display !== 'none';
            const tabOthVisible = editTabOth && editTabOth.style.display !== 'none';
            
            const ownerType = document.getElementById('editChoiceOwnerType')?.value;
            if (!ownerType) {
                errors.push('Owner Type is required');
                editShowPillContainerError('editOwnerTypeContainer', 'err-editOwnerType', 'Owner Type is required.');
            } else {
                editHidePillContainerError('editOwnerTypeContainer', 'err-editOwnerType');
            }
            
            if (!editActivePropertyTab) {
                errors.push('Property Type is required');
                editShowPillContainerError('editPropertyTypeContainer', 'err-editPropertyType', 'Property Type is required.');
            } else {
                editHidePillContainerError('editPropertyTypeContainer', 'err-editPropertyType');
            }
            
            let area = 0;
            if (tabResVisible) {
                const rType = document.getElementById('editChoiceResType')?.value;
                const rFurn = document.getElementById('editChoiceResFurnish')?.value;
                const rSize = document.getElementById('editChoiceResSize')?.value;
                const rArea = document.getElementById('editResArea')?.value.trim();
                
                if (!rType) {
                    errors.push('Residential Property Sub Type is required');
                    editShowPillContainerError('editResTypesContainer', 'err-editResType', 'Property Sub Type is required.');
                } else {
                    editHidePillContainerError('editResTypesContainer', 'err-editResType');
                }
                
                if (!rFurn) {
                    errors.push('Furnish Type is required');
                    editShowPillContainerError('edit-res-furnish-container', 'err-editResFurnish', 'Furnish Type is required.');
                } else {
                    editHidePillContainerError('edit-res-furnish-container', 'err-editResFurnish');
                }
                
                if (!rSize) {
                    errors.push('Size (BHK/RK) is required');
                    editShowPillContainerError('edit-res-size-container', 'err-editResSize', 'Size (BHK / RK) is required.');
                } else {
                    editHidePillContainerError('edit-res-size-container', 'err-editResSize');
                }
                
                if (!rArea || Number(rArea) <= 0) {
                    errors.push('Super Built-up Area is required and must be greater than 0');
                    editShowFieldError('editResArea', 'err-editResArea', 'Super Built-up Area is required and must be greater than 0');
                } else {
                    area = parseFloat(rArea);
                    editMarkFieldValid('editResArea');
                }
            }
            
            if (tabComVisible) {
                const cType = document.getElementById('editChoiceComType')?.value;
                const cFurn = document.getElementById('editChoiceComFurnish')?.value;
                const cArea = document.getElementById('editComArea')?.value.trim();
                
                if (!cType) {
                    errors.push('Commercial Property Sub Type is required');
                    editShowPillContainerError('editComTypesContainer', 'err-editComType', 'Property Sub Type is required.');
                } else {
                    editHidePillContainerError('editComTypesContainer', 'err-editComType');
                }
                
                if (!cFurn) {
                    errors.push('Furnish Type is required');
                    editShowPillContainerError('edit-com-furnish-container', 'err-editComFurnish', 'Furnish Type is required.');
                } else {
                    editHidePillContainerError('edit-com-furnish-container', 'err-editComFurnish');
                }
                
                if (!cArea || Number(cArea) <= 0) {
                    errors.push('Super Built-up Area is required and must be greater than 0');
                    editShowFieldError('editComArea', 'err-editComArea', 'Super Built-up Area is required and must be greater than 0');
                } else {
                    area = parseFloat(cArea);
                    editMarkFieldValid('editComArea');
                }
            }
            
            if (tabOthVisible) {
                const oLooking = document.getElementById('editChoiceOthLooking')?.value;
                const oDesc = document.getElementById('editOthDesc')?.value.trim();
                const oArea = document.getElementById('editOthArea')?.value.trim();
                const hasSelection = Boolean(oLooking);
                const hasOther = Boolean(oDesc);
                
                if (!hasSelection && !hasOther) {
                    errors.push('Please select an option or enter Other option');
                    editShowPillContainerError('editOthTypesContainer', 'err-editOthLooking', 'Select an option or enter Other option.');
                    editShowFieldError('editOthDesc', 'err-editOthDesc', 'Other option is required if none of the options are selected.');
                } else {
                    editHidePillContainerError('editOthTypesContainer', 'err-editOthLooking');
                    editHideFieldError('editOthDesc', 'err-editOthDesc');
                }
                
                if (!oArea || Number(oArea) <= 0) {
                    errors.push('Super Built-up Area is required and must be greater than 0');
                    editShowFieldError('editOthArea', 'err-editOthArea', 'Super Built-up Area is required and must be greater than 0');
                } else {
                    area = parseFloat(oArea);
                    editMarkFieldValid('editOthArea');
                }
            }
            
            const h = document.getElementById('editHouseNo')?.value.trim();
            const b = document.getElementById('editBuilding')?.value.trim();
            const p = document.getElementById('editPincode')?.value.trim();
            const f = document.getElementById('editFullAddress')?.value.trim();
            
            if (!h) {
                errors.push('House / Office No. is required');
                editShowFieldError('editHouseNo', 'err-editHouseNo', 'House / Office No. is required.');
            } else {
                editMarkFieldValid('editHouseNo');
            }
            
            if (!b) {
                errors.push('Society / Building Name is required');
                editShowFieldError('editBuilding', 'err-editBuilding', 'Society / Building Name is required.');
            } else {
                editMarkFieldValid('editBuilding');
            }
            
            if (!p) {
                errors.push('Pincode is required');
                editShowFieldError('editPincode', 'err-editPincode', 'Pincode is required.');
            } else if (!/^[0-9]{6}$/.test(p)) {
                errors.push('Pincode must be a valid 6-digit number');
                editShowFieldError('editPincode', 'err-editPincode', 'Pincode must be a valid 6-digit number');
            } else {
                editMarkFieldValid('editPincode');
            }
            
            if (!f) {
                errors.push('Full address is required');
                editShowFieldError('editFullAddress', 'err-editFullAddress', 'Full address is required.');
            } else {
                editMarkFieldValid('editFullAddress');
            }
            
            if (errors.length > 0) {
                const errorMessage = '• ' + errors.join('<br>• ');
                await showSweetAlert('error', 'Validation Error', errorMessage, true);
                return;
            }
            
            const mainPropertyTypeEl = document.getElementById('editMainPropertyType');
            const mainPropertyType = mainPropertyTypeEl ? mainPropertyTypeEl.value || 'Residential' : 'Residential';
            
            const bookingData = {
                booking_id: bookingId,
                owner_type: ownerType,
                main_property_type: mainPropertyType,
                residential_property_type: tabResVisible ? document.getElementById('editChoiceResType')?.value || null : null,
                residential_furnish: tabResVisible ? document.getElementById('editChoiceResFurnish')?.value || null : null,
                residential_size: tabResVisible ? document.getElementById('editChoiceResSize')?.value || null : null,
                residential_area: tabResVisible ? area : null,
                commercial_property_type: tabComVisible ? document.getElementById('editChoiceComType')?.value || null : null,
                commercial_furnish: tabComVisible ? document.getElementById('editChoiceComFurnish')?.value || null : null,
                commercial_area: tabComVisible ? area : null,
                other_looking: tabOthVisible ? document.getElementById('editChoiceOthLooking')?.value || null : null,
                other_option_details: tabOthVisible ? document.getElementById('editOthDesc')?.value?.trim() || null : null,
                other_area: tabOthVisible ? area : null,
                firm_name: document.getElementById('editFirmName')?.value?.trim() || null,
                gst_no: document.getElementById('editGstNo')?.value?.trim() || null,
                house_number: h,
                building_name: b,
                pincode: p,
                city: document.getElementById('editCity')?.value || 'Ahmedabad',
                full_address: f,
                scheduled_date: document.getElementById('editScheduledDate')?.value || null,
                notes: document.getElementById('editScheduleNotes')?.value?.trim() || null,
                price: (() => {
                    const paymentPriceField = document.getElementById('editPaymentPrice');
                    if (paymentPriceField && paymentPriceField.value) {
                        return parseFloat(paymentPriceField.value) || null;
                    }
                    if (area > 0) {
                        return calculatePriceFromArea(area);
                    }
                    return null;
                })()
            };
            
            try {
                const response = await fetch('{{ route("frontend.setup.update-booking") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                });
                
                const data = await response.json();
                
                if (data.success || response.ok) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editBookingModal'));
                    modal.hide();
                    
                    document.getElementById('successTitle').textContent = 'Booking Updated!';
                    document.getElementById('successMessage').textContent = 'Booking details have been updated successfully.';
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    await showSweetAlert('error', 'Error', data.message || 'Failed to update booking. Please try again.');
                }
            } catch (error) {
                console.error('Error updating booking:', error);
                await showSweetAlert('error', 'Error', 'Failed to update booking. Please try again.');
            }
        }

        // Initiate payment from edit modal
        async function initiatePaymentFromEdit() {
            const paymentCard = document.getElementById('editPaymentRequiredCard');
            const bookingId = paymentCard.getAttribute('data-booking-id');
            const paymentPrice = document.getElementById('editPaymentPrice')?.value || 0;
            
            if (!bookingId) {
                await showSweetAlert('error', 'Error', 'Booking ID not found. Please refresh and try again.');
                return;
            }
            
            if (!paymentPrice || parseFloat(paymentPrice) <= 0) {
                await showSweetAlert('warning', 'Validation Error', 'Please enter a valid area (sq. ft.) to calculate the price before making payment.');
                return;
            }
            
            const tabResVisible = document.getElementById('editTabRes').style.display !== 'none';
            const tabComVisible = document.getElementById('editTabCom').style.display !== 'none';
            const tabOthVisible = document.getElementById('editTabOth').style.display !== 'none';
            
            let area = 0;
            if (tabResVisible) {
                area = parseFloat(document.getElementById('editResArea').value) || 0;
            } else if (tabComVisible) {
                area = parseFloat(document.getElementById('editComArea').value) || 0;
            } else if (tabOthVisible) {
                area = parseFloat(document.getElementById('editOthArea').value) || 0;
            }
            
            if (area <= 0) {
                await showSweetAlert('warning', 'Validation Error', 'Please enter a valid area (sq. ft.) to calculate the price before making payment.');
                return;
            }
            
            try {
                const bookingIdValue = document.getElementById('editBookingId').value;
                
                const updateData = {
                    booking_id: bookingIdValue,
                    price: parseFloat(paymentPrice)
                };
                
                if (tabResVisible) {
                    updateData.residential_area = area;
                } else if (tabComVisible) {
                    updateData.commercial_area = area;
                } else if (tabOthVisible) {
                    updateData.other_area = area;
                }
                
                const saveResponse = await fetch('{{ route("frontend.setup.update-booking") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                let saveResult;
                try {
                    saveResult = await saveResponse.json();
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    throw new Error('Invalid response from server.');
                }
                
                if (!saveResponse.ok || !saveResult.success) {
                    const errorMsg = saveResult?.message || 'Failed to update booking price. Please try again.';
                    await showSweetAlert('error', 'Error', errorMsg);
                    console.error('Save booking error:', saveResult);
                    return;
                }
                
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editBookingModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                setTimeout(() => {
                    window.location.href = '{{ route("frontend.booking-dashboard") }}';
                }, 500);
                
            } catch (error) {
                console.error('Error saving booking before payment:', error);
                await showSweetAlert('error', 'Error', 'Failed to update booking: ' + (error.message || 'Please try again.'));
            }
        }

        // Add real-time validation listeners for edit modal
        document.addEventListener('DOMContentLoaded', function() {
            ['editResArea', 'editComArea', 'editOthArea'].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('input', function() {
                        const value = this.value.trim();
                        if (value && Number(value) > 0) {
                            editHideFieldError(id, 'err-' + id);
                            editMarkFieldValid(id);
                        }
                        editUpdatePaymentButtonState();
                    });
                }
            });

            ['editHouseNo', 'editBuilding', 'editPincode', 'editFullAddress'].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('input', function() {
                        const value = this.value.trim();
                        if (id === 'editPincode') {
                            if (value && /^[0-9]{6}$/.test(value)) {
                                editHideFieldError(id, 'err-' + id);
                                editMarkFieldValid(id);
                            }
                        } else {
                            if (value) {
                                editHideFieldError(id, 'err-' + id);
                                editMarkFieldValid(id);
                            }
                        }
                        editUpdatePaymentButtonState();
                    });
                }
            });

            const othDescInput = document.getElementById('editOthDesc');
            if (othDescInput) {
                othDescInput.addEventListener('input', function() {
                    const value = this.value.trim();
                    const othLooking = document.getElementById('editChoiceOthLooking')?.value;
                    if (value || othLooking) {
                        editHideFieldError('editOthDesc', 'err-editOthDesc');
                        editHidePillContainerError('editOthTypesContainer', 'err-editOthLooking');
                    }
                    editUpdatePaymentButtonState();
                });
            }

            document.querySelectorAll('[data-group^="edit"]').forEach(el => {
                el.addEventListener('click', function() {
                    setTimeout(() => {
                        editUpdatePaymentButtonState();
                    }, 100);
                });
            });
        });

        // ========== PAYMENT FUNCTIONS ==========
        
        // Initiate Payment
        async function initiatePayment(bookingId) {
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            const paymentContent = document.getElementById('paymentModalContent');
            
            // Show loading state
            paymentContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Preparing payment gateway...</p>
                </div>
            `;
            
            paymentModal.show();
            
            try {
                // Create Cashfree session
                const response = await fetch('{{ route("frontend.setup.payment.session") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ booking_id: bookingId })
                });
                
                let result;
                try {
                    result = await response.json();
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    throw new Error('Invalid response from server. Please try again.');
                }
                
                if (!response.ok) {
                    const errorMessage = result?.message || `HTTP error! status: ${response.status}`;
                    throw new Error(errorMessage);
                }
                
                if (result.success && result.data) {
                    const { payment_session_id, mode } = result.data;
                    
                    // Load Cashfree SDK if not already loaded
                    if (typeof Cashfree === 'undefined') {
                        const script = document.createElement('script');
                        script.src = 'https://sdk.cashfree.com/js/v3/cashfree.js';
                        script.onload = () => {
                            openCashfreeCheckout(payment_session_id, mode, paymentModal);
                        };
                        script.onerror = () => {
                            paymentContent.innerHTML = `
                                <div class="alert alert-danger" role="alert">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    Failed to load payment gateway. Please refresh the page and try again.
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            `;
                        };
                        document.head.appendChild(script);
                    } else {
                        openCashfreeCheckout(payment_session_id, mode, paymentModal);
                    }
                } else {
                    throw new Error(result.message || 'Failed to create payment session');
                }
            } catch (error) {
                console.error('Error initiating payment:', error);
                paymentContent.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> ${error.message || 'Failed to initiate payment. Please try again.'}
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                `;
            }
        }

        function openCashfreeCheckout(paymentSessionId, mode, paymentModal) {
            try {
                // Ensure Cashfree SDK is loaded
                if (typeof Cashfree === 'undefined') {
                    throw new Error('Cashfree SDK not loaded. Please refresh the page and try again.');
                }
                
                const cashfreeInstance = Cashfree({ mode: mode || 'sandbox' });
                
                // Hide the payment modal before opening checkout
                if (paymentModal) {
                    paymentModal.hide();
                }
                
                // Open Cashfree checkout (opens in modal/popup)
                console.log('Opening Cashfree checkout with session:', paymentSessionId);
                
                cashfreeInstance.checkout({
                    paymentSessionId: paymentSessionId
                }).then(function(result) {
                    console.log('Cashfree checkout promise resolved:', result);
                    
                    // The promise resolves when the checkout modal closes
                    if (result && result.error) {
                        console.log('Payment cancelled or failed:', result.error.message);
                        if (result.error.message && !result.error.message.includes('cancelled')) {
                            console.warn('Payment error:', result.error.message);
                        }
                    } else {
                        // Payment completed successfully
                        console.log('Payment completed - reloading page to check status');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }).catch(function(error) {
                    console.error('Cashfree checkout exception:', error);
                    if (error && error.message && 
                        !error.message.toLowerCase().includes('cancelled') && 
                        !error.message.toLowerCase().includes('closed')) {
                        console.error('Unexpected payment error:', error);
                    }
                });
            } catch (error) {
                console.error('Error opening Cashfree checkout:', error);
                alert('Failed to open payment gateway: ' + (error.message || 'Please refresh the page and try again.'));
                // Show error in payment modal if it's still open
                if (paymentModal) {
                    const paymentContent = document.getElementById('paymentModalContent');
                    if (paymentContent) {
                        paymentContent.innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                <strong>Error:</strong> ${error.message || 'Failed to open payment gateway. Please refresh the page and try again.'}
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        `;
                    }
                }
            }
        }
    </script>
    
    <!-- Download Receipt Function -->
    <script>
        let receiptPrintTriggered = false; // Global flag to prevent multiple print triggers
        
        function downloadReceipt(bookingId) {
            // Reset the print trigger flag for new download
            receiptPrintTriggered = false;
            
            // Get the receipt URL with download parameter (triggers auto-print in receipt page)
            const receiptUrl = "{{ url('/frontend/receipt/download') }}/" + bookingId + "?download=1";
            
            // Get modal and iframe elements
            const modal = document.getElementById('receiptDownloadModal');
            const iframe = document.getElementById('receiptIframe');
            
            // Reset iframe completely
            iframe.src = '';
            iframe.onload = null; // Clear previous onload handler
            
            // Show modal first
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // Set iframe source after modal is shown (ensures proper sizing)
            setTimeout(function() {
                iframe.src = receiptUrl;
                
                // Set onload handler only once
                iframe.onload = function() {
                    // Receipt page has its own auto-print, so we don't need to trigger from parent
                    // The receipt page will handle printing automatically
                    console.log('Receipt loaded in iframe - auto-print will be handled by receipt page');
                };
            }, 300);
        }
        
        function closeReceiptModal() {
            receiptPrintTriggered = false; // Reset flag when closing
            const modal = document.getElementById('receiptDownloadModal');
            const iframe = document.getElementById('receiptIframe');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            // Clear iframe source and onload handler when modal is closed
            iframe.onload = null;
            setTimeout(function() {
                iframe.src = '';
            }, 300);
        }
        
        // Close modal and clear iframe when modal is hidden
        document.getElementById('receiptDownloadModal')?.addEventListener('hidden.bs.modal', function() {
            receiptPrintTriggered = false; // Reset flag
            const iframe = document.getElementById('receiptIframe');
            iframe.onload = null;
            iframe.src = '';
        });
    </script>
    
    <!-- Cashfree SDK -->
    <script>
        window.CashfreeConfig = {
            mode: "{{ config('cashfree.env') === 'production' ? 'production' : 'sandbox' }}"
        };
    </script>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
@endsection

