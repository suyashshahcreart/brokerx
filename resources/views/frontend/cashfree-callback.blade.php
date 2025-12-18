@extends('frontend.layouts.base', ['title' => 'Payment Status - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/profile_page.css') }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/cashfree_callback_page.css') }}">
@endsection

@section('content')
@php
    $status = strtoupper($status ?? 'UNKNOWN');
    $isSuccess = $status === 'PAID';
    $isFailed = in_array($status, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED']);
    $isPending = !$isSuccess && !$isFailed;
    
    // Determine header background color based on status
    $headerBgClass = $isSuccess ? 'bg-success' : ($isFailed ? 'bg-danger' : 'bg-warning');
    $headerTextClass = 'text-white';
    
    // Status icon and text
    $statusIcon = $isSuccess ? 'fa-circle-check' : ($isFailed ? 'fa-circle-xmark' : 'fa-clock');
    $statusTitle = $isSuccess ? 'Payment Successful!' : ($isFailed ? 'Payment Failed' : 'Payment Pending');
    $statusSubtitle = $isSuccess ? 'Your payment has been processed successfully.' : ($isFailed ? 'We couldn\'t process your payment. Please try again.' : 'Your payment is being processed. Please wait...');
@endphp

<!-- Payment Status Header (New Theme Style) -->
<section class="py-5 {{ $headerBgClass }} {{ $headerTextClass }} mt-5">
    <div class="container pt-5 pb-3">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <p class="text-uppercase fw-bold small mb-2 opacity-75">Payment Gateway</p>
                <h1 class="display-5 fw-bold mb-3">
                    <i class="fa-solid {{ $statusIcon }} me-2"></i>{{ $statusTitle }}
                </h1>
                <p class="lead mb-0 opacity-75">{{ $statusSubtitle }}</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @if($isSuccess && !empty($details['booking_id']))
                    <div class="d-flex flex-column align-items-lg-end">
                        <a href="{{ route('frontend.booking.show', ['id' => $details['booking_id'], 'open_schedule' => '1']) }}" class="schedule-cta-header">
                            <i class="fa-solid fa-calendar-plus"></i>
                            <span>Schedule Appointment</span>
                        </a>
                        <p class="schedule-cta-text mb-0 mt-2 text-center text-lg-end">Book your virtual tour photoshoot date</p>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2 align-items-lg-end">
                        @if(!empty($details['booking_id']))
                            <a href="{{ route('frontend.booking.show', ['id' => $details['booking_id']]) }}" class="btn btn-light fw-semibold">
                                <i class="fa-solid fa-eye me-2"></i>View Booking
                            </a>
                        @endif
                        <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-outline-light fw-semibold">
                            <i class="fa-solid fa-table-cells me-2"></i>My Bookings
                        </a>
                    </div>
                @endif
                @if(!empty($orderId))
                    <div class="mt-3 small opacity-75">
                        <i class="fa-solid fa-hashtag me-1"></i>Order: {{ $orderId }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="page bg-setup-form py-4 payment-callback-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                
                @if(!empty($message) && $message !== 'Status received from Cashfree.')
                    <div class="alert alert-{{ $isSuccess ? 'success' : ($isFailed ? 'danger' : 'warning') }} alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-{{ $isSuccess ? 'circle-check' : ($isFailed ? 'circle-exclamation' : 'clock') }} me-2"></i>
                        <strong>{{ $isSuccess ? 'Success!' : ($isFailed ? 'Failed!' : 'Pending!') }}</strong> {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Main Payment Status Card - Compact Design -->
                <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="payment-content-compact">
                            <!-- Status Icon & Title -->
                            <div class="text-center mb-3">
                                <div class="status-icon-large {{ $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending') }}">
                                    <i class="fa-solid {{ $isSuccess ? 'fa-circle-check' : ($isFailed ? 'fa-circle-xmark' : 'fa-clock') }}"></i>
                                </div>
                                <h2 class="mb-2 section-title-compact d-none">
                                    {{ $isSuccess ? 'Payment Successful!' : ($isFailed ? 'Payment Failed' : 'Payment Pending') }}
                                </h2>
                                <span class="payment-status-badge {{ $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending') }} d-none">
                                    <i class="fa-solid {{ $isSuccess ? 'fa-check' : ($isFailed ? 'fa-times' : 'fa-clock') }}"></i>
                                    {{ $status }}
                                </span>
                            </div>

                            @if(!empty($details))
                                <!-- Payment Information Card -->
                                <div class="info-card-section">
                                    <h5 class="section-title-compact mb-3">
                                        <i class="fa-solid fa-receipt me-2 text-primary"></i>Payment Information
                                    </h5>
                                    
                                    <!-- Amount Paid - Special styling -->
                                    <div class="info-item info-item-amount {{ $isSuccess ? '' : ($isFailed ? 'failed-amount' : 'pending-amount') }}">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-indian-rupee-sign text-muted"></i>
                                            <span>Amount Paid</span>
                                        </div>
                                        <div class="info-item-value info-item-amount-value">
                                            <span class="amount-main">₹{{ number_format($details['amount'] ?? 0, 2) }}</span>
                                            <span class="amount-currency-small">{{ strtoupper($details['currency'] ?? 'INR') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-hashtag text-muted"></i>
                                            <span>Order ID</span>
                                        </div>
                                        <div class="info-item-value info-item-monospace">{{ $orderId ?? '-' }}</div>
                                    </div>
                                    
                                    @if(!empty($details['reference_id']))
                                    <div class="info-item">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-fingerprint text-muted"></i>
                                            <span>Reference ID</span>
                                        </div>
                                        <div class="info-item-value info-item-monospace">{{ $details['reference_id'] }}</div>
                                    </div>
                                    @endif
                                    
                                    @if(!empty($details['payment_method']))
                                    <div class="info-item">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-credit-card text-muted"></i>
                                            <span>Payment Method</span>
                                        </div>
                                        <div class="info-item-value">{{ ucfirst(str_replace('_', ' ', $details['payment_method'])) }}</div>
                                    </div>
                                    @endif
                                    
                                    @if(!empty($details['payment_at']))
                                    <div class="info-item">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-calendar-check text-muted"></i>
                                            <span>Payment Date</span>
                                        </div>
                                        <div class="info-item-value">{{ \Carbon\Carbon::parse($details['payment_at'])->format('d M Y, h:i A') }}</div>
                                    </div>
                                    @endif
                                    
                                    @if(!empty($details['booking_id']))
                                    <div class="info-item">
                                        <div class="info-item-label">
                                            <i class="fa-solid fa-bookmark text-muted"></i>
                                            <span>Booking ID</span>
                                        </div>
                                        <div class="info-item-value">#{{ $details['booking_id'] }}</div>
                                    </div>
                                    @endif
                                </div>

                                @if(!empty($details['raw']))
                                <div class="info-card-section d-none">
                                    <details>
                                        <summary>
                                            <span><i class="fa-solid fa-code me-2"></i>Technical Details</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </summary>
                                        <div class="technical-details mt-2">
                                            <pre class="mb-0">{{ json_encode($details['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </details>
                                </div>
                                @endif
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons-grid">
                            @if($isSuccess && !empty($details['booking_id']))
                                <button type="button" class="btn btn-success action-btn fw-semibold" onclick="downloadReceipt({{ $details['booking_id'] }})">
                                    <i class="fa-solid fa-download"></i>
                                    <span>Download Receipt</span>
                                </button>
                            @endif
                            
                            @if(!empty($details['booking_id']))
                                <a href="{{ route('frontend.booking.show', ['id' => $details['booking_id']]) }}" class="btn btn-outline-primary action-btn fw-semibold">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>View Booking</span>
                                </a>
                                
                                <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-outline-primary action-btn fw-semibold">
                                    <i class="fa-solid fa-table-cells"></i>
                                    <span>My Bookings</span>
                                </a>
                            @endif
                            
                            <a href="{{ route('frontend.index') }}" class="btn btn-outline-secondary action-btn fw-semibold">
                                <i class="fa-solid fa-home"></i>
                                <span>Go to Home</span>
                            </a>
                        </div>
                    </div>
                </div>
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
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
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
@endsection
