@extends('frontend.layouts.base', ['title' => 'Payment Status - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    <style>
        .payment-status-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        .status-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .status-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
        }
        .status-success-icon {
            background: #d1fae5;
            color: #10b981;
        }
        .status-failed-icon {
            background: #fee2e2;
            color: #ef4444;
        }
        .status-pending-icon {
            background: #fef3c7;
            color: #f59e0b;
        }
        .status-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .status-message {
            color: #6b7280;
            font-size: 1rem;
        }
        .status-box {
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .status-success-box {
            background: #d1fae5;
            border: 1px solid #10b981;
        }
        .status-failed-box {
            background: #fee2e2;
            border: 1px solid #ef4444;
        }
        .status-pending-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }
        .info-value {
            color: #1f2937;
            font-size: 0.95rem;
            text-align: right;
            font-weight: 500;
        }
        .amount-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .btn-action {
            flex: 1;
            min-width: 150px;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary-action {
            background: var(--color-primary, #2563eb);
            color: #fff;
        }
        .btn-primary-action:hover {
            background: var(--color-primary-dark, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-secondary-action {
            background: #fff;
            color: var(--color-primary, #2563eb);
            border: 2px solid var(--color-primary, #2563eb);
        }
        .btn-secondary-action:hover {
            background: var(--color-primary, #2563eb);
            color: #fff;
        }
        .details-section {
            margin-top: 1.5rem;
        }
        .details-toggle {
            cursor: pointer;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            color: #374151;
        }
        .details-toggle:hover {
            background: #f3f4f6;
        }
        .raw-response {
            background: #1f2937;
            color: #e5e7eb;
            padding: 1.5rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.875rem;
            font-family: 'Courier New', monospace;
            max-height: 400px;
            overflow-y: auto;
        }
        .page-header {
            margin-bottom: 2rem;
        }
    </style>
@endsection

@section('content')
<section class="page-header section-padding-bottom-b section-padding-top-t page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="wow page-title" data-splitting data-delay="100">Payment Status</h1>
            </div>
        </div>
    </div>
</section>

<div class="page bg-light section-padding-bottom section-padding-top">
    <div class="panel container">
        <div class="content">
            <div class="payment-status-container">
                @php
                    $status = strtoupper($status ?? 'UNKNOWN');
                    $isSuccess = $status === 'PAID';
                    $isFailed = in_array($status, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED']);
                    $isPending = !$isSuccess && !$isFailed;
                    
                    $statusIconClass = $isSuccess ? 'status-success-icon' : ($isFailed ? 'status-failed-icon' : 'status-pending-icon');
                    $statusBoxClass = $isSuccess ? 'status-success-box' : ($isFailed ? 'status-failed-box' : 'status-pending-box');
                    $statusIcon = $isSuccess ? '✓' : ($isFailed ? '✕' : '⏳');
                    $statusTitle = $isSuccess ? 'Payment Successful!' : ($isFailed ? 'Payment Failed' : 'Payment Pending');
                @endphp

                <div class="status-card">
                    <div class="status-header">
                        <div class="status-icon {{ $statusIconClass }}">
                            {{ $statusIcon }}
                        </div>
                        <h2 class="status-title">{{ $statusTitle }}</h2>
                        <p class="status-message">{{ $message ?? 'Status received from Cashfree.' }}</p>
                    </div>

                    <div class="status-box {{ $statusBoxClass }}">
                        <div class="info-row">
                            <span class="info-label">Order ID</span>
                            <span class="info-value">{{ $orderId ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Payment Status</span>
                            <span class="info-value" style="font-weight: 700; text-transform: uppercase;">{{ $status }}</span>
                        </div>
                    </div>

                    @if(!empty($details))
                        <div class="status-card">
                            <h3 class="app-title mb-3">Payment Details</h3>
                            
                            <div class="info-row">
                                <span class="info-label">Amount Paid</span>
                                <span class="info-value amount-value">
                                    ₹{{ number_format($details['amount'] ?? 0, 2) }} 
                                    <span style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">
                                        {{ strtoupper($details['currency'] ?? 'INR') }}
                                    </span>
                                </span>
                            </div>
                            
                            @if(!empty($details['reference_id']))
                            <div class="info-row">
                                <span class="info-label">Payment Reference ID</span>
                                <span class="info-value" style="font-family: monospace;">{{ $details['reference_id'] }}</span>
                            </div>
                            @endif
                            
                            @if(!empty($details['payment_method']))
                            <div class="info-row">
                                <span class="info-label">Payment Method</span>
                                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $details['payment_method'])) }}</span>
                            </div>
                            @endif
                            
                            @if(!empty($details['payment_at']))
                            <div class="info-row">
                                <span class="info-label">Payment Date & Time</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($details['payment_at'])->format('d M Y, h:i A') }}</span>
                            </div>
                            @endif
                            
                            @if(!empty($details['booking_id']))
                            <div class="info-row">
                                <span class="info-label">Booking ID</span>
                                <span class="info-value">#{{ $details['booking_id'] }}</span>
                            </div>
                            @endif
                        </div>

                        @if(!empty($details['raw']))
                        <div class="details-section">
                            <details>
                                <summary class="details-toggle">
                                    <span>Show Technical Details</span>
                                    <span>▼</span>
                                </summary>
                                <div class="raw-response">
                                    <pre>{{ json_encode($details['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </details>
                        </div>
                        @endif
                    @endif

                    <div class="action-buttons">
                        @if($isSuccess && !empty($details['booking_id']))
                            <a href="{{ route('frontend.download-receipt', ['booking_id' => $details['booking_id']]) }}" class="btn-action btn-primary-action" target="_blank">
                                <i class="fa-solid fa-download me-2"></i>Download Receipt
                            </a>
                        @endif
                        
                        @if(!empty($details['booking_id']))
                            <a href="{{ route('frontend.booking-dashboard') }}" class="btn-action btn-primary-action">
                                <i class="fa-solid fa-list me-2"></i>View My Bookings
                            </a>
                        @endif
                        
                        @php
                            $returnParams = array_filter([
                                'booking_id' => $details['booking_id'] ?? null,
                                'order_id' => $details['order_id'] ?? ($orderId ?? null),
                                'open_payment' => 1,
                            ]);
                        @endphp
                        
                        <a href="{{ route('frontend.setup', array_filter($returnParams)) }}" class="btn-action btn-secondary-action">
                            <i class="fa-solid fa-arrow-left me-2"></i>Back to Setup
                        </a>
                        
                        <a href="{{ route('frontend.index') }}" class="btn-action btn-secondary-action">
                            <i class="fa-solid fa-home me-2"></i>Go to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

