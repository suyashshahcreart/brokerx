<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR {{ $qr->code }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; font-size: 12px; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #666; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; }
        .qr-box { border: 1px solid #ddd; padding: 8px; border-radius: 6px; display: inline-block; }
        .qr-box svg { width: 220px; height: 220px; }
        .section { margin-top: 14px; }
        .section-title { font-size: 14px; font-weight: bold; margin: 0 0 8px; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details th, table.details td { text-align: left; padding: 6px 8px; border: 1px solid #e5e5e5; }
        table.details th { width: 36%; background: #fafafa; font-weight: 600; }
        .footer { position: fixed; bottom: 12mm; left: 20mm; right: 20mm; text-align: center; font-size: 10px; color: #777; }
        .row { display: table; width: 100%; table-layout: fixed; }
        .col { display: table-cell; }
        .col-45 { width: 45%; }
        .col-55 { width: 55%; padding-left: 12px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .small { font-size: 11px; }
        .break-all { word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>QR Code Sheet</h1>
        <div class="muted small">Code: {{ $qr->code }} | Generated: {{ $generatedAt->format('d M Y, h:i A') }}</div>
    </div>

    <div class="row">
        <div class="col col-45" style="text-align:center;">
            <div class="qr-box">
                @if($qrCodeImage)
                    <img src="{{ $qrCodeImage }}" alt="QR Code" style="width: 220px; height: 220px;" />
                @endif
            </div>
            @if($qr->qr_link)
                <div class="small muted break-all" style="margin-top:6px;">{{ $qr->qr_link }}</div>
            @endif
        </div>
        <div class="col col-55">
            <div class="section">
                <div class="section-title">QR Information</div>
                <table class="details">
                    <tr>
                        <th>QR Name</th>
                        <td>{{ $qr->name }}</td>
                    </tr>
                    <tr>
                        <th>QR Code</th>
                        <td>{{ $qr->code }}</td>
                    </tr>
                    <tr>
                        <th>Booking Assigned</th>
                        <td>{{ $qr->booking_id ? 'Yes (ID: ' . $qr->booking_id . ')' : 'No' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Booking Details</div>
        @if($bookingDetails)
            <table class="details">
                <tr>
                    <th>Booking ID</th>
                    <td>#{{ $bookingDetails['id'] }}</td>
                </tr>
                <tr>
                    <th>Customer</th>
                    <td>{{ $bookingDetails['customer'] }} ({{ $bookingDetails['mobile'] }})</td>
                </tr>
                <tr>
                    <th>Property</th>
                    <td>{{ $bookingDetails['property_type'] }} / {{ $bookingDetails['property_sub_type'] }} / {{ $bookingDetails['bhk'] }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $bookingDetails['city'] }}, {{ $bookingDetails['state'] }} {{ $bookingDetails['pin_code'] }}</td>
                </tr>
                <tr>
                    <th>Area</th>
                    <td>{{ $bookingDetails['area'] }}</td>
                </tr>
                <tr>
                    <th>Price</th>
                    <td>{{ $bookingDetails['price'] }}</td>
                </tr>
                <tr>
                    <th>Booking Date</th>
                    <td>{{ $bookingDetails['booking_date'] }}</td>
                </tr>
                @if(!empty($bookingDetails['status']))
                <tr>
                    <th>Status</th>
                    <td><span class="badge status-{{ strtolower($bookingDetails['status']) }}">{{ $bookingDetails['status'] }}</span></td>
                </tr>
                @endif
                @if(!empty($bookingDetails['address']))
                <tr>
                    <th>Address</th>
                    <td>{{ $bookingDetails['address'] }}</td>
                </tr>
                @endif
            </table>
        @else
            <div class="muted">No booking assigned to this QR yet.</div>
        @endif
    </div>

    <div class="footer">BrokerX • {{ config('app.name') }} • {{ config('app.url') }}</div>
</body>
</html>
