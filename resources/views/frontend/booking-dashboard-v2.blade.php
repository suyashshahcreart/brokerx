@extends('frontend.layouts.base', ['title' => 'Booking Dashboard - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/setup_page.css') }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/profile_page.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/booking_dashboard_v2.css') }}">
@endsection

@section('content')
    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="container mt-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i>
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>
                <strong>Success:</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
    
    <!-- Booking Dashboard Hero -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <p class="text-uppercase fw-bold small mb-2 opacity-75">Dashboard</p>
                    <h1 class="display-5 fw-bold mb-3">Booking Dashboard</h1>
                    <p class="lead mb-0 opacity-75">Manage your bookings, track status, and view analytics in one place.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('frontend.setup') }}" class="btn btn-light fw-semibold">
                        <i class="fa-solid fa-plus me-2"></i>New Booking
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="page bg-setup-form py-5">
        <div class="container">
            
            <!-- Analytics & KPIs Section -->
            <div class="row g-4 mb-5">
                <!-- Total Bookings KPI -->
                <div class="col-md-6 col-lg-3">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-primary">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value">{{ $totalBookings }}</div>
                            <div class="kpi-label">Total Bookings</div>
                        </div>
                    </div>
                </div>
                
                <!-- Paid Bookings KPI -->
                <div class="col-md-6 col-lg-3">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-success">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value">{{ $paidBookings }}</div>
                            <div class="kpi-label">Paid Bookings</div>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Bookings KPI -->
                <div class="col-md-6 col-lg-3">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-warning">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value">{{ $pendingBookings }}</div>
                            <div class="kpi-label">Pending Payment</div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Revenue KPI -->
                <div class="col-md-6 col-lg-3">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-info">
                            <i class="fa-solid fa-indian-rupee-sign"></i>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value">₹{{ number_format($totalAmount, 0) }}</div>
                            <div class="kpi-label">Total Revenue</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Breakdown Section -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-body p-4">
                            <h5 class="mb-4" style="font-family: 'Montagu Slab', serif; font-weight: 700;">
                                <i class="fa-solid fa-chart-pie me-2 text-primary"></i>Booking Status Overview
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <div class="status-stat-card status-success">
                                        <div class="status-stat-icon">
                                            <i class="fa-solid fa-calendar-check"></i>
                                        </div>
                                        <div class="status-stat-value">{{ $statusBreakdown['scheduled'] }}</div>
                                        <div class="status-stat-label">Scheduled</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="status-stat-card status-warning">
                                        <div class="status-stat-icon">
                                            <i class="fa-solid fa-hourglass-half"></i>
                                        </div>
                                        <div class="status-stat-value">{{ $statusBreakdown['pending'] }}</div>
                                        <div class="status-stat-label">Pending Approval</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="status-stat-card status-danger">
                                        <div class="status-stat-icon">
                                            <i class="fa-solid fa-times-circle"></i>
                                        </div>
                                        <div class="status-stat-value">{{ $statusBreakdown['declined'] }}</div>
                                        <div class="status-stat-label">Declined</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="status-stat-card status-info">
                                        <div class="status-stat-icon">
                                            <i class="fa-solid fa-calendar-plus"></i>
                                        </div>
                                        <div class="status-stat-value">{{ $statusBreakdown['not_scheduled'] }}</div>
                                        <div class="status-stat-label">Not Scheduled</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Grid -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0" style="font-family: 'Montagu Slab', serif; font-weight: 700;">
                            <i class="fa-solid fa-list me-2 text-primary"></i>My Bookings
                        </h3>
                        <span class="badge bg-primary">{{ $totalBookings }} {{ Str::plural('booking', $totalBookings) }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add New Booking Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="booking-card add-booking-card" onclick="window.location.href='{{ route('frontend.setup') }}'">
                        <div class="booking-card-body text-center p-4">
                            <div class="add-booking-icon-wrapper mb-3">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <h5 class="mb-2 fw-bold">Add New Booking</h5>
                            <p class="text-muted mb-0 small">Start a fresh property booking</p>
                        </div>
                    </div>
                </div>
                
                @forelse($bookings ?? [] as $booking)
                    @php
                        // Calculate price using dynamic settings
                        $baseArea = (int) ($priceSettings['base_area'] ?? 1500);
                        $basePrice = (int) ($priceSettings['base_price'] ?? 599);
                        $extraArea = (int) ($priceSettings['extra_area'] ?? 500);
                        $extraAreaPrice = (int) ($priceSettings['extra_area_price'] ?? 200);
                        $areaValue = $booking->area ?? 0;
                        $price = $booking->price ?? $basePrice;
                        
                        if ($areaValue > $baseArea) {
                            $extra = $areaValue - $baseArea;
                            $blocks = ceil($extra / $extraArea);
                            $price = $basePrice + ($blocks * $extraAreaPrice);
                        }
                        
                        // Get property details
                        $propertyType = '';
                        $propertyDetails = '';
                        
                        if ($booking->propertyType) {
                            $mainType = $booking->propertyType->name ?? '';
                            
                            if ($mainType === 'Residential') {
                                $propertyType = $booking->propertySubType->name ?? 'Residential';
                                $parts = [];
                                if ($booking->bhk) $parts[] = $booking->bhk->name;
                                if ($booking->propertySubType) $parts[] = $booking->propertySubType->name;
                                if ($booking->area) $parts[] = $booking->area . ' sq. ft.';
                                $propertyDetails = implode(' - ', $parts);
                            } elseif ($mainType === 'Commercial') {
                                $propertyType = $booking->propertySubType->name ?? 'Commercial';
                                $parts = [];
                                if ($booking->propertySubType) $parts[] = $booking->propertySubType->name;
                                if ($booking->area) $parts[] = $booking->area . ' sq. ft.';
                                $propertyDetails = implode(' - ', $parts);
                            } else {
                                $propertyType = $booking->propertySubType->name ?? 'Other';
                                $propertyDetails = $booking->area ? $booking->area . ' sq. ft.' : '';
                            }
                        }
                        
                        // Format dates
                        $createdDate = $booking->created_at ? $booking->created_at->format('F j, Y') : 'Date not available';
                        $scheduledDate = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') : null;
                        
                        // Status
                        $status = $booking->status ?? 'pending';
                        $statusClass = match($status) {
                            'schedul_pending' => 'warning',
                            'schedul_accepted' => 'success',
                            'schedul_decline' => 'danger',
                            'reschedul_pending' => 'warning',
                            'reschedul_accepted' => 'success',
                            'reschedul_decline' => 'danger',
                            'reschedul_blocked' => 'dark',
                            'tour_live' => 'success',
                            'completed', 'confirmed' => 'info',
                            default => 'warning'
                        };
                        $statusText = match($status) {
                            'schedul_pending' => 'Pending Approval',
                            'schedul_accepted' => 'Scheduled',
                            'schedul_decline' => 'Declined',
                            'reschedul_pending' => 'Reschedule Pending',
                            'reschedul_accepted' => 'Rescheduled',
                            'reschedul_decline' => 'Reschedule Declined',
                            'reschedul_blocked' => 'Blocked',
                            default => ucfirst(str_replace('_', ' ', $status))
                        };
                        
                        // Payment status
                        $paymentStatus = $booking->payment_status ?? 'pending';
                        $isPaymentPaid = $paymentStatus === 'paid';
                        
                        // Check if schedule was declined - clear scheduled date display
                        $showScheduledDate = $scheduledDate && !in_array($status, ['schedul_decline', 'reschedul_decline', 'reschedul_blocked']);
                        
                        // Check if blocked
                        $isBlocked = $status === 'reschedul_blocked';
                        
                        // Get attempt count and max attempts
                        $attemptCount = 0;
                        if ($isBlocked || in_array($status, ['schedul_pending', 'schedul_accepted', 'schedul_decline', 'reschedul_pending', 'reschedul_accepted', 'reschedul_decline'])) {
                            // Count accepted attempts
                            $acceptedAttempts = \App\Models\BookingHistory::where('booking_id', $booking->id)
                                ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                                ->count();
                            
                            // If status is pending, add 1 for the current pending attempt
                            if (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                                $attemptCount = $acceptedAttempts + 1;
                            } else {
                                // For accepted or declined, use the accepted count
                                $attemptCount = $acceptedAttempts;
                            }
                        }
                        
                        // User info
                        $userName = $booking->user ? trim(($booking->user->firstname ?? '') . ' ' . ($booking->user->lastname ?? '')) : 'N/A';
                        $userPhone = $booking->user->mobile ?? 'N/A';
                        $ownerType = $booking->owner_type ?? 'Owner';
                        
                        // Address - only show fields that have values
                        $addressParts = [];
                        if (!empty($booking->house_no)) $addressParts[] = $booking->house_no;
                        if (!empty($booking->building)) $addressParts[] = $booking->building;
                        if (!empty($booking->society_name)) $addressParts[] = $booking->society_name;
                        if (!empty($booking->address_area)) $addressParts[] = $booking->address_area;
                        if (!empty($booking->landmark)) $addressParts[] = $booking->landmark;
                        if (!empty($booking->full_address)) $addressParts[] = $booking->full_address;
                        
                        $addressDisplay = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                    @endphp
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100" style="border-radius:12px; border: 1px solid #e0e0e0;">
                            <div class="card-body">
                                <!-- Card Header with Combined Status -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">{{ $propertyType ?: 'Property' }}</h5>
                                    <div class="d-flex gap-1 flex-wrap justify-content-end">
                                        @php
                                            // Create combined status text
                                            $combinedStatus = '';
                                            if ($isPaymentPaid) {
                                                if ($isBlocked) {
                                                    $combinedStatus = 'Paid Blocked';
                                                } elseif (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                                                    $combinedStatus = 'Paid Pending';
                                                } elseif (in_array($status, ['schedul_accepted', 'reschedul_accepted'])) {
                                                    $combinedStatus = 'Paid Scheduled';
                                                } elseif (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                                                    $combinedStatus = 'Paid Declined';
                                                } elseif ($showScheduledDate) {
                                                    $combinedStatus = 'Paid Scheduled';
                                                } else {
                                                    $combinedStatus = 'Paid Not Scheduled';
                                                }
                                            } else {
                                                $combinedStatus = 'Payment Pending';
                                            }
                                        @endphp
                                        <span class="badge {{ $isPaymentPaid ? 'bg-success' : 'bg-warning' }}">{{ $combinedStatus }}</span>
                                    </div>
                                </div>
                                
                                <!-- Property Details -->
                                @if($propertyDetails)
                                    <div class="mb-2">
                                        <small class="text-muted"><i class="fa-solid fa-info-circle me-1"></i>{{ $propertyDetails }}</small>
                                    </div>
                                @endif
                                
                                <!-- Address -->
                                <div class="mb-2">
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><strong>Address: </strong>{{ $addressDisplay }}</small>
                                </div>
                                
                                <!-- Created Date -->
                                <div class="mb-2">
                                    <small class="text-muted"><i class="fa-solid fa-clock me-1"></i>Created: {{ $createdDate }}</small>
                                </div>
                                
                                <!-- Price -->
                                <div class="mb-3 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><strong>Price:</strong></small>
                                        <strong class="text-primary" style="font-size: 1.1em;">₹{{ number_format($price, 0, '.', ',') }}</strong>
                                    </div>
                                </div>
                                
                                <!-- Owner Info -->
                                <div class="pt-2 border-top mb-3">
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted"><i class="fa-solid fa-user me-1"></i><strong>{{ $ownerType }} : </strong> {{ $userName }} - <i class="fa-solid fa-phone me-1"></i>{{ $userPhone }}</small>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    {{-- View button - always shown --}}
                                    <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm-r btn-primary flex-fill">
                                        <i class="fa-solid fa-eye me-1"></i>View
                                    </a>
                                    
                                    @if($isPaymentPaid)
                                        {{-- Payment is paid - show schedule button based on status --}}
                                        @if($isBlocked)
                                            {{-- Blocked - show contact admin button --}}
                                            <button class="btn btn-sm-r btn-dark flex-fill" disabled>
                                                <i class="fa-solid fa-ban me-1"></i>Contact Admin
                                            </button>
                                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                            {{-- Waiting for admin approval - disabled button --}}
                                            <button class="btn btn-sm-r btn-secondary flex-fill" disabled>
                                                <i class="fa-solid fa-clock me-1"></i>Awaiting Approval
                                            </button>
                                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                            {{-- Schedule declined - allow new request if not at limit --}}
                                            @if($attemptCount < $maxAttempts)
                                                <button class="btn btn-sm-r btn-primary flex-fill" onclick="openScheduleModal({{ $booking->id }})">
                                                    <i class="fa-solid fa-calendar-plus me-1"></i>Request Again ({{ $maxAttempts - $attemptCount }} left)
                                                </button>
                                            @else
                                                <button class="btn btn-sm-r btn-dark flex-fill" disabled>
                                                    <i class="fa-solid fa-ban me-1"></i>No Attempts Left
                                                </button>
                                            @endif
                                        @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted', 'confirmed']) && $showScheduledDate)
                                            {{-- Schedule approved - can reschedule --}}
                                            <button class="btn btn-sm-r btn-outline-primary flex-fill reschedule-btn" 
                                                    onclick="openScheduleModal({{ $booking->id }}, this)"
                                                    data-status="{{ $status }}"
                                                    data-booking-date="{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '' }}"
                                                    data-attempt-count="{{ $attemptCount }}"
                                                    data-max-attempts="{{ $maxAttempts }}">
                                                <i class="fa-solid fa-calendar-edit me-1"></i>Reschedule
                                            </button>
                                        @elseif(!$scheduledDate)
                                            {{-- No schedule yet --}}
                                            <button class="btn btn-sm-r btn-primary flex-fill" onclick="openScheduleModal({{ $booking->id }})">
                                                <i class="fa-solid fa-calendar-plus me-1"></i>Schedule
                                            </button>
                                        @else
                                            {{-- Has schedule --}}
                                            <button class="btn btn-sm-r btn-outline-primary flex-fill reschedule-btn" 
                                                    onclick="openScheduleModal({{ $booking->id }}, this)"
                                                    data-status="{{ $status }}"
                                                    data-booking-date="{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '' }}"
                                                    data-attempt-count="{{ $attemptCount }}"
                                                    data-max-attempts="{{ $maxAttempts }}">
                                                <i class="fa-solid fa-calendar-edit me-1"></i>Reschedule
                                            </button>
                                        @endif
                                    @else
                                        {{-- Payment not paid - show Edit and Pay buttons --}}
                                        <button class="btn btn-sm-r btn-primary flex-fill" onclick="openEditModal({{ $booking->id }})">
                                            <i class="fa-solid fa-edit me-1"></i>Edit
                                        </button>
                                        
                                        {{-- Pay button - only show if property and address data are complete --}}
                                        @if($booking->isReadyForPayment())
                                            <button class="btn btn-sm-r btn-primary flex-fill" onclick="initiatePayment({{ $booking->id }})">
                                                <i class="fa-solid fa-credit-card me-1"></i>Pay
                                            </button>
                                        @endif
                                    @endif
                                </div>
                                
                                <!-- Notifications Section -->
                                <div class="mt-3">
                                    @if(!$isPaymentPaid)
                                        @if(!$booking->isReadyForPayment())
                                            @php
                                                $hasPropertyData = $booking->hasCompletePropertyData();
                                                $hasAddressData = $booking->hasCompleteAddressData();
                                                
                                                if (!$hasPropertyData && !$hasAddressData) {
                                                    $alertType = 'warning';
                                                    $alertIcon = 'fa-exclamation-triangle';
                                                    $alertTitle = 'Complete Your Booking';
                                                    $alertMessage = 'Please complete both Property Details and Address Information to proceed with payment. Click "Edit" to update your booking.';
                                                } elseif (!$hasPropertyData) {
                                                    $alertType = 'warning';
                                                    $alertIcon = 'fa-exclamation-triangle';
                                                    $alertTitle = 'Property Details Required';
                                                    $alertMessage = 'Please complete your Property Details (Property Type, Size, Area, etc.) to proceed with payment. Click "Edit" to update your booking.';
                                                } elseif (!$hasAddressData) {
                                                    $alertType = 'warning';
                                                    $alertIcon = 'fa-exclamation-triangle';
                                                    $alertTitle = 'Address Information Required';
                                                    $alertMessage = 'Please complete your Address Information (House Number, Building Name, Pincode, Full Address) to proceed with payment. Click "Edit" to update your booking.';
                                                } else {
                                                    $alertType = 'info';
                                                    $alertIcon = 'fa-info-circle';
                                                    $alertTitle = 'Ready for Payment';
                                                    $alertMessage = 'Your booking details are complete. You can now proceed with payment.';
                                                }
                                            @endphp
                                    
                                            <div class="alert alert-{{ $alertType }} py-2 mb-0" role="alert" style="border-left: 4px solid {{ $alertType === 'warning' ? '#ffc107' : '#0dcaf0' }}; background-color: {{ $alertType === 'warning' ? '#fff3cd' : '#d1ecf1' }}; font-size: 0.75rem;">
                                                <small class="d-block">
                                                    <i class="fa-solid {{ $alertIcon }} me-1"></i><strong>{{ $alertTitle }}:</strong> {{ $alertMessage }}
                                                </small>
                                            </div>
                                        @endif
                                    @else
                                        @if($isBlocked)
                                            @php
                                                $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                            @endphp
                                            <div class="alert alert-danger py-2 mb-0" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da; font-size: 0.75rem;">
                                                <small class="d-block mb-1"><i class="fa-solid fa-ban me-1"></i><strong>Scheduling Blocked</strong></small>
                                                <small class="text-muted d-block mb-1">Maximum attempts reached ({{ $attemptCount }}/{{ $maxAttempts }})</small>
                                                <small class="d-block">
                                                    <i class="fa-solid fa-info-circle me-1"></i>
                                                    {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                                </small>
                                            </div>
                                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                            <div class="alert alert-warning py-2 mb-0" role="alert" style="border-left: 4px solid #ffc107; background-color: #fff3cd; font-size: 0.75rem;">
                                                <small class="d-block mb-1"><i class="fa-solid fa-clock me-1"></i><strong>Status:</strong> Awaiting Admin Approval</small>
                                                @if($scheduledDate)
                                                    <small class="text-muted d-block mb-1"><i class="fa-solid fa-calendar me-1"></i><strong>Requested Date:</strong> {{ $scheduledDate }}</small>
                                                @endif
                                                <small class="text-muted d-block"><i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}</small>
                                            </div>
                                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                            @php
                                                $declineReason = null;
                                                $latestHistory = \App\Models\BookingHistory::where('booking_id', $booking->id)
                                                    ->whereIn('to_status', ['schedul_decline', 'reschedul_decline'])
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();
                                                if ($latestHistory && isset($latestHistory->metadata['reason'])) {
                                                    $declineReason = $latestHistory->metadata['reason'];
                                                }
                                            @endphp
                                            <div class="alert alert-danger py-2 mb-0" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da; font-size: 0.75rem;">
                                                <small class="d-block mb-1"><i class="fa-solid fa-times-circle me-1"></i><strong>Status:</strong> Schedule Declined</small>
                                                <small class="text-muted d-block mb-1"><i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}</small>
                                                @if($declineReason)
                                                    <small class="d-block mt-2">
                                                        <i class="fa-solid fa-exclamation-triangle me-1"></i><strong>Reason:</strong> {{ $declineReason }}
                                                    </small>
                                                @endif
                                            </div>
                                            @if($attemptCount >= $maxAttempts)
                                                @php
                                                    $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                                @endphp
                                                <div class="alert alert-danger py-2 mb-0 mt-2" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da; font-size: 0.75rem;">
                                                    <small class="d-block">
                                                        <i class="fa-solid fa-ban me-1"></i>
                                                        {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                                    </small>
                                                </div>
                                            @else
                                                <div class="alert alert-warning py-2 mb-0 mt-2" role="alert" style="border-left: 4px solid #ffc107; background-color: #fff3cd; font-size: 0.75rem;">
                                                    <small class="d-block">
                                                        <i class="fa-solid fa-info-circle me-1"></i>You can request again ({{ $maxAttempts - $attemptCount }} {{ Str::plural('attempt', $maxAttempts - $attemptCount) }} left)
                                                    </small>
                                                </div>
                                            @endif
                                        @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted']) && $showScheduledDate)
                                            <div class="alert alert-info py-2 mb-0" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #d1ecf1; font-size: 0.75rem;">
                                                <small class="d-block mb-1">
                                                    <i class="fa-solid fa-calendar-check me-1"></i><strong>Scheduled:</strong> {{ $scheduledDate }} at {{ $booking->scheduled_time ?? 'TBD' }}
                                                </small>
                                                <small class="d-block">
                                                    <i class="fa-solid fa-clock me-1"></i><strong>Photographer Assignment in Progress:</strong> Your booking date has been accepted! In a short time, a photographer will be assigned and a specific time will be set for when the photographer visits your property to start your property tour photography. Please wait for the admin to assign the photographer and schedule the visit time.
                                                </small>
                                            </div>
                                        @elseif($showScheduledDate)
                                            <div class="alert alert-success py-2 mb-0" role="alert" style="border-left: 4px solid #198754; background-color: #d1e7dd; font-size: 0.75rem;">
                                                <small class="d-block">
                                                    <i class="fa-solid fa-calendar-check me-1"></i><strong>Scheduled:</strong> {{ $scheduledDate }} at {{ $booking->scheduled_time ?? 'TBD' }}
                                                </small>
                                            </div>
                                        @else
                                            <div class="alert alert-info py-2 mb-0" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #d1ecf1; font-size: 0.75rem;">
                                                <small class="d-block">
                                                    <i class="fa-solid fa-calendar me-1"></i><strong>Status:</strong> Not Scheduled - Please schedule your appointment
                                                </small>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px;">
                            <div class="card-body">
                                <i class="fa-solid fa-calendar-xmark mb-3" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mb-0">No bookings found. Create your first booking!</p>
                            </div>
                        </div>
                    </div>
                @endforelse
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

    <!-- Edit Booking Modal -->
    <div class="modal fade" id="editBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-edit me-2"></i>Edit Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editBookingForm">
                        <input type="hidden" id="editBookingId">
                        
                        <!-- Property Details Section -->
                        <div class="card mb-3" style="border-radius:12px;">
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
                        <div class="card mb-3" style="border-radius:12px;">
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
                            <div class="card mb-3" id="editPaymentRequiredCard" style="border-radius:12px; display:none;">
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
                                    <button type="button" class="btn btn-primary w-100" id="editMakePaymentBtn" onclick="initiatePaymentFromEdit()" disabled>
                                        <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                                    </button>
                                    <small class="text-muted d-block text-center mt-2">Secure payment via Cashfree</small>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Details Section (only if payment is done) -->
                        <div class="card mb-3" id="editScheduleDetailsCard" style="border-radius:12px;">
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

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                    <h5 id="successTitle" class="mb-2">Success!</h5>
                    <p id="successMessage" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.reload()">OK</button>
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
    
    <script type="text/javascript">
        // Booking Management System - Full Implementation
        let bookings = @json($bookings ?? []);
        
        // Convert Laravel bookings to frontend format for modal operations
        if (bookings && bookings.length > 0) {
            bookings = bookings.map(booking => {
                // Get attempt count and max attempts from booking data
                let attemptCount = booking.attempt_count || 0;
                let maxAttempts = booking.max_attempts || {{ $maxAttempts }};
                const status = booking.status || 'pending';
                
                // Calculate attempt count if not provided
                if (!booking.attempt_count && ['schedul_pending', 'schedul_accepted', 'schedul_decline', 'reschedul_pending', 'reschedul_accepted', 'reschedul_decline', 'reschedul_blocked'].includes(status)) {
                    // Count accepted attempts from history
                    const acceptedAttempts = (booking.history || []).filter(h => 
                        h && ['schedul_accepted', 'reschedul_accepted'].includes(h.to_status)
                    ).length;
                    
                    if (['schedul_pending', 'reschedul_pending'].includes(status)) {
                        attemptCount = acceptedAttempts + 1;
                    } else {
                        attemptCount = acceptedAttempts;
                    }
                }
                
                return {
                    id: booking.id,
                    status: status,
                    booking_date: booking.booking_date || null,
                    scheduled_date: booking.scheduled_date || booking.booking_date || null,
                    scheduledTime: booking.scheduled_time || null,
                    scheduleNotes: booking.schedule_notes || null,
                    booking_notes: booking.booking_notes || null,
                    notes: booking.notes || null,
                    attemptCount: attemptCount,
                    maxAttempts: maxAttempts
                };
            });
        }

        // Flatpickr instance for schedule date
        let scheduleDatePicker = null;
        
        // Open schedule modal - Make it globally accessible
        window.openScheduleModal = async function(bookingId, triggerButton = null) {
            const booking = bookings.find(b => b.id === bookingId);
            if (!booking) {
                window.location.href = "{{ route('frontend.booking.show', ':id') }}".replace(':id', bookingId);
                return;
            }

            document.getElementById('scheduleBookingId').value = bookingId;
            const existingDate = booking.scheduled_date || booking.booking_date || '';
            document.getElementById('scheduleNotes').value = booking.booking_notes || booking.notes || '';

            // Get attempt data from button that triggered the modal (if reschedule)
            const blockedDiv = document.getElementById('blockedWarning');
            const warningDiv = document.getElementById('rescheduleWarning');
            const scheduleModal = document.getElementById('scheduleModal');
            const scheduleDateInput = document.getElementById('scheduleDate');
            const scheduleNotesInput = document.getElementById('scheduleNotes');
            const scheduleDateIcon = document.getElementById('scheduleDateIcon');
            const saveScheduleBtn = document.getElementById('saveScheduleBtn');
            
            let status = booking.status || 'pending';
            let oldDate = booking.booking_date || booking.scheduled_date || null;
            let attemptCount = booking.attemptCount || 0;
            let maxAttempts = booking.maxAttempts || {{ $maxAttempts }};
            
            // Get data from button if available (for reschedule buttons)
            if (triggerButton && triggerButton.dataset) {
                status = triggerButton.dataset.status || status;
                oldDate = triggerButton.dataset.bookingDate || oldDate;
                attemptCount = parseInt(triggerButton.dataset.attemptCount || attemptCount);
                maxAttempts = parseInt(triggerButton.dataset.maxAttempts || maxAttempts);
            }
            
            // Check if blocked (max attempts reached or status is reschedul_blocked)
            const isBlocked = status === 'reschedul_blocked' || attemptCount >= maxAttempts;
            
            // Store original date and status for comparison
            if (scheduleModal) {
                scheduleModal.dataset.originalDate = oldDate || '';
                scheduleModal.dataset.originalStatus = status;
                scheduleModal.dataset.originalNotes = booking.booking_notes || booking.notes || '';
            }
            
            // Show blocked message if max attempts reached
            if (isBlocked) {
                const adminEmail = '{{ \App\Models\Setting::where("name", "support_email")->value("value") ?? "support@proppik.in" }}';
                const adminPhone = '{{ \App\Models\Setting::where("name", "support_phone")->value("value") ?? "+91-XXXXXXXXXX" }}';
                
                const blockedHTML = `
                    <div class="alert alert-danger py-3 mb-3" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da;">
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-ban me-2 mt-1" style="color: #dc3545; font-size: 1.1rem;"></i>
                            <div>
                                <strong class="d-block mb-2" style="color: #721c24;">Maximum Attempts Reached</strong>
                                <p class="mb-2 small" style="color: #721c24; line-height: 1.6;">
                                    You have lost all your attempts (${attemptCount}/${maxAttempts}). You have now lost this booking.
                                </p>
                                <p class="mb-2 small" style="color: #721c24; line-height: 1.6;">
                                    <strong>Please create a new booking to start the process again.</strong>
                                </p>
                                <p class="mb-0 small" style="color: #721c24; line-height: 1.6;">
                                    If you have any doubts or queries, please contact the administration department:<br>
                                    <i class="fa-solid fa-phone me-1"></i><strong>Phone:</strong> ${adminPhone}<br>
                                    <i class="fa-solid fa-envelope me-1"></i><strong>Email:</strong> ${adminEmail}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                
                if (blockedDiv) {
                    blockedDiv.innerHTML = blockedHTML;
                    blockedDiv.style.display = 'block';
                }
                if (warningDiv) warningDiv.style.display = 'none';
                
                // Disable inputs and button
                if (scheduleDateInput) {
                    scheduleDateInput.disabled = true;
                    scheduleDateInput.style.cursor = 'not-allowed';
                    scheduleDateInput.style.opacity = '0.6';
                }
                if (scheduleNotesInput) {
                    scheduleNotesInput.disabled = true;
                    scheduleNotesInput.style.cursor = 'not-allowed';
                    scheduleNotesInput.style.opacity = '0.6';
                }
                if (scheduleDateIcon) {
                    scheduleDateIcon.style.cursor = 'not-allowed';
                    scheduleDateIcon.style.opacity = '0.5';
                }
                if (saveScheduleBtn) {
                    saveScheduleBtn.disabled = true;
                    saveScheduleBtn.style.cursor = 'not-allowed';
                    saveScheduleBtn.style.opacity = '0.6';
                }
            } else {
                // Not blocked - enable inputs and button
                if (blockedDiv) {
                    blockedDiv.innerHTML = '';
                    blockedDiv.style.display = 'none';
                }
                
                if (scheduleDateInput) {
                    scheduleDateInput.disabled = false;
                    scheduleDateInput.style.cursor = '';
                    scheduleDateInput.style.opacity = '';
                }
                if (scheduleNotesInput) {
                    scheduleNotesInput.disabled = false;
                    scheduleNotesInput.style.cursor = '';
                    scheduleNotesInput.style.opacity = '';
                }
                if (scheduleDateIcon) {
                    scheduleDateIcon.style.cursor = 'pointer';
                    scheduleDateIcon.style.opacity = '';
                }
                if (saveScheduleBtn) {
                    saveScheduleBtn.disabled = false;
                    saveScheduleBtn.style.cursor = '';
                    saveScheduleBtn.style.opacity = '';
                }
                
                const isReschedule = ['schedul_accepted', 'reschedul_accepted'].includes(status);

                if (isReschedule && oldDate) {
                    // Format old date
                    const oldDateObj = new Date(oldDate);
                    const formattedOldDate = oldDateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    
                    // Calculate remaining attempts
                    const remainingAttempts = maxAttempts - attemptCount;
                    const isLastAttempt = attemptCount >= maxAttempts - 1;
                    
                    let warningHTML = `
                        <div class="alert alert-warning py-3 mb-3" role="alert" style="border-left: 4px solid #ffc107; background-color: #fff3cd;">
                            <div class="d-flex align-items-start">
                                <i class="fa-solid fa-exclamation-triangle me-2 mt-1" style="color: #ffc107; font-size: 1.1rem;"></i>
                                <div>
                                    <strong class="d-block mb-2" style="color: #856404;">Reschedule Warning</strong>
                                    <p class="mb-2 small" style="color: #856404; line-height: 1.6;">
                                        <strong>Current Accepted Date:</strong> ${formattedOldDate}
                                    </p>
                                    <p class="mb-2 small" style="color: #856404; line-height: 1.6;">
                                        If you change this date, it will count as a new attempt. You have already completed <strong>${attemptCount}</strong> of <strong>${maxAttempts}</strong> attempts.
                                    </p>
                    `;
                    
                    if (isLastAttempt) {
                        warningHTML += `
                                    <p class="mb-0 small" style="color: #721c24; line-height: 1.6; font-weight: 600;">
                                        <i class="fa-solid fa-ban me-1"></i><strong>Warning:</strong> This is your last attempt! If you reschedule and this attempt reaches the maximum limit (${maxAttempts}), you will lose this booking and will need to create a new booking to start the process again.
                                    </p>
                        `;
                    } else {
                        warningHTML += `
                                    <p class="mb-0 small" style="color: #856404; line-height: 1.6;">
                                        <i class="fa-solid fa-info-circle me-1"></i>You have <strong>${remainingAttempts}</strong> attempt(s) remaining. If you reach the maximum limit (${maxAttempts}), you will lose this booking and will need to create a new booking to start the process again.
                                    </p>
                        `;
                    }
                    
                    warningHTML += `
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if (warningDiv) {
                        warningDiv.innerHTML = warningHTML;
                        warningDiv.style.display = 'block';
                    }
                } else {
                    if (warningDiv) {
                        warningDiv.innerHTML = '';
                        warningDiv.style.display = 'none';
                    }
                }
            }

            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
            
            // Initialize Flatpickr after modal is shown (only if not blocked)
            if (!isBlocked) {
                setTimeout(() => {
                    initScheduleDatePicker(existingDate || null);
                }, 100);
            }
        };
        
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
                const disabledDates = data.disabled_dates || [];
                
                return { holidays, availableDays, disabledDates };
            } catch (error) {
                console.error('Error fetching holidays:', error);
                return { holidays: [], availableDays: 30, disabledDates: [] };
            }
        }

        // Initialize Flatpickr with holidays and date restrictions
        function initScheduleDatePicker(selectedDate = null) {
            if (scheduleDatePicker) {
                scheduleDatePicker.destroy();
            }
            
            const scheduleDateInput = document.getElementById('scheduleDate');
            if (!scheduleDateInput) return;
            
            // Fetch holidays and available days, then initialize
            fetchHolidaysAndAvailableDays().then(({ holidays, availableDays, disabledDates }) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Calculate max date (today + available days)
                const maxDate = new Date(today);
                maxDate.setDate(today.getDate() + availableDays);
                
                // Combine holidays and disabled dates
                const allDisabledDates = [...holidays, ...disabledDates];
                
                // Initialize Flatpickr
                scheduleDatePicker = flatpickr(scheduleDateInput, {
                    dateFormat: 'Y-m-d',
                    minDate: today,
                    maxDate: maxDate,
                    disable: allDisabledDates,
                    defaultDate: selectedDate || null,
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
            });
        }
        
        // Save schedule - Make it globally accessible
        window.saveSchedule = async function() {
            const bookingId = parseInt(document.getElementById('scheduleBookingId').value);
            const scheduleDateInput = document.getElementById('scheduleDate');
            const scheduleDate = scheduleDateInput ? scheduleDateInput.value : '';
            const scheduleNotes = document.getElementById('scheduleNotes').value.trim();
            
            // Check if inputs are disabled (blocked state)
            if (scheduleDateInput && scheduleDateInput.disabled) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Action Not Allowed',
                    text: 'You have reached the maximum number of attempts. Please create a new booking.'
                });
                return;
            }

            if (!scheduleDate) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a date'
                });
                return;
            }
            
            // Check if this is a reschedule (status is schedul_accepted or reschedul_accepted)
            const scheduleModal = document.getElementById('scheduleModal');
            const originalStatus = scheduleModal?.dataset.originalStatus || '';
            const originalDate = scheduleModal?.dataset.originalDate || '';
            const isReschedule = ['schedul_accepted', 'reschedul_accepted'].includes(originalStatus);
            const dateChanged = originalDate && scheduleDate !== originalDate;
            
            // If reschedule and date changed, show confirmation
            if (isReschedule && dateChanged) {
                const originalDateFormatted = new Date(originalDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                const newDateFormatted = new Date(scheduleDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Confirm Date Change',
                    html: `
                        <p>You have changed the date from <strong>${originalDateFormatted}</strong> to <strong>${newDateFormatted}</strong>.</p>
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
                // Make AJAX request to update booking in database
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
                        update_notes_only: updateNotesOnly
                    })
                });
                
                const data = await response.json();
                
                if (data.success || response.ok) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                    modal.hide();

                    const isNotesOnly = updateNotesOnly;
                    document.getElementById('successTitle').textContent = isNotesOnly ? 'Notes Updated!' : 'Schedule Updated!';
                    document.getElementById('successMessage').textContent = data.message || (isNotesOnly ? 'Notes updated successfully.' : 'Booking schedule has been updated successfully.');
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    
                    // Reload page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update schedule. Please try again.'
                    });
                }
            } catch (error) {
                console.error('Error updating schedule:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update schedule. Please try again.'
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
        
        // SweetAlert Helper Function
        function showSweetAlert(icon, title, message, html = false) {
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

        // Price Settings from Laravel
        const priceSettings = @json($priceSettings ?? []);

        // Calculate price based on area (matching setup page logic)
        function calculatePrice(area) {
            if (!area || Number(area) <= 0) return 0;
            
            const basePrice = parseFloat(priceSettings.base_price || 0);
            const baseArea = parseFloat(priceSettings.base_area || 0);
            const extraArea = parseFloat(priceSettings.extra_area || 0);
            const extraAreaPrice = parseFloat(priceSettings.extra_area_price || 0);
            
            const areaNum = Number(area);
            
            if (areaNum <= baseArea) {
                return basePrice;
            } else {
                const extra = areaNum - baseArea;
                const extraCharges = Math.ceil(extra / extraArea) * extraAreaPrice;
                return basePrice + extraCharges;
            }
        }

        // Update price in edit modal
        function updateEditPrice() {
            const paymentPriceField = document.getElementById('editPaymentPrice');
            if (!paymentPriceField) return;
            
            let area = 0;
            const tabResVisible = document.getElementById('editTabRes')?.style.display !== 'none';
            const tabComVisible = document.getElementById('editTabCom')?.style.display !== 'none';
            const tabOthVisible = document.getElementById('editTabOth')?.style.display !== 'none';
            
            if (tabResVisible) {
                area = parseFloat(document.getElementById('editResArea')?.value || 0);
            } else if (tabComVisible) {
                area = parseFloat(document.getElementById('editComArea')?.value || 0);
            } else if (tabOthVisible) {
                area = parseFloat(document.getElementById('editOthArea')?.value || 0);
            }
            
            const price = calculatePrice(area);
            paymentPriceField.value = price.toFixed(2);
        }

        // Open Edit Modal - redirect to booking-show page which has the full edit functionality
        window.openEditModal = function(bookingId) {
            window.location.href = "{{ route('frontend.booking.show', ':id') }}".replace(':id', bookingId);
        };
        
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

