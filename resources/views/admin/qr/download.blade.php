<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $qr->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 18px;
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .qr-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        .qr-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .qr-code-container {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .qr-code-container svg {
            max-width: 400px;
            height: auto;
        }
        .qr-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .qr-details h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        .booking-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
        }
        .booking-section h3 {
            color: #764ba2;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #764ba2;
            padding-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            font-size: 14px;
        }
        .no-booking {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>QR Code Details</h1>
            <p>Code: {{ $qr->code }}</p>
        </div>
        
        <div class="content">
            <!-- QR Code Section -->
            <div class="qr-section">
                <h2>QR Code</h2>
                <div class="qr-code-container">
                    {!! $qrCode !!}
                </div>
            </div>
            
            <!-- QR Details Section -->
            <div class="qr-details">
                <h3>QR Code Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">QR Name</div>
                        <div class="detail-value">{{ $qr->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">QR Code</div>
                        <div class="detail-value">{{ $qr->code }}</div>
                    </div>
                    @if($qr->qr_link)
                    <div class="detail-item">
                        <div class="detail-label">QR Link</div>
                        <div class="detail-value" style="word-break: break-all; font-size: 14px;">{{ $qr->qr_link }}</div>
                    </div>
                    @endif
                    <div class="detail-item">
                        <div class="detail-label">Booking Status</div>
                        <div class="detail-value">
                            @if($qr->booking_id)
                                <span style="color: #28a745; font-weight: 600;">✓ Assigned</span>
                            @else
                                <span style="color: #dc3545; font-weight: 600;">✗ Not Assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Booking Details Section -->
            @if($bookingDetails)
            <div class="booking-section">
                <h3>Booking Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Booking ID</div>
                        <div class="detail-value">#{{ $bookingDetails['id'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Customer Name</div>
                        <div class="detail-value">{{ $bookingDetails['customer'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mobile</div>
                        <div class="detail-value">{{ $bookingDetails['mobile'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Property Type</div>
                        <div class="detail-value">{{ $bookingDetails['property_type'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Property Sub-Type</div>
                        <div class="detail-value">{{ $bookingDetails['property_sub_type'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">BHK</div>
                        <div class="detail-value">{{ $bookingDetails['bhk'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Area</div>
                        <div class="detail-value">{{ $bookingDetails['area'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Price</div>
                        <div class="detail-value">{{ $bookingDetails['price'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">City</div>
                        <div class="detail-value">{{ $bookingDetails['city'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">State</div>
                        <div class="detail-value">{{ $bookingDetails['state'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">PIN Code</div>
                        <div class="detail-value">{{ $bookingDetails['pin_code'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Booking Date</div>
                        <div class="detail-value">{{ $bookingDetails['booking_date'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-{{ $bookingDetails['status'] }}">{{ $bookingDetails['status'] }}</span>
                        </div>
                    </div>
                    @if($bookingDetails['address'])
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Address</div>
                        <div class="detail-value">{{ $bookingDetails['address'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="no-booking">
                <p>No booking assigned to this QR code yet.</p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            Generated on {{ now()->format('d M Y, h:i A') }} | BrokerX System
        </div>
    </div>
</body>
</html>
