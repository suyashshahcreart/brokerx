<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - PROP PIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montagu+Slab:opsz,wght@16..144,100..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#000080;
            --primary-dark:#152742;
            --secondary:#FF8C00;
            --text:#0f172a;
            --muted:#64748b;
            --border:rgba(15, 23, 42, .10);
            --paper:#ffffff;
            --bg:#f4f6fb;
            --success:#16a34a;
            --success-bg:rgba(22,163,74,.12);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        @page { size: A4; margin: 12mm 12mm 14mm 12mm; }

        body{
            font-family:'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 18px;
        }

        .page{
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15,23,42,.10);
            overflow: hidden;
        }

        .pad{ padding: 14mm; }

        .topbar{
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 18px 14mm;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 16px;
        }

        .brand{
            display:flex;
            align-items:center;
            gap: 12px;
            min-width: 260px;
        }
        .brand img{ height: 38px; width:auto; filter: drop-shadow(0 6px 18px rgba(0,0,0,.22)); }
        .brand .tagline{
            font-size: 12px;
            opacity: .85;
            line-height: 1.3;
        }

        .invoice-meta{
            text-align:right;
        }
        .invoice-meta .title{
            font-family: 'Montagu Slab', serif;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: .02em;
        }
        .invoice-meta .sub{
            font-size: 12px;
            opacity: .85;
            margin-top: 4px;
        }

        .row{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .card{
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }

        .section-title{
            font-family: 'Montagu Slab', serif;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .kv{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
        }
        .kv .item{ min-width:0; }
        .label{
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: var(--muted);
            margin-bottom: 4px;
            font-weight: 600;
        }
        .value{
            font-size: 13px;
            font-weight: 500;
            word-break: break-word;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid rgba(22,163,74,.18);
            white-space: nowrap;
        }

        .amount{
            margin-top: 14px;
            border-radius: 16px;
            border: 1px solid rgba(0,0,128,.16);
            background: linear-gradient(135deg, rgba(0,0,128,.06), rgba(255,140,0,.08));
            padding: 16px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
        }
        .amount .left .label{ margin-bottom: 6px; }
        .amount .money{
            font-family: 'Montagu Slab', serif;
            font-weight: 700;
            font-size: 28px;
            color: var(--primary);
            line-height: 1.1;
        }
        .amount .currency{
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .table{
            width:100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid var(--border);
        }
        .table th{
            background: rgba(15,23,42,.04);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: var(--muted);
            text-align:left;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
        }
        .table td{
            padding: 11px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
            font-size: 13px;
        }
        .table tr:last-child td{ border-bottom:none; }

        .muted{ color: var(--muted); }

        .footer{
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
            font-size: 11px;
            color: var(--muted);
            display:grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .no-print{ }
        .actions{
            margin-top: 14px;
            display:flex;
            justify-content:center;
            gap: 10px;
        }
        .btn{
            border:none;
            cursor:pointer;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 14px;
        }
        .btn-primary{
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover{ background: var(--primary-dark); }

        .btn-secondary{
            background: rgba(15,23,42,.06);
            color: var(--text);
        }
        .btn-secondary:hover{ background: rgba(15,23,42,.10); }

        /* Page-break safety */
        .card, .amount, .table, .footer { break-inside: avoid; page-break-inside: avoid; }

        @media print{
            body{ background:#fff; padding: 0; }
            .no-print{ display:none !important; }
            .page{
                width: 100%;
                /*
                  Keep border on ONE page:
                  printable height = A4 height (297mm) - (top+bottom page margins).
                  Our @page margins: top 12mm + bottom 14mm = 26mm ⇒ 271mm printable height.
                */
                min-height: 271mm;
                box-sizing: border-box;
                /* Keep a clean A4 paper border on print/PDF */
                border: 1px solid rgba(15, 23, 42, .22);
                border-radius: 0;
                box-shadow:none;
            }
            .pad{ padding: 10mm; }
            .topbar{ padding: 14px 10mm; }
            .row{ gap: 10px; }
            .card{ padding: 12px; }
            .table th{ padding: 8px 10px; }
            .table td{ padding: 9px 10px; font-size: 12px; }
            .value{ font-size: 12px; }
            .amount{ padding: 14px; }
            .amount .money{ font-size: 26px; }
            .topbar{
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .amount, .table th{
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="brand">
                <img src="{{ asset('proppik/assets/logo/w-logo.svg') }}" alt="PROP PIK">
                <div class="tagline">
                    Next‑Generation AI Web Virtual Reality<br>
                    <span style="opacity:.9;">Virtual Tour Booking Service</span>
                </div>
            </div>
            <div class="invoice-meta">
                <div class="title">Payment Receipt</div>
                <div class="sub">Receipt #{{ $booking->id }} • <span class="badge">PAID</span></div>
            </div>
        </div>

        <div class="pad">
            <div class="row">
                <div class="card">
                    <div class="section-title">Payment Information</div>
                    <div class="kv">
                        <div class="item">
                            <div class="label">Order ID</div>
                            <div class="value">{{ $order_id ?? 'N/A' }}</div>
                        </div>
                        <div class="item">
                            <div class="label">Payment Reference</div>
                            <div class="value">{{ $reference_id ?? 'N/A' }}</div>
                        </div>
                        <div class="item">
                            <div class="label">Payment Method</div>
                            <div class="value">{{ ucfirst(str_replace('_', ' ', $payment_method)) }}</div>
                        </div>
                        <div class="item">
                            <div class="label">Payment Date</div>
                            <div class="value">{{ \Carbon\Carbon::parse($payment_at)->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title">Customer</div>
                    <div class="kv">
                        <div class="item">
                            <div class="label">Name</div>
                            <div class="value">{{ trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: 'N/A' }}</div>
                        </div>
                        <div class="item">
                            <div class="label">Mobile</div>
                            <div class="value">{{ $user->mobile ?? 'N/A' }}</div>
                        </div>
                        <div class="item" style="grid-column:1 / -1;">
                            <div class="label">Email</div>
                            <div class="value">{{ $user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="amount">
                <div class="left">
                    <div class="label">Amount Paid</div>
                    <div class="muted" style="font-size:12px;">Includes all applicable taxes/fees (if any).</div>
                </div>
                <div class="right" style="text-align:right;">
                    <div class="money">₹{{ number_format($amount, 2) }}</div>
                    <div class="currency">{{ strtoupper($currency) }}</div>
                </div>
            </div>

            <div class="row" style="margin-top: 14px;">
                <div>
                    <div class="section-title" style="margin-bottom:10px;">Booking &amp; Property Details</div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th style="width: 36%;">Field</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="muted">Booking ID</td>
                            <td>#{{ $booking->id }}</td>
                        </tr>
                        <tr>
                            <td class="muted">Owner Type</td>
                            <td>{{ $booking->owner_type ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="muted">Property Type</td>
                            <td>{{ $property_type }}</td>
                        </tr>
                        <tr>
                            <td class="muted">Property Sub Type</td>
                            <td>{{ $property_sub_type }}</td>
                        </tr>
                        @if($booking->property_type !== 'Commercial')
                        <tr>
                            <td class="muted">Size (BHK/RK)</td>
                            <td>{{ $bhk }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="muted">Furniture Type</td>
                            <td>{{ $furniture_type }}</td>
                        </tr>
                        <tr>
                            <td class="muted">Area</td>
                            <td>{{ number_format($area) }} sq. ft.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <div class="section-title" style="margin-bottom:10px;">Address</div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th style="width: 36%;">Field</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="muted">Full Address</td>
                            <td>{{ $address }}</td>
                        </tr>
                        <tr>
                            <td class="muted">City</td>
                            <td>{{ $city }}</td>
                        </tr>
                        <tr>
                            <td class="muted">State</td>
                            <td>{{ $state }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <div>
                    <strong>Thank you for choosing PROP PIK!</strong><br>
                    This is a computer-generated receipt and does not require a signature.
                </div>
                <div style="text-align:right;">
                    Generated on: {{ now()->format('d M Y, h:i A') }}<br>
                    <span class="muted">For any queries, please contact our support team.</span>
                </div>
            </div>

            <div class="actions no-print">
                <button onclick="window.print()" class="btn btn-primary">Print (A4)</button>
                <button onclick="window.close?.()" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Auto-trigger print when opened for download (only once, prevent multiple triggers)
        (function() {
            // Use a global flag stored in window to persist across events
            if (!window.receiptPrintTriggered) {
                window.receiptPrintTriggered = false;
            }
            
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('download') === '1' && !window.receiptPrintTriggered) {
                function triggerPrintOnce() {
                    if (!window.receiptPrintTriggered) {
                        window.receiptPrintTriggered = true;
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (e) {
                                console.error('Print error:', e);
                            }
                        }, 600);
                    }
                }
                
                // Check if page is already loaded
                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    triggerPrintOnce();
                } else {
                    // Wait for page to fully load
                    window.addEventListener('load', triggerPrintOnce, { once: true });
                }
            }
        })();
    </script>
</body>
</html>

