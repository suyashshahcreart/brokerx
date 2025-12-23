<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noindex, nofollow" />
    <title>QR - Proppik</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            display: grid;
            place-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            color: #fff;
            text-align: center;
            padding: 24px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 48px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            max-width: 600px;
            width: min(90vw, 600px);
        }
        h1 {
            margin: 0 0 16px;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .domain {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 24px;
        }
        p { 
            margin: 12px 0;
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.95;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 999px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.3);
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            background: rgba(255,255,255,0.3);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: rgba(255,255,255,0.4);
            transform: translateY(-2px);
        }
        
        /* Location Permission Modal */
        .location-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .location-modal.active {
            display: flex;
        }
        .location-modal-content {
            background: #fff;
            color: #333;
            padding: 32px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .location-modal-content h3 {
            margin: 0 0 16px;
            color: #667eea;
        }
        .location-modal-content p {
            margin: 12px 0;
            color: #666;
        }
        .location-modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .location-modal-btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .location-modal-btn.primary {
            background: #667eea;
            color: #fff;
        }
        .location-modal-btn.primary:hover {
            background: #5568d3;
        }
        .location-modal-btn.secondary {
            background: #e0e0e0;
            color: #333;
        }
        .location-modal-btn.secondary:hover {
            background: #d0d0d0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="pill">📱 QR Code Analytics</div>
        <h1>Proppik QR</h1>
        <div class="domain">qr.proppik.com</div>
        <p>Welcome to QR Analytics Dashboard</p>
        <p>Track and manage your QR codes with powerful analytics.</p>
        <a href="{{ route('qr.analytics') }}" class="btn">View Analytics</a>
    </div>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" style="display:none;"></div>
    <script>
        // Make CSRF token globally available
        window.csrfToken = '{{ csrf_token() }}';
        // Override getTourCodeFromUrl for welcome page
        window.getTourCodeFromUrl = function() { return null; };
        window.getPageType = function() { return 'welcome'; };
    </script>
    <script src="{{ url('/js/qr-location-tracker.js') }}"></script>
</body>
</html>
