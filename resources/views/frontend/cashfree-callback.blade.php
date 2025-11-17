<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Payment Status</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 60px auto; background: #1e293b; border-radius: 16px; padding: 32px; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.6); }
        h1 { margin-top: 0; font-size: 26px; text-align: center; }
        .status-success { background: #22c55e22; border: 1px solid #22c55e; }
        .status-failed { background: #ef444422; border: 1px solid #ef4444; }
        .status-pending { background: #eab30822; border: 1px solid #eab308; }
        .status-box { border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .label { font-weight: 600; color: #cbd5f5; display: block; margin-bottom: 4px; }
        .value { font-size: 15px; margin-bottom: 12px; }
        pre { background: #0f172a; color: #e2e8f0; padding: 16px; border-radius: 12px; overflow-x: auto; font-size: 12px; }
        a.button { display: inline-block; margin-top: 16px; padding: 11px 18px; border-radius: 999px; background: #38bdf8; color: #0f172a; text-decoration: none; font-weight: 600; }
        a.button:hover { background: #0ea5e9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Status</h1>

        @php
            $status = strtoupper($status ?? 'UNKNOWN');
            $statusClass = match(true) {
                $status === 'PAID' => 'status-success',
                in_array($status, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED']) => 'status-failed',
                default => 'status-pending',
            };
        @endphp

        <div class="status-box {{ $statusClass }}">
            <span class="label">Cashfree Order ID</span>
            <div class="value">{{ $orderId ?? '-' }}</div>

            <span class="label">Status</span>
            <div class="value">{{ $status }}</div>

            <p>{{ $message ?? 'Status received from Cashfree.' }}</p>
        </div>

        @if(!empty($details))
            <div class="status-box">
                <span class="label">Amount</span>
                <div class="value">
                    ₹{{ number_format($details['amount'] ?? 0) }} {{ $details['currency'] ?? '' }}
                </div>
                <span class="label">Payment Reference</span>
                <div class="value">{{ $details['reference_id'] ?? '-' }}</div>
                <span class="label">Payment Method</span>
                <div class="value">{{ $details['payment_method'] ?? '-' }}</div>
                <span class="label">Paid At</span>
                <div class="value">{{ $details['payment_at'] ?? '-' }}</div>
            </div>

            <details>
                <summary style="cursor:pointer; margin-bottom: 12px;">Show Raw Response</summary>
                <pre>{{ json_encode($details['raw'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @endif

        @php
            $returnParams = array_filter([
                'booking_id' => $details['booking_id'] ?? null,
                'order_id' => $details['order_id'] ?? ($orderId ?? null),
                'open_payment' => 1,
            ]);
        @endphp
        <a href="{{ route('frontend.setup', array_filter($returnParams)) }}" class="button">Return to Setup</a>
    </div>
</body>
</html>

