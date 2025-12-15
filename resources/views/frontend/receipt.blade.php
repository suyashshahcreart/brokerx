<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - PROP PIK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4;
            margin: 15mm 15mm 20mm 15mm;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 0;
                background: #fff;
            }
            .no-print { 
                display: none !important; 
            }
            .receipt-container { 
                box-shadow: none; 
                margin: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
            }
            .receipt-section {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .amount-box {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .receipt-header {
                page-break-after: avoid;
            }
            .receipt-title {
                page-break-after: avoid;
            }
            .receipt-footer {
                page-break-before: auto;
                position: relative;
            }
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 15mm;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 297mm;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .receipt-header h1 {
            color: #2563eb;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .receipt-header p {
            color: #666;
            font-size: 13px;
        }
        .receipt-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #10b981;
            margin: 20px 0;
            padding: 12px;
            background: #d1fae5;
            border-radius: 8px;
        }
        .receipt-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
        }
        .info-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            word-wrap: break-word;
        }
        .amount-box {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .amount-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .amount-value {
            font-size: 32px;
            font-weight: 700;
            color: #10b981;
        }
        .receipt-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            page-break-inside: avoid;
        }
        .receipt-footer p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .print-button {
            text-align: center;
            margin: 25px 0;
        }
        .btn-print {
            display: inline-block;
            padding: 12px 30px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background: #d1fae5;
            color: #10b981;
        }
        .full-width {
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>PROP PIK</h1>
            <p>Virtual Tour Booking Service</p>
        </div>

        <div class="receipt-title">
            ✓ PAYMENT RECEIPT
        </div>

        <div class="receipt-section">
            <div class="section-title">Payment Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Receipt Number</span>
                    <span class="info-value">#{{ $booking->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order ID</span>
                    <span class="info-value">{{ $order_id ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Reference</span>
                    <span class="info-value">{{ $reference_id ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payment_method)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Date</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($payment_at)->format('d M Y, h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status</span>
                    <span class="info-value"><span class="status-badge">Paid</span></span>
                </div>
            </div>
        </div>

        <div class="amount-box">
            <div class="amount-label">Amount Paid</div>
            <div class="amount-value">₹{{ number_format($amount, 2) }}</div>
            <div style="margin-top: 5px; color: #6b7280; font-size: 14px;">{{ strtoupper($currency) }}</div>
        </div>

        <div class="receipt-section">
            <div class="section-title">Customer Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value">{{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mobile Number</span>
                    <span class="info-value">{{ $user->mobile ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $user->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Booking ID</span>
                    <span class="info-value">#{{ $booking->id }}</span>
                </div>
            </div>
        </div>

        <div class="receipt-section">
            <div class="section-title">Property Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Property Type</span>
                    <span class="info-value">{{ $property_type }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Property Sub Type</span>
                    <span class="info-value">{{ $property_sub_type }}</span>
                </div>
                @if($booking->property_type !== 'Commercial')
                <div class="info-item">
                    <span class="info-label">Size (BHK/RK)</span>
                    <span class="info-value">{{ $bhk }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Furniture Type</span>
                    <span class="info-value">{{ $furniture_type }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Area (sq. ft.)</span>
                    <span class="info-value">{{ number_format($area) }} sq. ft.</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Owner Type</span>
                    <span class="info-value">{{ $booking->owner_type ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="receipt-section">
            <div class="section-title">Address</div>
            <div class="info-grid">
                <div class="info-item full-width">
                    <span class="info-label">Full Address</span>
                    <span class="info-value">{{ $address }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">City</span>
                    <span class="info-value">{{ $city }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">State</span>
                    <span class="info-value">{{ $state }}</span>
                </div>
            </div>
        </div>

        <div class="receipt-footer">
            <p><strong>Thank you for choosing PROP PIK!</strong></p>
            <p style="margin-top: 10px;">This is a computer-generated receipt and does not require a signature.</p>
            <p style="margin-top: 5px;">For any queries, please contact our support team.</p>
            <p style="margin-top: 15px; font-size: 10px;">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        </div>

        <div class="print-button no-print">
            <button onclick="window.print()" class="btn-print">Print Receipt</button>
        </div>
    </div>
</body>
</html>

