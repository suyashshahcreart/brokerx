@extends('frontend.layouts.base', ['title' => 'Booking Details - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/setup_page.css') }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/booking_show_page.css') }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/booking_show_v2.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    <!-- Booking Details Hero -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <p class="text-uppercase fw-bold small mb-2 opacity-75">Booking Details</p>
                    <h1 class="display-5 fw-bold mb-3">Booking #{{ $booking->id }}</h1>
                    <p class="lead mb-0 opacity-75">Complete overview of your booking with analytics and timeline</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-light fw-semibold">
                        <i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="page bg-setup-form py-5">
        <div class="container">
            @php
                // Get FTP URL for tour_live status using Booking model method
                $tourFtpUrl = $booking->getTourLiveUrl();
            @endphp
            
            <!-- Booking Status Header Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="booking-status-header-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                    <div class="status-icon-large status-{{ $status }}">
                                        @if(in_array($status, ['schedul_accepted', 'reschedul_accepted']))
                                            <i class="fa-solid fa-check-circle"></i>
                                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                            <i class="fa-solid fa-clock"></i>
                                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                            <i class="fa-solid fa-times-circle"></i>
                                        @elseif($status === 'reschedul_blocked')
                                            <i class="fa-solid fa-ban"></i>
                                        @else
                                            <i class="fa-solid fa-info-circle"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="mb-1" style="font-family: 'Montagu Slab', serif; font-weight: 700;">
                                            {{ $statusText }}
                                        </h3>
                                        <p class="mb-0 text-muted">
                                            @if($isPaymentPaid)
                                                <i class="fa-solid fa-check-circle text-success me-1"></i>Payment Completed
                                            @else
                                                <i class="fa-solid fa-clock text-warning me-1"></i>Payment Pending
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="booking-meta-info">
                                    <div class="meta-item">
                                        <i class="fa-solid fa-calendar-plus me-2"></i>
                                        <span>Created: {{ $booking->created_at->format('M d, Y') }}</span>
                                    </div>
                                    @if($booking->updated_at && $booking->updated_at != $booking->created_at)
                                        <div class="meta-item">
                                            <i class="fa-solid fa-clock-rotate-left me-2"></i>
                                            <span>Updated: {{ $booking->updated_at->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics & Insights Section -->
            <div class="row g-4 mb-4">
                <!-- Completion Progress -->
                <div class="col-md-4">
                    <div class="analytics-card">
                        <div class="analytics-icon analytics-icon-primary">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-value">{{ $completionPercentage }}%</div>
                            <div class="analytics-label">Completion</div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $completionPercentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Days Since Created -->
                <div class="col-md-4">
                    <div class="analytics-card">
                        <div class="analytics-icon analytics-icon-info">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-value">{{ $daysSinceCreated }}</div>
                            <div class="analytics-label">Days Since Created</div>
                        </div>
                    </div>
                </div>
                
                <!-- Attempts -->
                <div class="col-md-4">
                    <div class="analytics-card">
                        <div class="analytics-icon analytics-icon-{{ $attemptCount >= $maxAttempts ? 'danger' : 'warning' }}">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-value">{{ $attemptCount }}/{{ $maxAttempts }}</div>
                            <div class="analytics-label">Schedule Attempts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    
                    <!-- Booking Details Card -->
                    <div class="detail-card mb-4">
                        <div class="detail-card-header">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-info-circle me-2 text-primary"></i>Complete Booking Details
                            </h5>
                        </div>
                        <div class="detail-card-body">
                            <div class="row g-4">
                                <!-- Property Details -->
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <div class="detail-section-title">
                                            <i class="fa-solid fa-building me-2"></i>Property Information
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Owner Type:</span>
                                            <span class="detail-value">{{ $booking->owner_type ?? '-' }}</span>
                                        </div>
                                        @if($booking->firm_name)
                                            <div class="detail-item">
                                                <span class="detail-label">Company Name:</span>
                                                <span class="detail-value">{{ $booking->firm_name }}</span>
                                            </div>
                                        @endif
                                        @if($booking->gst_no)
                                            <div class="detail-item">
                                                <span class="detail-label">GST No:</span>
                                                <span class="detail-value">{{ $booking->gst_no }}</span>
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <span class="detail-label">Property Type:</span>
                                            <span class="detail-value">{{ $booking->propertyType?->name ?? '-' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Property Sub Type:</span>
                                            <span class="detail-value">{{ $booking->propertySubType?->name ?? '-' }}</span>
                                        </div>
                                        @if($booking->bhk)
                                            <div class="detail-item">
                                                <span class="detail-label">Size (BHK/RK):</span>
                                                <span class="detail-value">{{ $booking->bhk->name }}</span>
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <span class="detail-label">Furnish Type:</span>
                                            <span class="detail-value">{{ $booking->furniture_type ?? '-' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Area:</span>
                                            <span class="detail-value">{{ number_format($booking->area ?? 0) }} sq. ft.</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address Details -->
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <div class="detail-section-title">
                                            <i class="fa-solid fa-location-dot me-2"></i>Address Information
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">House/Office No:</span>
                                            <span class="detail-value">{{ $booking->house_no ?? '-' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Building/Society:</span>
                                            <span class="detail-value">{{ $booking->building ?? '-' }}</span>
                                        </div>
                                        @if($booking->society_name)
                                            <div class="detail-item">
                                                <span class="detail-label">Society Name:</span>
                                                <span class="detail-value">{{ $booking->society_name }}</span>
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <span class="detail-label">City:</span>
                                            <span class="detail-value">{{ $booking->city?->name ?? 'Ahmedabad' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">State:</span>
                                            <span class="detail-value">{{ $booking->state?->name ?? 'Gujarat' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Pincode:</span>
                                            <span class="detail-value">{{ $booking->pin_code ?? '-' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Full Address:</span>
                                            <span class="detail-value">{{ $booking->full_address ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking History Timeline -->
                    <div class="detail-card mb-4">
                        <div class="detail-card-header">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Booking History & Timeline
                            </h5>
                        </div>
                        <div class="detail-card-body">
                            @if($history->count() > 0)
                                <div class="timeline">
                                    @foreach($history as $index => $historyItem)
                                        @php
                                            $isLast = $loop->last;
                                            $statusClass = match($historyItem->to_status) {
                                                'schedul_accepted', 'reschedul_accepted' => 'success',
                                                'schedul_pending', 'reschedul_pending' => 'warning',
                                                'schedul_decline', 'reschedul_decline' => 'danger',
                                                default => 'info'
                                            };
                                            $statusIcon = match($historyItem->to_status) {
                                                'schedul_accepted', 'reschedul_accepted' => 'fa-check-circle',
                                                'schedul_pending', 'reschedul_pending' => 'fa-clock',
                                                'schedul_decline', 'reschedul_decline' => 'fa-times-circle',
                                                default => 'fa-info-circle'
                                            };
                                            $statusText = match($historyItem->to_status) {
                                                'schedul_pending' => 'Schedule Pending',
                                                'schedul_accepted' => 'Schedule Accepted',
                                                'schedul_decline' => 'Schedule Declined',
                                                'reschedul_pending' => 'Reschedule Pending',
                                                'reschedul_accepted' => 'Reschedule Accepted',
                                                'reschedul_decline' => 'Reschedule Declined',
                                                default => ucfirst(str_replace('_', ' ', $historyItem->to_status))
                                            };
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-marker timeline-marker-{{ $statusClass }}">
                                                <i class="fa-solid {{ $statusIcon }}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <h6 class="mb-1">{{ $statusText }}</h6>
                                                    <span class="timeline-date">{{ $historyItem->created_at->format('M d, Y h:i A') }}</span>
                                                </div>
                                                @if($historyItem->changedBy)
                                                    <p class="timeline-user mb-1">
                                                        <i class="fa-solid fa-user me-1"></i>
                                                        {{ $historyItem->changedBy->firstname }} {{ $historyItem->changedBy->lastname }}
                                                    </p>
                                                @endif
                                                @if(isset($historyItem->metadata['reason']) && $historyItem->metadata['reason'])
                                                    <div class="timeline-note">
                                                        <i class="fa-solid fa-comment me-1"></i>
                                                        <strong>Reason:</strong> {{ $historyItem->metadata['reason'] }}
                                                    </div>
                                                @endif
                                                @if(isset($historyItem->metadata['admin_notes']) && $historyItem->metadata['admin_notes'])
                                                    <div class="timeline-note">
                                                        <i class="fa-solid fa-sticky-note me-1"></i>
                                                        <strong>Admin Notes:</strong> {{ $historyItem->metadata['admin_notes'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fa-solid fa-history mb-3" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mb-0">No history available for this booking.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    
                    <!-- Payment & Pricing Card -->
                    <div class="detail-card mb-4">
                        <div class="detail-card-header">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-indian-rupee-sign me-2 text-primary"></i>Payment & Pricing
                            </h5>
                        </div>
                        <div class="detail-card-body">
                            <div class="price-display-large mb-3">
                                <div class="price-amount">₹{{ number_format($priceToShow, 0) }}</div>
                                <span class="badge bg-{{ $isPaymentPaid ? 'success' : 'warning' }} ms-2">
                                    {{ ucfirst($booking->payment_status ?? 'pending') }}
                                </span>
                            </div>
                            
                            @if($isPaymentPaid)
                                @if($booking->cashfree_order_id)
                                    <div class="detail-item">
                                        <span class="detail-label">Order ID:</span>
                                        <span class="detail-value small">{{ $booking->cashfree_order_id }}</span>
                                    </div>
                                @endif
                                @if($booking->cashfree_reference_id)
                                    <div class="detail-item">
                                        <span class="detail-label">Reference ID:</span>
                                        <span class="detail-value small">{{ $booking->cashfree_reference_id }}</span>
                                    </div>
                                @endif
                                @if($booking->cashfree_payment_method)
                                    <div class="detail-item">
                                        <span class="detail-label">Payment Method:</span>
                                        <span class="detail-value">{{ ucfirst($booking->cashfree_payment_method) }}</span>
                                    </div>
                                @endif
                                @if($booking->cashfree_payment_at)
                                    <div class="detail-item">
                                        <span class="detail-label">Payment Date:</span>
                                        <span class="detail-value">{{ $booking->cashfree_payment_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                @endif
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="downloadReceipt({{ $booking->id }})">
                                        <i class="fa-solid fa-download me-2"></i>Download Receipt
                                    </button>
                                </div>
                            @else
                                @if($isReadyForPayment)
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-success w-100" onclick="initiatePayment({{ $booking->id }})">
                                            <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                                        </button>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-0" role="alert">
                                        <small>
                                            <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                            Complete booking details to proceed with payment
                                        </small>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    </div>

                    <!-- Schedule & Status Card -->
                    <div class="detail-card mb-4">
                        <div class="detail-card-header">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-calendar me-2 text-primary"></i>Schedule & Status
                            </h5>
                        </div>
                        <div class="detail-card-body">
                            @php
                                $showScheduledDate = $booking->booking_date && !in_array($status, ['schedul_decline', 'reschedul_decline', 'reschedul_blocked']);
                            @endphp
                            
                            @if($status === 'tour_live')
                                {{-- Tour is live - show live tour button (regardless of payment status) --}}
                                <div class="alert alert-success py-2 mb-3" role="alert">
                                    <small class="d-block mb-1">
                                        <i class="fa-solid fa-video me-1"></i>
                                        <strong>Tour is Live</strong>
                                    </small>
                                    <small class="text-muted d-block mb-2">
                                        Your property tour is now live and accessible.
                                    </small>
                                </div>
                                <a href="{{ $tourFtpUrl }}" target="_blank" class="btn btn-success w-100">
                                    <i class="fa-solid fa-video me-2"></i> View Tour 
                                </a>
                            @elseif($isPaymentPaid)
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
                                    <div class="alert alert-warning py-2 mb-3" role="alert">
                                        <small class="d-block mb-1"><i class="fa-solid fa-clock me-1"></i><strong>Awaiting Admin Approval</strong></small>
                                        @if($showScheduledDate)
                                            <small class="text-muted d-block mb-1">
                                                <i class="fa-solid fa-calendar me-1"></i>Requested Date: {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                                            </small>
                                        @endif
                                        <small class="text-muted d-block">
                                            <i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}
                                        </small>
                                    </div>
                                @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                    <div class="alert alert-danger py-2 mb-3" role="alert">
                                        <small class="d-block mb-1"><i class="fa-solid fa-times-circle me-1"></i><strong>Schedule Declined</strong></small>
                                        <small class="text-muted d-block mb-1">
                                            <i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}
                                        </small>
                                        @if($declineReason)
                                            <small class="d-block mt-2">
                                                <i class="fa-solid fa-exclamation-triangle me-1"></i><strong>Reason:</strong> {{ $declineReason }}
                                            </small>
                                        @endif
                                    </div>
                                    @if($attemptCount < $maxAttempts)
                                        <button type="button" class="btn btn-primary w-100" onclick="openScheduleModal({{ $booking->id }})">
                                            <i class="fa-solid fa-calendar-plus me-2"></i>Request Again
                                        </button>
                                    @endif
                                @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted']) && $showScheduledDate)
                                    <div class="alert alert-success py-2 mb-3" role="alert">
                                        <small class="d-block mb-1">
                                            <i class="fa-solid fa-calendar-check me-1"></i>
                                            <strong>Scheduled:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                                            @if($booking->scheduled_time)
                                                at {{ $booking->scheduled_time }}
                                            @else
                                                (Time TBD)
                                            @endif
                                        </small>
                                        @if($adminNotes)
                                            <small class="d-block mt-2">
                                                <i class="fa-solid fa-sticky-note me-1"></i><strong>Admin Notes:</strong> {{ $adminNotes }}
                                            </small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="openScheduleModal({{ $booking->id }})">
                                        <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                                    </button>
                                @elseif($showScheduledDate)
                                    <div class="alert alert-info py-2 mb-3" role="alert">
                                        <small class="d-block">
                                            <i class="fa-solid fa-calendar-check me-1"></i>
                                            <strong>Scheduled:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="openScheduleModal({{ $booking->id }})">
                                        <i class="fa-solid fa-calendar-edit me-2"></i>Reschedule
                                    </button>
                                @else
                                    <div class="alert alert-info py-2 mb-3" role="alert">
                                        <small class="d-block">
                                            <i class="fa-solid fa-calendar me-1"></i>Not Scheduled
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" onclick="openScheduleModal({{ $booking->id }})">
                                        <i class="fa-solid fa-calendar-plus me-2"></i>Schedule Appointment
                                    </button>
                                @endif
                            @else
                                <div class="alert alert-warning py-2 mb-0" role="alert">
                                    <small>
                                        <i class="fa-solid fa-info-circle me-1"></i>
                                        Complete payment to schedule your appointment
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Next Steps & Actions -->
                    @if(count($nextSteps) > 0)
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-lightbulb me-2 text-primary"></i>Suggested Next Steps
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                @foreach($nextSteps as $step)
                                    <div class="next-step-item next-step-{{ $step['priority'] }}">
                                        <div class="next-step-icon">
                                            <i class="fa-solid {{ $step['icon'] }}"></i>
                                        </div>
                                        <div class="next-step-content">
                                            <h6 class="mb-1">{{ $step['title'] }}</h6>
                                            <p class="mb-2 small text-muted">{{ $step['description'] }}</p>
                                            @if($step['action'] === 'Edit')
                                                <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-edit me-1"></i>{{ $step['action'] }}
                                                </a>
                                            @elseif($step['action'] === 'Pay Now')
                                                <button type="button" class="btn btn-sm btn-success" onclick="initiatePayment({{ $booking->id }})">
                                                    <i class="fa-solid fa-credit-card me-1"></i>{{ $step['action'] }}
                                                </button>
                                            @elseif($step['action'] === 'Schedule')
                                                <button type="button" class="btn btn-sm btn-primary" onclick="openScheduleModal({{ $booking->id }})">
                                                    <i class="fa-solid fa-calendar-plus me-1"></i>{{ $step['action'] }}
                                                </button>
                                            @elseif($step['action'] === 'View')
                                                <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-eye me-1"></i>{{ $step['action'] }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Booking Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="blockedWarning" style="display: none;"></div>
                    <div id="rescheduleWarning" style="display: none;"></div>
                    <form id="scheduleForm">
                        <input type="hidden" id="scheduleBookingId">
                        <div class="mb-3">
                            <label class="form-label">Select Date <span class="text-danger">*</span></label>
                            <div class="date-input-group">
                                <input type="text" class="form-control" id="scheduleDate" placeholder="Select a date" required readonly>
                                <i class="ri-calendar-line date-icon" id="scheduleDateIcon"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="scheduleNotes" rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleBtn" onclick="saveSchedule()">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
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

    <!-- Receipt Download Modal -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Booking data for JavaScript
        @php
            $bookingData = [
                'id' => $booking->id,
                'status' => $booking->status ?? 'pending',
                'booking_date' => $booking->booking_date,
                'scheduled_date' => $booking->booking_date,
                'booking_notes' => $booking->booking_notes ?? $booking->notes ?? '',
                'attemptCount' => $attemptCount,
                'maxAttempts' => $maxAttempts
            ];
        @endphp
        const booking = @json($bookingData);

        let scheduleDatePicker = null;

        // Download Receipt Function
        function downloadReceipt(bookingId) {
            const receiptUrl = "{{ url('/frontend/receipt/download') }}/" + bookingId + "?download=1";
            const modal = document.getElementById('receiptDownloadModal');
            const iframe = document.getElementById('receiptIframe');
            
            iframe.src = '';
            iframe.onload = null;
            
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            setTimeout(function() {
                iframe.src = receiptUrl;
            }, 300);
        }
        
        function closeReceiptModal() {
            const modal = document.getElementById('receiptDownloadModal');
            const iframe = document.getElementById('receiptIframe');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            setTimeout(function() {
                iframe.src = '';
            }, 300);
        }
        
        document.getElementById('receiptDownloadModal')?.addEventListener('hidden.bs.modal', function() {
            const iframe = document.getElementById('receiptIframe');
            iframe.onload = null;
            iframe.src = '';
        });

        // Open Schedule Modal
        window.openScheduleModal = async function(bookingId) {
            document.getElementById('scheduleBookingId').value = bookingId;
            document.getElementById('scheduleNotes').value = booking.booking_notes || '';

            const blockedDiv = document.getElementById('blockedWarning');
            const warningDiv = document.getElementById('rescheduleWarning');
            const scheduleModal = document.getElementById('scheduleModal');
            const scheduleDateInput = document.getElementById('scheduleDate');
            const scheduleNotesInput = document.getElementById('scheduleNotes');
            const saveScheduleBtn = document.getElementById('saveScheduleBtn');
            
            let status = booking.status || 'pending';
            let oldDate = booking.booking_date || null;
            let attemptCount = booking.attemptCount || 0;
            let maxAttempts = booking.maxAttempts || {{ $maxAttempts }};
            
            const isBlocked = status === 'reschedul_blocked' || attemptCount >= maxAttempts;
            
            if (scheduleModal) {
                scheduleModal.dataset.originalDate = oldDate || '';
                scheduleModal.dataset.originalStatus = status;
                scheduleModal.dataset.originalNotes = booking.booking_notes || '';
            }
            
            if (isBlocked) {
                const adminEmail = '{{ \App\Models\Setting::where("name", "support_email")->value("value") ?? "contact@proppik.com" }}';
                const adminPhone = '{{ \App\Models\Setting::where("name", "support_phone")->value("value") ?? "9898363026" }}';
                
                const blockedHTML = `
                    <div class="alert alert-danger py-3 mb-3" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-ban me-2 mt-1"></i>
                            <div>
                                <strong class="d-block mb-2">Maximum Attempts Reached</strong>
                                <p class="mb-2 small">You have lost all your attempts (${attemptCount}/${maxAttempts}).</p>
                                <p class="mb-0 small">Contact: ${adminPhone} | ${adminEmail}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                if (blockedDiv) {
                    blockedDiv.innerHTML = blockedHTML;
                    blockedDiv.style.display = 'block';
                }
                if (warningDiv) warningDiv.style.display = 'none';
                
                if (scheduleDateInput) scheduleDateInput.disabled = true;
                if (scheduleNotesInput) scheduleNotesInput.disabled = true;
                if (saveScheduleBtn) saveScheduleBtn.disabled = true;
            } else {
                if (blockedDiv) {
                    blockedDiv.innerHTML = '';
                    blockedDiv.style.display = 'none';
                }
                
                if (scheduleDateInput) scheduleDateInput.disabled = false;
                if (scheduleNotesInput) scheduleNotesInput.disabled = false;
                if (saveScheduleBtn) saveScheduleBtn.disabled = false;
                
                const isReschedule = ['schedul_accepted', 'reschedul_accepted'].includes(status);
                if (isReschedule && oldDate) {
                    const oldDateObj = new Date(oldDate);
                    const formattedOldDate = oldDateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    const remainingAttempts = maxAttempts - attemptCount;
                    
                    let warningHTML = `
                        <div class="alert alert-warning py-3 mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fa-solid fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <strong class="d-block mb-2">Reschedule Warning</strong>
                                    <p class="mb-2 small">Current Date: ${formattedOldDate}</p>
                                    <p class="mb-0 small">Attempts: ${attemptCount}/${maxAttempts} (${remainingAttempts} remaining)</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if (warningDiv) {
                        warningDiv.innerHTML = warningHTML;
                        warningDiv.style.display = 'block';
                    }
                } else {
                    if (warningDiv) warningDiv.style.display = 'none';
                }
            }

            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
            
            if (!isBlocked) {
                setTimeout(() => {
                    initScheduleDatePicker(oldDate || null);
                }, 100);
            }
        };

        // Fetch holidays and available days
        async function fetchHolidaysAndAvailableDays() {
            try {
                const response = await fetch('{{ url("/api/holidays") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to fetch holidays');
                
                const data = await response.json();
                return {
                    holidays: (data.holidays || []).map(h => h.date),
                    availableDays: data.day_limit?.value ? parseInt(data.day_limit.value, 10) : 30,
                    disabledDates: data.disabled_dates || []
                };
            } catch (error) {
                console.error('Error fetching holidays:', error);
                return { holidays: [], availableDays: 30, disabledDates: [] };
            }
        }

        // Initialize Flatpickr
        function initScheduleDatePicker(selectedDate = null) {
            if (scheduleDatePicker) {
                scheduleDatePicker.destroy();
            }
            
            const scheduleDateInput = document.getElementById('scheduleDate');
            if (!scheduleDateInput) return;
            
            fetchHolidaysAndAvailableDays().then(({ holidays, availableDays, disabledDates }) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const maxDate = new Date(today);
                maxDate.setDate(today.getDate() + availableDays);
                const allDisabledDates = [...holidays, ...disabledDates];
                
                scheduleDatePicker = flatpickr(scheduleDateInput, {
                    dateFormat: 'Y-m-d',
                    minDate: today,
                    maxDate: maxDate,
                    disable: allDisabledDates,
                    defaultDate: selectedDate || null,
                    allowInput: false,
                    clickOpens: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (holidays.includes(dateStr) || disabledDates.includes(dateStr)) {
                            Swal.fire({
                                icon: 'warning',
                                title: holidays.includes(dateStr) ? 'Holiday Selected' : 'Date Fully Booked',
                                text: holidays.includes(dateStr) ? 'The selected date is a holiday.' : 'This date has reached the maximum bookings.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            instance.clear();
                        }
                    }
                });
            });
        }
        
        // Save Schedule
        window.saveSchedule = async function() {
            const bookingId = parseInt(document.getElementById('scheduleBookingId').value);
            const scheduleDate = document.getElementById('scheduleDate').value;
            const scheduleNotes = document.getElementById('scheduleNotes').value.trim();
            
            if (!scheduleDate) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a date'
                });
                return;
            }
            
            const scheduleModal = document.getElementById('scheduleModal');
            const originalStatus = scheduleModal?.dataset.originalStatus || '';
            const originalDate = scheduleModal?.dataset.originalDate || '';
            const isReschedule = ['schedul_accepted', 'reschedul_accepted'].includes(originalStatus);
            const dateChanged = originalDate && scheduleDate !== originalDate;
            
            if (isReschedule && dateChanged) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Confirm Date Change',
                    html: `<p>This will count as a new attempt. Are you sure?</p>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Change Date',
                    cancelButtonText: 'Cancel'
                });
                
                if (!result.isConfirmed) return;
            }
            
            try {
                const response = await fetch('{{ route("frontend.setup.update-booking") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        scheduled_date: scheduleDate,
                        notes: scheduleNotes || null,
                        booking_notes: scheduleNotes || null,
                        update_notes_only: isReschedule && !dateChanged
                    })
                });
                
                const data = await response.json();
                
                if (data.success || response.ok) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                    modal.hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Schedule updated successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update schedule.'
                    });
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            }
        };

        // Initiate Payment
        window.initiatePayment = async function(bookingId) {
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
                        // Load Cashfree SDK
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
        };

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
                        // User cancelled or payment failed - don't reload, let them try again
                        if (result.error.message && !result.error.message.includes('cancelled')) {
                            console.warn('Payment error:', result.error.message);
                        }
                    } else {
                        // Payment completed successfully (no error in result)
                        console.log('Payment completed - reloading page to check status');
                        // Wait a moment for Cashfree to process, then reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }).catch(function(error) {
                    console.error('Cashfree checkout exception:', error);
                    // This catch is for actual exceptions, not user cancellation
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
        
        // Cleanup on modal close
        document.addEventListener('DOMContentLoaded', function() {
            const scheduleModal = document.getElementById('scheduleModal');
            if (scheduleModal) {
                scheduleModal.addEventListener('hidden.bs.modal', function() {
                    if (scheduleDatePicker) {
                        scheduleDatePicker.destroy();
                        scheduleDatePicker = null;
                    }
                    const scheduleDateInput = document.getElementById('scheduleDate');
                    if (scheduleDateInput) {
                        scheduleDateInput.value = '';
                    }
                });
            }
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

