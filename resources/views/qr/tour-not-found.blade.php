<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Tour Not Found - QR Proppik</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
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
        .tour-code {
            font-size: 1.5rem;
            opacity: 0.9;
            margin-bottom: 24px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            padding: 12px 24px;
            border-radius: 12px;
            display: inline-block;
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
    </style>
</head>
<body>
    <div class="card">
        <div class="pill">❌ Tour Code Not Found</div>
        <h1>Invalid Tour Code</h1>
        <div class="tour-code">{{ $tour_code }}</div>
        <p>Sorry, the tour code you entered does not exist in our system.</p>
        <p>Please check the code and try again, or contact support if you believe this is an error.</p>
        <a href="{{ route('qr.welcome') }}" class="btn">← Back to Home</a>
    </div>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" style="display:none;"></div>
    <script>
        // Make CSRF token globally available
        window.csrfToken = '{{ csrf_token() }}';
        // Override getTourCodeFromUrl for tour-not-found page
        window.getTourCodeFromUrl = function() {
            const path = window.location.pathname;
            const match = path.match(/\/([A-Za-z0-9]+)$/);
            return match ? match[1] : null;
        };
        window.getPageType = function() {
            return 'tour_code';
        };
    </script>
    <script src="{{ url('/js/qr-location-tracker.js') }}"></script>
</body>
</html>

