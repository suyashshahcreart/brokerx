<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Loading Tour - QR Proppik</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            color: #333;
        }
        
        .loader-container {
            text-align: center;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 4px solid #e0e0e0;
            border-top: 4px solid #06b6d4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" data-tour-code="{{ $tour_code }}" data-page-type="tour_code" data-redirect-url="{{ $redirectUrl ?? '' }}" style="display:none;"></div>
    <script>
        // Make CSRF token globally available
        window.csrfToken = '{{ csrf_token() }}';
        // Make redirect URL available globally (properly escaped)
        window.tourRedirectUrl = {!! json_encode($redirectUrl ?? '') !!};
    </script>
    
    <div class="loader-container">
        <div class="loader"></div>
        <div class="loading-text">Loading tour...</div>
    </div>

    <script src="{{ url('/js/qr-location-tracker.js') }}"></script>
    <script>
        // Override getTourCodeFromUrl for tour code pages
        window.getTourCodeFromUrl = function() {
            const path = window.location.pathname;
            const match = path.match(/\/([A-Za-z0-9]+)$/);
            return match ? match[1] : null;
        };
        window.getPageType = function() {
            return 'tour_code';
        };
    </script>
</body>
</html>
