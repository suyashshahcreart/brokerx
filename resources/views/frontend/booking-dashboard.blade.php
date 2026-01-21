@extends('frontend.layouts.base', ['title' => 'Booking Dashboard - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- New theme uses global fonts from base layout; keep icon font for existing <i class="fa ..."> usage --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/setup_page.css') }}">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    
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
    
    
    <!-- Booking Dashboard Hero (New Theme style, matches policy pages) -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Dashboard</p>
                    <h1 class="display-5 fw-bold mb-3">Booking Dashboard</h1>
                    <p class="lead mb-0">Manage your bookings, status updates, and details in one place.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page bg-setup-form py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex flex-column flex-md-row gap-2 gap-sm-0 justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">My Bookings</h3>
                        </div>

                    <!-- Bookings List -->
                    <div id="bookingsList" class="row g-4">
                        <!-- Add New Booking Card -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 add-booking-card" onclick="switchToNewBooking()">
                                <div class="card-body add-booking-card-body text-center">
                                    <div class="mb-3">
                                        <div class="add-booking-icon border">
                                            <i class="fa-solid fa-plus text-primary"></i>
                                        </div>
                                    </div>
                                    <h5 class="mb-2 add-booking-title text-primary">Add New Booking</h5>
                                    <p class="mb-0 add-booking-subtitle text-primary">Start a fresh property booking flow.</p>
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
                                $maxAttempts = 3;
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
                                    
                                    $maxAttemptsSetting = \App\Models\Setting::where('name', 'customer_attempt')->first();
                                    $maxAttempts = $maxAttemptsSetting ? (int) $maxAttemptsSetting->value : 3;
                                }
                                
                                // User info
                                $userName = $booking->user ? trim(($booking->user->firstname ?? '') . ' ' . ($booking->user->lastname ?? '')) : 'N/A';
                                $userPhone = $booking->user->mobile ?? 'N/A';
                                $ownerType = $booking->user ? 'Owner' : 'N/A';
                                
                                // Address - only show fields that have values
                                $addressParts = [];
                                if (!empty($booking->house_no)) $addressParts[] = $booking->house_no;
                                if (!empty($booking->building)) $addressParts[] = $booking->building;
                                if (!empty($booking->society_name)) $addressParts[] = $booking->society_name;
                                if (!empty($booking->address_area)) $addressParts[] = $booking->address_area;
                                if (!empty($booking->landmark)) $addressParts[] = $booking->landmark;
                                if (!empty($booking->full_address)) $addressParts[] = $booking->full_address;
                                
                                $addressDisplay = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                                
                                // Get FTP URL for tour_live status using Booking model method
                                $tourFtpUrl = $booking->getTourLiveUrl();
                            @endphp
                            
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100" style="border-radius:12px; border: 1px solid #e0e0e0;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title mb-0">{{ $propertyType ?: 'Property' }}</h5>
                                            <div class="d-flex gap-1 flex-wrap">
                                                {{-- Payment Status Badge --}}
                                                @if($isPaymentPaid)
                                                    <span class="badge bg-success">Paid</span>
                                                @else
                                                    <span class="badge bg-warning">Payment Pending</span>
                                                @endif
                                                
                                                {{-- Schedule Status Badge --}}
                                                @if($status === 'tour_live')
                                                    <span class="badge bg-success">Live</span>
                                                @elseif($isPaymentPaid)
                                                        @if($isBlocked)
                                                            <span class="badge bg-dark">Blocked ({{ $attemptCount }}/{{ $maxAttempts }})</span>
                                                        @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                                            <span class="badge bg-warning">{{ $statusText }} ({{ $attemptCount }}/{{ $maxAttempts }})</span>
                                                        @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted']))
                                                            <span class="badge bg-success">Approved</span>
                                                        @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                                            <span class="badge bg-danger">Declined ({{ $attemptCount }}/{{ $maxAttempts }})</span>
                                                        @elseif($scheduledDate)
                                                            <span class="badge bg-info">{{ $statusText }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not Scheduled</span>
                                                        @endif
                                                    @endif
                                            </div>
                                        </div>
                                        
                                        @if($propertyDetails)
                                            <div class="mb-2">
                                                <small class="text-muted"><i class="fa-solid fa-info-circle me-1"></i>{{ $propertyDetails }}</small>
                                            </div>
                                        @endif
                                        
                                        <div class="mb-2">
                                            <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><strong>Address: </strong>  {{ $addressDisplay }}</small>
                                            <div class="ms-4 mt-1">
                                                <small class="text-muted d-block"></small>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="mb-2">
                                            <small class="text-muted"><i class="fa-solid fa-clock me-1"></i>Created: {{ $createdDate }}</small>
                                        </div>
                                        
                                        <div class="mb-3 pt-2 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><strong>Price:</strong></small>
                                                <strong class="text-primary" style="font-size: 1.1em;">₹{{ number_format($price, 0, '.', ',') }}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="pt-2 border-top mb-3">
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted"><i class="fa-solid fa-user me-1"></i><strong>{{ $ownerType }} : </strong> {{ $userName }} - <i class="fa-solid fa-phone me-1"></i>{{ $userPhone }}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            {{-- View button - always shown --}}
                                            <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm-r btn-primary flex-fill">
                                                <i class="fa-solid fa-eye me-1"></i>View
                                            </a>
                                            
                                            @if($status === 'tour_live')
                                                {{-- Tour is live - show live tour button (regardless of payment status) --}}
                                                <a href="{{ $tourFtpUrl }}" target="_blank" class="btn btn-sm-r btn-success flex-fill">
                                                    <i class="fa-solid fa-video me-1"></i> View Tour 
                                                </a>
                                            @elseif($isPaymentPaid)
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
                                                {{-- Payment not paid - show View and Edit buttons always --}}
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

                                        <div class="mt-2">

                                            {{-- All Notifications Consolidated Here --}}
                                            @if($status === 'tour_live')
                                                <span class="badge bg-success"></span>
                                            @elseif(!$isPaymentPaid)
                                                {{-- Unpaid Booking Notifications --}}
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
                                            
                                                    <div class="alert alert-{{ $alertType }} py-2 my-2" role="alert" style="border-left: 4px solid {{ $alertType === 'warning' ? '#ffc107' : '#0dcaf0' }}; background-color: {{ $alertType === 'warning' ? '#fff3cd' : '#d1ecf1' }};">
                                                        <small class="d-block">
                                                            <i class="fa-solid {{ $alertIcon }} me-1"></i><strong>{{ $alertTitle }}:</strong> {{ $alertMessage }}
                                                        </small>
                                                    </div>
                                                @endif
                                            @else
                                                {{-- Paid Booking Notifications --}}
                                                @if($isBlocked)
                                                    {{-- Scheduling Blocked --}}
                                                    @php
                                                        $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                                    @endphp
                                                    <div class="alert alert-danger mb-2" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da;">
                                                        <small class="d-block mb-1"><i class="fa-solid fa-ban me-1"></i><strong>Scheduling Blocked</strong></small>
                                                        <small class="text-muted d-block mb-2">Maximum attempts reached ({{ $attemptCount }}/{{ $maxAttempts }})</small>
                                                        <small class="d-block">
                                                            <i class="fa-solid fa-info-circle me-1"></i>
                                                            {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                                        </small>
                                                    </div>
                                                @elseif(in_array($status, ['schedul_pending', 'reschedul_pending']))
                                                    {{-- Schedule Pending - Awaiting Admin Approval --}}
                                                    <div class="alert alert-warning py-2 mb-2" role="alert" style="border-left: 4px solid #ffc107; background-color: #fff3cd;">
                                                        <small class="d-block mb-1"><i class="fa-solid fa-clock me-1"></i><strong>Status:</strong> Awaiting Admin Approval</small>
                                                        @if($scheduledDate)
                                                            <small class="text-muted d-block mb-1"><i class="fa-solid fa-calendar me-1"></i><strong>Requested Date:</strong> {{ $scheduledDate }}</small>
                                                        @endif
                                                        <small class="text-muted d-block"><i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}</small>
                                                    </div>
                                                @elseif(in_array($status, ['schedul_decline', 'reschedul_decline']))
                                                    {{-- Schedule Declined --}}
                                                    @php
                                                        // Get decline reason from latest history entry
                                                        $declineReason = null;
                                                        $latestHistory = \App\Models\BookingHistory::where('booking_id', $booking->id)
                                                            ->whereIn('to_status', ['schedul_decline', 'reschedul_decline'])
                                                            ->orderBy('created_at', 'desc')
                                                            ->first();
                                                        if ($latestHistory && isset($latestHistory->metadata['reason'])) {
                                                            $declineReason = $latestHistory->metadata['reason'];
                                                        }
                                                    @endphp
                                                    <div class="alert alert-danger py-2 mb-2" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da;">
                                                        <small class="d-block mb-1"><i class="fa-solid fa-times-circle me-1"></i><strong>Status:</strong> Schedule Declined</small>
                                                        <small class="text-muted d-block mb-1"><i class="fa-solid fa-chart-line me-1"></i>Attempt {{ $attemptCount }} of {{ $maxAttempts }}</small>
                                                        @if($declineReason)
                                                            <small class="d-block mt-2">
                                                                <i class="fa-solid fa-exclamation-triangle me-1"></i><strong>Reason:</strong> {{ $declineReason }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    @if($attemptCount >= $maxAttempts)
                                                        {{-- Show blocked message when at limit --}}
                                                        @php
                                                            $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
                                                        @endphp
                                                        <div class="alert alert-danger py-2 mb-2" role="alert" style="border-left: 4px solid #dc3545; background-color: #f8d7da;">
                                                            <small class="d-block">
                                                                <i class="fa-solid fa-ban me-1"></i>
                                                                {{ $blockedMessage?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning py-2 mb-2" role="alert" style="border-left: 4px solid #ffc107; background-color: #fff3cd;">
                                                            <small class="d-block">
                                                                <i class="fa-solid fa-info-circle me-1"></i>You can request again ({{ $maxAttempts - $attemptCount }} {{ Str::plural('attempt', $maxAttempts - $attemptCount) }} left)
                                                            </small>
                                                        </div>
                                                    @endif
                                                @elseif(in_array($status, ['schedul_accepted', 'reschedul_accepted']) && $showScheduledDate)
                                                    {{-- Schedule Accepted - Photographer Assignment in Progress --}}
                                                    <div class="alert alert-info py-2 mb-2" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #d1ecf1;">
                                                        <small class="d-block mb-1">
                                                            <i class="fa-solid fa-calendar-check me-1"></i><strong>Scheduled:</strong> {{ $scheduledDate }} at {{ $booking->scheduled_time ?? 'TBD' }}
                                                        </small>
                                                        <small class="d-block">
                                                            <i class="fa-solid fa-clock me-1"></i><strong>Photographer Assignment in Progress:</strong> Your booking date has been accepted! In a short time, a photographer will be assigned and a specific time will be set for when the photographer visits your property to start your property tour photography. Please wait for the admin to assign the photographer and schedule the visit time.
                                                        </small>
                                                    </div>
                                                    
                                                    {{-- Admin notes only visible to admin users (not shown to customers) --}}
                                                    @php
                                                        $isAdmin = Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'));
                                                        // Get admin notes from latest history entry
                                                        $adminNotes = null;
                                                        if ($isAdmin) {
                                                            $latestHistory = \App\Models\BookingHistory::where('booking_id', $booking->id)
                                                                ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                                                                ->orderBy('created_at', 'desc')
                                                                ->first();
                                                            if ($latestHistory && isset($latestHistory->metadata['admin_notes'])) {
                                                                $adminNotes = $latestHistory->metadata['admin_notes'];
                                                            }
                                                        }
                                                    @endphp
                                                    @if($adminNotes && $isAdmin)
                                                        <div class="alert alert-secondary py-2 mb-2" role="alert" style="border-left: 4px solid #6c757d; background-color: #e2e3e5;">
                                                            <small class="d-block">
                                                                <i class="fa-solid fa-user-shield me-1"></i><strong>Admin Notes:</strong> {{ $adminNotes }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                @elseif($showScheduledDate)
                                                    {{-- Scheduled (with date but not in accepted status) --}}
                                                    <div class="alert alert-success py-2 mb-2" role="alert" style="border-left: 4px solid #198754; background-color: #d1e7dd;">
                                                        <small class="d-block">
                                                            <i class="fa-solid fa-calendar-check me-1"></i><strong>Scheduled:</strong> {{ $scheduledDate }} at {{ $booking->scheduled_time ?? 'TBD' }}
                                                        </small>
                                                    </div>
                                                @else
                                                    {{-- Not Scheduled --}}
                                                    <div class="alert alert-info py-2 mb-2" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #d1ecf1;">
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
                                <div class="card p-4" style="border-radius:12px;">
                                    <div class="text-center py-5">
                                        <i class="fa-solid fa-calendar-xmark" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                        <p class="text-muted mb-0">No bookings found. Create your first booking!</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Date Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Booking Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Blocked Message (will be populated dynamically) --}}
                    <div id="blockedWarning" style="display: none;"></div>
                    
                    {{-- Reschedule Warning Message (will be populated dynamically) --}}
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

    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-eye me-2"></i>View Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewBookingContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading booking details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashfree Payment Modal -->
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

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #28a745;">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h5 class="mb-3" id="successTitle">Success!</h5>
                    <p class="muted-small mb-3" id="successMessage">Operation completed successfully.</p>
                    <h5 class="mb-3" id="successTitle">Success!</h5>
                    <p class="muted-small mb-3" id="successMessage">Operation completed successfully.</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery-migrate-3.5.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/smooth-scroll.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/wow.js') }}"></script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Base API URL from Laravel environment
        const API_BASE_URL = '{{ url("/api") }}';
        
        // SweetAlert Helper Function (same as setup page)
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
                
                // Enhanced styling for error messages
                if (icon === 'error') {
                    config.confirmButtonColor = '#dc3545';
                    config.iconColor = '#dc3545';
                    config.width = '500px';
                    if (html) {
                        // Format error message with better styling
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
                // Fallback to regular alert if SweetAlert not available
                const cleanMessage = message.replace(/<br>/g, '\n').replace(/• /g, '- ');
                alert(cleanMessage);
                return Promise.resolve();
            }
        }

        // Booking Management System
        let bookings = @json($bookings ?? []);
        
        // Convert Laravel bookings to frontend format for modal operations
        if (bookings && bookings.length > 0) {
            bookings = bookings.map(booking => {
                // Get attempt count and max attempts from booking data
                // These should be calculated on the backend and passed in the booking object
                let attemptCount = booking.attempt_count || 0;
                let maxAttempts = booking.max_attempts || 3;
                const status = booking.status || 'pending';
                
                // If attempt_count is not provided, calculate it from status
                if (!booking.attempt_count && ['schedul_pending', 'schedul_accepted', 'schedul_decline', 'reschedul_pending', 'reschedul_accepted', 'reschedul_decline', 'reschedul_blocked'].includes(status)) {
                    // Try to get from history if available
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
        
        // Open schedule modal - Make it globally accessible (defined early to be available for onclick handlers)
        window.openScheduleModal = async function(bookingId, triggerButton = null) {
            const booking = bookings.find(b => b.id === bookingId);
            if (!booking) return;

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
            let maxAttempts = booking.maxAttempts || 3;
            
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
                // Fetch admin contact info (you can replace these with actual settings)
                const adminEmail = '{{ \App\Models\Setting::where("name", "support_email")->value("value") ?? "contact@proppik.com" }}';
                const adminPhone = '{{ \App\Models\Setting::where("name", "support_phone")->value("value") ?? "9898363026" }}';
                
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
                
                blockedDiv.innerHTML = blockedHTML;
                blockedDiv.style.display = 'block';
                warningDiv.style.display = 'none';
                
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
                blockedDiv.innerHTML = '';
                blockedDiv.style.display = 'none';
                
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
                    
                    warningDiv.innerHTML = warningHTML;
                    warningDiv.style.display = 'block';
                } else {
                    warningDiv.innerHTML = '';
                    warningDiv.style.display = 'none';
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
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function () {
            // Clean up Flatpickr when modal is closed
            const scheduleModal = document.getElementById('scheduleModal');
            if (scheduleModal) {
                scheduleModal.addEventListener('hidden.bs.modal', function () {
                    if (scheduleDatePicker) {
                        scheduleDatePicker.destroy();
                        scheduleDatePicker = null;
                    }
                    // Clear the input
                    const scheduleDateInput = document.getElementById('scheduleDate');
                    if (scheduleDateInput) {
                        scheduleDateInput.value = '';
                    }
                });
            }
        });

        // Switch to new booking tab (redirects to setup with force_new parameter)
        function switchToNewBooking() {
            window.location.href = "{{ route('frontend.setup') }}?force_new=true";
        }

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
        function initScheduleDatePicker(selectedDate = null) {
            // Destroy existing instance if any
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
                
                // Combine holidays and disabled dates (dates at booking limit)
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
                await showSweetAlert('error', 'Action Not Allowed', 'You have reached the maximum number of attempts. Please create a new booking.');
                return;
            }

            if (!scheduleDate) {
                await showSweetAlert('warning', 'Validation Error', 'Please select a date');
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
                    await showSweetAlert('error', 'Error', data.message || 'Failed to update schedule. Please try again.');
                }
            } catch (error) {
                console.error('Error updating schedule:', error);
                await showSweetAlert('error', 'Error', 'Failed to update schedule. Please try again.');
            }
        };

        // Delete booking
        function deleteBooking(bookingId) {
            if (confirm('Are you sure you want to delete this booking?')) {
                // Make AJAX request to delete booking from database
                fetch(`/${window.adminBasePath}/bookings/${bookingId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('successTitle').textContent = 'Booking Deleted!';
                        document.getElementById('successMessage').textContent = 'Booking has been deleted successfully.';
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                        
                        // Reload page after a short delay to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        alert('Failed to delete booking: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting booking:', error);
                    alert('Failed to delete booking. Please try again.');
                });
            }
        }

        // Open View Modal
        async function openViewModal(bookingId) {
            const modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            const contentDiv = document.getElementById('viewBookingContent');
            
            // Show loading state
            contentDiv.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading booking details...</p>
                </div>
            `;
            
            modal.show();
            
            try {
                // Fetch booking details
                const response = await fetch('{{ route("frontend.setup.summary") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ booking_id: bookingId })
                });
                
                // Check if response is ok
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
                
                // Check if result has error
                if (!result || !result.success) {
                    throw new Error(result?.message || 'Failed to load booking details');
                }
                
                if (result.booking) {
                    const b = result.booking;
                    const priceToShow = b.payment_amount || b.price || b.price_estimate || 0;
                    const formattedPrice = `₹${priceToShow.toLocaleString('en-IN')}`;
                    const scheduledDate = b.scheduled_date ? new Date(b.scheduled_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Not Scheduled';
                    const scheduledTime = b.scheduled_time || 'Not Set';
                    
                    contentDiv.innerHTML = `
                        <div class="row g-4">
                            <!-- Property Details -->
                            <div class="col-md-6">
                                <div class="card" style="border-radius:12px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-building me-2"></i>Property Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2"><strong>Owner Type:</strong> ${b.owner_type || '-'}</div>
                                        ${b.firm_name ? `<div class="mb-2"><strong>Firm Name:</strong> ${b.firm_name}</div>` : ''}
                                        ${b.gst_no ? `<div class="mb-2"><strong>GST No:</strong> ${b.gst_no}</div>` : ''}
                                        <div class="mb-2"><strong>Property Type:</strong> ${b.property_type || '-'}</div>
                                        
                                        ${b.property_type === 'Residential' ? `
                                        <!-- Residential Property Details -->
                                        <div class="mb-2"><strong>Property Sub Type:</strong> ${b.property_sub_type || '-'}</div>
                                        <div class="mb-2"><strong>Furnish Type:</strong> ${b.furniture_type || '-'}</div>
                                        ${b.bhk ? `<div class="mb-2"><strong>Size (BHK / RK):</strong> ${b.bhk}</div>` : ''}
                                        <div class="mb-0"><strong>Super Built-up Area (sq. ft.):</strong> ${b.area ? b.area.toLocaleString() + ' sq. ft.' : '-'}</div>
                                        ` : b.property_type === 'Commercial' ? `
                                        <!-- Commercial Property Details -->
                                        <div class="mb-2"><strong>Property Sub Type:</strong> ${b.property_sub_type || '-'}</div>
                                        <div class="mb-2"><strong>Furnish Type:</strong> ${b.furniture_type || '-'}</div>
                                        <div class="mb-0"><strong>Super Built-up Area (sq. ft.):</strong> ${b.area ? b.area.toLocaleString() + ' sq. ft.' : '-'}</div>
                                        ` : b.property_type === 'Other' ? `
                                        <!-- Other Property Details -->
                                        ${b.property_sub_type ? `<div class="mb-2"><strong>Select Option:</strong> ${b.property_sub_type}</div>` : ''}
                                        ${b.other_option_details ? `<div class="mb-2"><strong>Other Option Details:</strong> ${b.other_option_details}</div>` : ''}
                                        <div class="mb-0"><strong>Super Built-up Area (sq. ft.):</strong> ${b.area ? b.area.toLocaleString() + ' sq. ft.' : '-'}</div>
                                        ` : `
                                        <!-- Fallback for unknown property type -->
                                        <div class="mb-2"><strong>Property Sub Type:</strong> ${b.property_sub_type || '-'}</div>
                                        <div class="mb-2"><strong>Furniture Type:</strong> ${b.furniture_type || '-'}</div>
                                        ${b.other_option_details ? `<div class="mb-2"><strong>Other Option Details:</strong> ${b.other_option_details}</div>` : ''}
                                        ${b.bhk ? `<div class="mb-2"><strong>BHK/Size:</strong> ${b.bhk}</div>` : ''}
                                        <div class="mb-0"><strong>Area:</strong> ${b.area ? b.area.toLocaleString() + ' sq. ft.' : '-'}</div>
                                        `}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Details -->
                            <div class="col-md-6">
                                <div class="card" style="border-radius:12px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-location-dot me-2"></i>Address Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2"><strong>House/Office No.:</strong> ${b.house_number || '-'}</div>
                                        <div class="mb-2"><strong>Building/Society:</strong> ${b.building_name || '-'}</div>
                                        <div class="mb-2"><strong>City:</strong> ${b.city || 'Ahmedabad'}</div>
                                        <div class="mb-2"><strong>Pincode:</strong> ${b.pincode || '-'}</div>
                                        <div class="mb-0"><strong>Full Address:</strong><br>${b.full_address || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Schedule & Status -->
                            <div class="col-md-6">
                                <div class="card" style="border-radius:12px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-calendar me-2"></i>Schedule & Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2"><strong>Status:</strong> <span class="badge bg-${b.status === 'scheduled' ? 'success' : (b.status === 'completed' ? 'info' : 'warning')}">${(b.status || 'pending').charAt(0).toUpperCase() + (b.status || 'pending').slice(1)}</span></div>
                                        <div class="mb-2"><strong>Scheduled Date:</strong> ${scheduledDate}</div>
                                        <div class="mb-0"><strong>Scheduled Time:</strong> ${scheduledTime}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price & Payment -->
                            <div class="col-md-6">
                                <div class="card" style="border-radius:12px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-indian-rupee-sign me-2"></i>Pricing & Payment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="text-center py-2">
                                                <div class="display-6 text-primary fw-bold">${formattedPrice}</div>
                                                <small class="text-muted">${b.payment_amount && b.payment_amount > 0 ? 'Payment Amount' : 'Estimated Price'}</small>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Payment Status:</strong> 
                                            <span class="badge bg-${b.payment_status === 'paid' ? 'success' : (b.payment_status === 'pending' ? 'warning' : 'danger')}">
                                                ${(b.payment_status || 'pending').charAt(0).toUpperCase() + (b.payment_status || 'pending').slice(1)}
                                            </span>
                                        </div>
                                        ${b.payment_status === 'paid' ? `
                                            <div class="mt-2">
                                                <small class="text-success">
                                                    <i class="fa-solid fa-check-circle me-1"></i>
                                                    Payment completed successfully
                                                </small>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            ${(b.payment_status === 'paid' && b.tour_code && String(b.tour_code).trim() && b.tour_final_link && String(b.tour_final_link).trim()) ? `
                            <!-- Tour Details (Only shown when payment is paid AND both tour fields have data) -->
                            <div class="col-md-6">
                                <div class="card" style="border-radius:12px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-link me-2"></i>Tour Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2"><strong>Tour Code:</strong> ${b.tour_code}</div>
                                        <div class="mb-0"><strong>Tour Final Link:</strong><br><a href="${b.tour_final_link}" target="_blank" class="text-primary">${b.tour_final_link}</a></div>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            ${b.payment_status !== 'paid' && b.is_ready_for_payment ? `
                            <!-- Payment Required Card - Only show if data is complete -->
                            <div class="col-12">
                                <div class="card mb-3" style="border-radius:12px;">
                                    <div class="card-header bg-warning">
                                        <h6 class="mb-0"><i class="fa-solid fa-exclamation-triangle me-2"></i>Payment Required</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3" role="alert">
                                            <i class="fa-solid fa-info-circle me-2"></i>
                                            <strong>Make payment first, then schedule your deals.</strong> Please complete the payment to proceed with scheduling.
                                        </div>
                                        <button type="button" class="btn btn-success w-100" onclick="initiatePayment(${b.id})">
                                            <i class="fa-solid fa-credit-card me-2"></i>Make Payment
                                        </button>
                                        <small class="text-muted d-block text-center mt-2">Secure payment via Cashfree</small>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            ${b.payment_status !== 'paid' && !b.is_ready_for_payment ? `
                            <!-- Incomplete Data Notice -->
                            <div class="col-12">
                                <div class="card mb-3" style="border-radius:12px; border: 2px solid #ffc107;">
                                    <div class="card-header bg-warning">
                                        <h6 class="mb-0"><i class="fa-solid fa-exclamation-triangle me-2"></i>Complete Required Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning mb-3" role="alert">
                                            <i class="fa-solid fa-info-circle me-2"></i>
                                            <strong>Please complete all required property and address details before making payment.</strong>
                                            <ul class="mb-0 mt-2" style="padding-left: 20px;">
                                                ${!b.has_complete_property_data ? '<li>Property details are incomplete</li>' : ''}
                                                ${!b.has_complete_address_data ? '<li>Address details are incomplete</li>' : ''}
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-warning w-100" onclick="openEditModal(${b.id})">
                                            <i class="fa-solid fa-edit me-2"></i>Edit Booking to Complete Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    throw new Error('Booking data not found in response');
                }
            } catch (error) {
                console.error('Error fetching booking:', error);
                const errorMessage = error.message || 'Error loading booking details. Please try again.';
                contentDiv.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> ${errorMessage}
                        <br><small class="mt-2 d-block">Please check the console for more details or contact support if the issue persists.</small>
                    </div>
                `;
            }
        }

        // Edit Modal Validation Helper Functions (matching setup.js)
        function editShowFieldError(fieldId, errorId, message) {
            const field = document.getElementById(fieldId);
            const errorEl = document.getElementById(errorId);
            if (field) {
                field.classList.remove('is-valid');
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
            // Clear all field errors
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

        // Edit Modal Selection Functions (same logic as setup page)
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
            
            // Check based on active tab
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

        // Check if all required property data is filled (matching setup page logic)
        function editIsPropertyStepCompleted() {
            if (!editActivePropertyTab) return false;

            // Owner Type is required
            const ownerType = document.getElementById('editChoiceOwnerType')?.value;
            if (!ownerType) return false;

            // Check based on active tab
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

        // Check if all required address data is filled (matching setup page logic)
        function editIsAddressStepCompleted() {
            const h = document.getElementById('editHouseNo')?.value?.trim();
            const b = document.getElementById('editBuilding')?.value?.trim();
            const p = document.getElementById('editPincode')?.value?.trim();
            const f = document.getElementById('editFullAddress')?.value?.trim();
            
            // Check if all required address fields are filled
            return !!(h && b && p && /^[0-9]{6}$/.test(p) && f);
        }

        // Check if booking is ready for payment (both property and address complete)
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
            // If key is null, hide all tabs and clear selection
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

            // Check if property type was already set, user is trying to change it, AND there's actual data filled
            if (editActivePropertyTab && editActivePropertyTab !== key && (editHasPropertyDataFilled() || editHasAddressDataFilled())) {
                // Get current property type name
                const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
                const currentType = typeMap[editActivePropertyTab] || 'Current';
                const newType = typeMap[key] || 'New';
                
                // Build message based on what data exists
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
                
                // Show confirmation dialog only if there's data that will be lost
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
                
                // If user cancels, don't change property type
                if (!result.isConfirmed) {
                    return;
                }
            }

            // Clear related fields when property type changes
            // Clear Property Sub Type
            document.querySelectorAll('[data-group="editResType"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[data-group="editComType"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResType').value = '';
            document.getElementById('editChoiceComType').value = '';
            
            // Clear Furnish Type
            document.querySelectorAll('[data-group="editResFurnish"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[data-group="editComFurnish"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResFurnish').value = '';
            document.getElementById('editChoiceComFurnish').value = '';
            
            // Clear Size (BHK / RK)
            document.querySelectorAll('[data-group="editResSize"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceResSize').value = '';
            
            // Clear Looking For
            document.querySelectorAll('[data-group="editOthLooking"]').forEach(el => el.classList.remove('active'));
            document.getElementById('editChoiceOthLooking').value = '';
            
            // Clear Other Description
            document.getElementById('editOthDesc').value = '';
            
            // Clear area fields
            document.getElementById('editResArea').value = '';
            document.getElementById('editComArea').value = '';
            document.getElementById('editOthArea').value = '';
            
            // Also clear address fields when property type changes (matching setup page behavior)
            document.getElementById('editHouseNo').value = '';
            document.getElementById('editBuilding').value = '';
            document.getElementById('editPincode').value = '';
            document.getElementById('editFullAddress').value = '';
            
            // Hide all tabs
            document.getElementById('editTabRes').style.display = 'none';
            document.getElementById('editTabCom').style.display = 'none';
            document.getElementById('editTabOth').style.display = 'none';
            
            // Remove active from all pills
            ['editPillResidential', 'editPillCommercial', 'editPillOther'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('active');
            });
            
            // Show selected tab
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
            
            // Update main property type hidden field
            const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
            document.getElementById('editMainPropertyType').value = typeMap[key] || 'Residential';
            
            // Clear property type error
            editHidePillContainerError('editPropertyTypeContainer', 'err-editPropertyType');
            
            // Recalculate price when property type changes
            updateEditPrice();
            
            // Update payment button state
            editUpdatePaymentButtonState();
        }
        
        // Price settings from server (will be updated via API)
        let dashboardPriceSettings = {
            basePrice: {{ $priceSettings['base_price'] ?? 599 }},
            baseArea: {{ $priceSettings['base_area'] ?? 1500 }},
            extraArea: {{ $priceSettings['extra_area'] ?? 500 }},
            extraAreaPrice: {{ $priceSettings['extra_area_price'] ?? 200 }}
        };
        
        // Fetch latest price settings from API
        async function fetchDashboardPriceSettings() {
            try {
                const settingsToFetch = ['base_price', 'base_area', 'extra_area', 'extra_area_price'];
                const promises = settingsToFetch.map(async (settingName) => {
                    try {
                        const response = await fetch(`${API_BASE_URL}/settings/${settingName}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.data && data.data.value) {
                                return { name: settingName, value: parseFloat(data.data.value) || 0 };
                            }
                        }
                    } catch (error) {
                        console.warn(`Failed to fetch setting ${settingName}:`, error);
                    }
                    return null;
                });
                
                const results = await Promise.all(promises);
                results.forEach(result => {
                    if (result) {
                        switch (result.name) {
                            case 'base_price':
                                dashboardPriceSettings.basePrice = result.value;
                                break;
                            case 'base_area':
                                dashboardPriceSettings.baseArea = result.value;
                                break;
                            case 'extra_area':
                                dashboardPriceSettings.extraArea = result.value;
                                break;
                            case 'extra_area_price':
                                dashboardPriceSettings.extraAreaPrice = result.value;
                                break;
                        }
                    }
                });
            } catch (error) {
                console.warn('Failed to fetch price settings, using defaults:', error);
            }
        }
        
        // Calculate price based on area (same logic as backend)
        function calculatePriceFromArea(area) {
            const areaVal = parseInt(area) || 0;
            if (areaVal <= 0) return 0;
            
            // Use dynamic settings
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
        
        // Fetch settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchDashboardPriceSettings();
        });
        
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
            
            // Update price in schedule details (if visible)
            const priceField = document.getElementById('editPrice');
            if (priceField) {
                priceField.value = calculatedPrice;
            }
            
            // Update price in payment card (if visible)
            const paymentPriceField = document.getElementById('editPaymentPrice');
            if (paymentPriceField) {
                paymentPriceField.value = calculatedPrice;
            }
            
            // Update payment button state based on complete validation (property + address)
            editUpdatePaymentButtonState();
        }
        
        // Lock/unlock property and address fields based on payment status
        function lockEditFieldsForPayment(paymentStatus) {
            const isPaid = paymentStatus === 'paid';
            
            // Property Details - lock all interactive elements
            const propertyCard = document.querySelector('#editBookingForm .card:first-of-type .card-body');
            if (propertyCard) {
                // Lock all pills, chips, and inputs
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
                
                // Lock all input fields
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
            
            // Address Details - lock all inputs
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
            
            // Show lock notice if paid
            if (isPaid) {
                // Add notice to property card
                let propertyNotice = document.getElementById('editPropertyLockNotice');
                if (!propertyNotice && propertyCard) {
                    propertyNotice = document.createElement('div');
                    propertyNotice.id = 'editPropertyLockNotice';
                    propertyNotice.className = 'alert alert-warning mb-3';
                    propertyNotice.innerHTML = '<i class="fa-solid fa-lock me-2"></i><strong>Locked:</strong> Property details cannot be changed after payment is completed.';
                    propertyCard.insertBefore(propertyNotice, propertyCard.firstChild);
                }
                
                // Add notice to address card
                let addressNotice = document.getElementById('editAddressLockNotice');
                if (!addressNotice && addressCard) {
                    addressNotice = document.createElement('div');
                    addressNotice.id = 'editAddressLockNotice';
                    addressNotice.className = 'alert alert-warning mb-3';
                    addressNotice.innerHTML = '<i class="fa-solid fa-lock me-2"></i><strong>Locked:</strong> Address details cannot be changed after payment is completed.';
                    addressCard.insertBefore(addressNotice, addressCard.firstChild);
                }
            } else {
                // Remove notices if not paid
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
            
            // Set booking ID
            document.getElementById('editBookingId').value = bookingId;
            
            // Reset form and clear all selections
            document.getElementById('editBookingForm').reset();
            
            // Clear all pill/chip selections
            document.querySelectorAll('[data-group^="edit"]').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('[id^="editChoice"], [id^="editMain"], [id^="editRes"], [id^="editCom"], [id^="editOth"]').forEach(el => {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') el.value = '';
            });
            
            // Clear all errors
            editClearAllFieldErrors();
            
            // Reset tabs - no default selection (matching setup page)
            editHandlePropertyTabChange(null);
            editActivePropertyTab = null;
            
            // Disable payment button initially
            const paymentBtn = document.getElementById('editMakePaymentBtn');
            if (paymentBtn) {
                paymentBtn.disabled = true;
                paymentBtn.classList.remove('btn-success');
                paymentBtn.classList.add('btn-secondary');
            }
            
            // Show loading state (optional - you can add a loading indicator)
            modal.show();
            
            try {
                // Fetch booking details
                const response = await fetch('{{ route("frontend.setup.summary") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ booking_id: bookingId })
                });
                
                // Check if response is ok
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
                
                // Check if result has error
                if (!result || !result.success) {
                    throw new Error(result?.message || 'Failed to load booking details');
                }
                
                if (result.booking) {
                    const b = result.booking;
                    
                    // Set Owner Type
                    if (b.owner_type) {
                        editSetGroupValue('editOwnerType', b.owner_type);
                        document.getElementById('editChoiceOwnerType').value = b.owner_type;
                    }
                    
                    // Set Property Type and show appropriate tab
                    const propertyType = (b.property_type || 'Residential').toLowerCase();
                    let tabKey = 'res';
                    if (propertyType === 'commercial') tabKey = 'com';
                    else if (propertyType === 'other') tabKey = 'oth';
                    
                    editHandlePropertyTabChange(tabKey);
                    editActivePropertyTab = tabKey;
                    
                    // Set Property Sub Type based on property type
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
                    
                    // Set Furnish Type
                    if (b.furniture_type) {
                        if (tabKey === 'res') {
                            editSetGroupValue('editResFurnish', b.furniture_type);
                            document.getElementById('editChoiceResFurnish').value = b.furniture_type;
                        } else if (tabKey === 'com') {
                            editSetGroupValue('editComFurnish', b.furniture_type);
                            document.getElementById('editChoiceComFurnish').value = b.furniture_type;
                        }
                    }
                    
                    // Set BHK/Size (for residential only)
                    if (tabKey === 'res') {
                        // Try to use bhk_id first, then fallback to name matching
                        if (b.bhk_id) {
                            editSetGroupValue('editResSize', b.bhk_id.toString());
                            document.getElementById('editChoiceResSize').value = b.bhk_id.toString();
                        } else if (b.bhk) {
                            // Fallback: match by name
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
                    
                    // Set Area based on property type
                    if (b.area) {
                        if (tabKey === 'res') {
                            document.getElementById('editResArea').value = b.area;
                        } else if (tabKey === 'com') {
                            document.getElementById('editComArea').value = b.area;
                        } else if (tabKey === 'oth') {
                            document.getElementById('editOthArea').value = b.area;
                        }
                    }
                    
                    // Set Other Option Details
                    if (b.other_option_details && tabKey === 'oth') {
                        document.getElementById('editOthDesc').value = b.other_option_details;
                    }
                    
                    // Set Firm Name and GST No
                    if (b.firm_name) {
                        document.getElementById('editFirmName').value = b.firm_name;
                    }
                    if (b.gst_no) {
                        document.getElementById('editGstNo').value = b.gst_no;
                    }
                    
                    // Address fields
                    document.getElementById('editHouseNo').value = b.house_number || '';
                    document.getElementById('editBuilding').value = b.building_name || '';
                    document.getElementById('editPincode').value = b.pincode || '';
                    document.getElementById('editCity').value = b.city || 'Ahmedabad';
                    document.getElementById('editFullAddress').value = b.full_address || '';
                    
                    // Show/hide Schedule Details based on payment status
                    const paymentStatus = b.payment_status || 'pending';
                    const paymentRequiredCard = document.getElementById('editPaymentRequiredCard');
                    const scheduleDetailsCard = document.getElementById('editScheduleDetailsCard');
                    
                    // Lock/unlock fields based on payment status
                    lockEditFieldsForPayment(paymentStatus);
                    
                    if (paymentStatus !== 'paid') {
                        // Payment pending - show payment button, hide schedule
                        paymentRequiredCard.style.display = 'block';
                        scheduleDetailsCard.style.display = 'none';
                        // Store booking ID for payment
                        paymentRequiredCard.setAttribute('data-booking-id', b.id);
                        
                        // Update payment price field
                        const paymentPriceField = document.getElementById('editPaymentPrice');
                        if (paymentPriceField) {
                            const priceToShow = b.price || b.price_estimate || 0;
                            paymentPriceField.value = priceToShow;
                        }
                    } else {
                        // Payment done - show schedule, hide payment button
                        paymentRequiredCard.style.display = 'none';
                        scheduleDetailsCard.style.display = 'block';
                        
                        // Schedule fields
                        if (b.scheduled_date) {
                            const date = new Date(b.scheduled_date);
                            document.getElementById('editScheduledDate').value = date.toISOString().split('T')[0];
                        } else {
                            document.getElementById('editScheduledDate').value = '';
                        }
                        // Set notes if available (assuming it's stored in other_details or a notes field)
                        const notesField = document.getElementById('editScheduleNotes');
                        if (notesField) {
                            notesField.value = b.notes || b.other_details || '';
                        }
                    }
                    
                    // Always update price based on area (this will also enable/disable payment button)
                    updateEditPrice();
                    
                    // Update payment button state after loading booking data
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
            
            // Check if payment is completed - if so, only allow schedule updates
            const paymentRequiredCard = document.getElementById('editPaymentRequiredCard');
            const isPaymentPaid = !paymentRequiredCard || paymentRequiredCard.style.display === 'none';
            
            if (isPaymentPaid) {
                // Payment is done - only allow schedule updates, not property/address changes
                // Get schedule data only
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
                return; // Exit early - don't process property/address updates
            }
            
            // Payment not done - allow full updates including property and address
            // Clear previous errors
            editClearAllFieldErrors();
            const errors = [];
            
            // Check tab visibility safely
            const editTabRes = document.getElementById('editTabRes');
            const editTabCom = document.getElementById('editTabCom');
            const editTabOth = document.getElementById('editTabOth');
            
            const tabResVisible = editTabRes && editTabRes.style.display !== 'none';
            const tabComVisible = editTabCom && editTabCom.style.display !== 'none';
            const tabOthVisible = editTabOth && editTabOth.style.display !== 'none';
            
            // Owner Type validation
            const ownerType = document.getElementById('editChoiceOwnerType')?.value;
            if (!ownerType) {
                errors.push('Owner Type is required');
                editShowPillContainerError('editOwnerTypeContainer', 'err-editOwnerType', 'Owner Type is required.');
            } else {
                editHidePillContainerError('editOwnerTypeContainer', 'err-editOwnerType');
            }
            
            // Property Type validation - must be selected
            if (!editActivePropertyTab) {
                errors.push('Property Type is required');
                editShowPillContainerError('editPropertyTypeContainer', 'err-editPropertyType', 'Property Type is required.');
            } else {
                editHidePillContainerError('editPropertyTypeContainer', 'err-editPropertyType');
            }
            
            // Residential validations
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
            
            // Commercial validations
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
            
            // Other validations
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
            
            // Validate address fields
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
            
            // Show all errors at once if any
            if (errors.length > 0) {
                const errorMessage = '• ' + errors.join('<br>• ');
                await showSweetAlert('error', 'Validation Error', errorMessage, true);
                return;
            }
            
            // Get values for data preparation (after validation passes)
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
                    // Try to get price from editPaymentPrice field first
                    const paymentPriceField = document.getElementById('editPaymentPrice');
                    if (paymentPriceField && paymentPriceField.value) {
                        return parseFloat(paymentPriceField.value) || null;
                    }
                    // Otherwise, calculate from area
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
                    
                    // Reload page after a short delay to show updated data
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

        // Initiate Payment from View Modal
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
                    // Get error message from response
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
                // The checkout() method opens a modal and the promise resolves when payment is completed or cancelled
                console.log('Opening Cashfree checkout with session:', paymentSessionId);
                
                cashfreeInstance.checkout({
                    paymentSessionId: paymentSessionId
                }).then(function(result) {
                    console.log('Cashfree checkout promise resolved:', result);
                    
                    // The promise resolves when the checkout modal closes
                    // result will be undefined/null if payment completed, or have error if cancelled
                    if (result && result.error) {
                        console.log('Payment cancelled or failed:', result.error.message);
                        // User cancelled or payment failed - don't reload, let them try again
                        // Optionally show a message
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
                    // Don't show alert for user cancellation - it's normal behavior
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
            
            // Get current area value to save price
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
            
            // First, save the booking with updated price before creating payment session
            try {
                const bookingIdValue = document.getElementById('editBookingId').value;
                
                // Prepare price-only update data (minimal data to update price)
                const updateData = {
                    booking_id: bookingIdValue,
                    price: parseFloat(paymentPrice)
                };
                
                // Add area based on current tab
                if (tabResVisible) {
                    updateData.residential_area = area;
                } else if (tabComVisible) {
                    updateData.commercial_area = area;
                } else if (tabOthVisible) {
                    updateData.other_area = area;
                }
                
                // Save booking with updated price (price-only update)
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
                
                // Close edit modal
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editBookingModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                // Small delay to ensure modal closes and database is updated, then open payment with updated price
                // This ensures the booking is refreshed in the database before creating payment session
                setTimeout(() => {
                    initiatePayment(bookingId);
                }, 500);
                
            } catch (error) {
                console.error('Error saving booking before payment:', error);
                await showSweetAlert('error', 'Error', 'Failed to update booking: ' + (error.message || 'Please try again.'));
            }
        }

        // Add real-time validation listeners for edit modal (matching setup.js)
        document.addEventListener('DOMContentLoaded', function() {
            // Area fields
            ['editResArea', 'editComArea', 'editOthArea'].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('input', function() {
                        const value = this.value.trim();
                        if (value && Number(value) > 0) {
                            editHideFieldError(id, 'err-' + id);
                            editMarkFieldValid(id);
                        }
                        // Update payment button state
                        editUpdatePaymentButtonState();
                    });
                }
            });

            // Address fields
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
                        // Update payment button state
                        editUpdatePaymentButtonState();
                    });
                }
            });

            // Other description field
            const othDescInput = document.getElementById('editOthDesc');
            if (othDescInput) {
                othDescInput.addEventListener('input', function() {
                    const value = this.value.trim();
                    const othLooking = document.getElementById('editChoiceOthLooking')?.value;
                    if (value || othLooking) {
                        editHideFieldError('editOthDesc', 'err-editOthDesc');
                        editHidePillContainerError('editOthTypesContainer', 'err-editOthLooking');
                    }
                    // Update payment button state
                    editUpdatePaymentButtonState();
                });
            }

            // Property selection fields - update payment button when selections change
            document.querySelectorAll('[data-group^="edit"]').forEach(el => {
                el.addEventListener('click', function() {
                    // Small delay to allow value to be set
                    setTimeout(() => {
                        editUpdatePaymentButtonState();
                    }, 100);
                });
            });
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