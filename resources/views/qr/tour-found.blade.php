<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Tour Found - QR Proppik</title>
    <style>
        :root {
            color-scheme: light dark;
            --christmas-red: #dc2626;
            --christmas-green: #16a34a;
            --christmas-gold: #fbbf24;
            --winter-blue: #1e40af;
            --winter-cyan: #06b6d4;
            --snow-white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            display: grid;
            place-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 20%, #1e40af 40%, #1e293b 60%, #0f172a 100%);
            background-attachment: fixed;
            color: #fff;
            text-align: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Christmas lights effect */
        .christmas-lights {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            opacity: 0.3;
        }
        
        .light {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: twinkle 2s ease-in-out infinite;
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        
        /* Snowflakes Animation */
        .snowflakes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }
        
        .snowflake {
            position: absolute;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.5em;
            font-family: Arial;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
            animation: snowfall linear infinite;
            top: -10px;
        }
        
        @keyframes snowfall {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) translateX(50px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Ice particles effect */
        .ice-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
            overflow: hidden;
        }
        
        .ice-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(173, 216, 230, 0.8), 0 0 15px rgba(147, 197, 253, 0.6);
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0.8;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px) scale(0.5);
                opacity: 0;
            }
        }
        
        .card {
            background: linear-gradient(135deg, 
                rgba(220, 38, 38, 0.15) 0%, 
                rgba(185, 28, 28, 0.15) 50%, 
                rgba(251, 191, 36, 0.1) 100%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 48px 36px;
            box-shadow: 
                0 20px 60px rgba(220, 38, 38, 0.2),
                0 0 40px rgba(22, 163, 74, 0.15),
                0 0 60px rgba(251, 191, 36, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            max-width: 700px;
            width: min(90vw, 700px);
            position: relative;
            z-index: 10;
            transition: all 0.3s ease;
            animation: cardGlow 4s ease-in-out infinite;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                var(--christmas-red), 
                var(--christmas-green), 
                var(--christmas-gold), 
                var(--christmas-red));
            border-radius: 24px;
            z-index: -1;
            opacity: 0.5;
            animation: borderGlow 3s ease-in-out infinite;
        }
        
        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }
        
        .card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 
                0 30px 80px rgba(220, 38, 38, 0.3),
                0 0 60px rgba(22, 163, 74, 0.2),
                0 0 80px rgba(251, 191, 36, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        @keyframes cardGlow {
            0%, 100% {
                box-shadow: 
                    0 20px 60px rgba(220, 38, 38, 0.2),
                    0 0 40px rgba(22, 163, 74, 0.15),
                    0 0 60px rgba(251, 191, 36, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }
            50% {
                box-shadow: 
                    0 20px 60px rgba(220, 38, 38, 0.3),
                    0 0 50px rgba(22, 163, 74, 0.2),
                    0 0 70px rgba(251, 191, 36, 0.15),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
            }
        }
        
        h1 {
            margin: 0 0 16px;
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-shadow: 
                0 0 20px rgba(255, 255, 255, 0.8),
                0 0 40px rgba(255, 255, 255, 0.6),
                0 4px 10px rgba(0, 0, 0, 0.3);
            animation: textShimmer 4s ease-in-out infinite;
            transition: all 0.3s ease;
        }
        
        
        .card:hover h1 {
            transform: scale(1.05);
        }
        
        @keyframes textShimmer {
            0%, 100% {
                filter: brightness(1);
            }
            50% {
                filter: brightness(1.3);
            }
        }
        
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 999px;
            background: linear-gradient(135deg, 
                rgba(22, 163, 74, 0.4) 0%, 
                rgba(34, 197, 94, 0.3) 50%, 
                rgba(16, 185, 129, 0.2) 100%);
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            box-shadow: 
                0 4px 15px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(22, 163, 74, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .pill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }
        
        .pill:hover {
            transform: translateY(-3px) scale(1.05);
            background: linear-gradient(135deg, 
                rgba(22, 163, 74, 0.5) 0%, 
                rgba(34, 197, 94, 0.4) 50%, 
                rgba(16, 185, 129, 0.3) 100%);
            box-shadow: 
                0 6px 20px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(22, 163, 74, 0.4),
                0 0 40px rgba(34, 197, 94, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            border-color: rgba(255, 255, 255, 0.7);
        }
        
        .pill:hover::before {
            left: 100%;
        }
        
        .tour-code {
            font-size: 1.8rem;
            opacity: 0.95;
            margin-bottom: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, 
                rgba(22, 163, 74, 0.3) 0%, 
                rgba(34, 197, 94, 0.2) 50%, 
                rgba(16, 185, 129, 0.15) 100%);
            padding: 16px 32px;
            border-radius: 16px;
            display: inline-block;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 4px 15px rgba(22, 163, 74, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: #e0f2fe;
            text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .card:hover .tour-code {
            opacity: 1;
            transform: translateY(-2px) scale(1.02);
        }
        
        p { 
            margin: 12px 0;
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.95;
            color: #f0f9ff;
            transition: all 0.3s ease;
        }
        
        .card:hover p {
            opacity: 1;
            transform: translateY(-2px);
        }
        
        .booking-info {
            background: linear-gradient(135deg, 
                rgba(220, 38, 38, 0.12) 0%, 
                rgba(22, 163, 74, 0.1) 50%, 
                rgba(251, 191, 36, 0.08) 100%);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .booking-info h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(255, 255, 255, 0.2);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            align-items: center;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            opacity: 0.9;
            color: #e0f2fe;
        }
        
        .info-value {
            opacity: 0.95;
            color: #ffffff;
            font-weight: 500;
        }
        
        .info-value a {
            color: #60a5fa;
            text-decoration: underline;
            transition: all 0.3s;
        }
        
        .info-value a:hover {
            color: #93c5fd;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
        }
        
        .btn {
            display: inline-block;
            padding: 14px 32px;
            margin-top: 24px;
            background: linear-gradient(135deg, 
                rgba(22, 163, 74, 0.4) 0%, 
                rgba(34, 197, 94, 0.3) 50%, 
                rgba(16, 185, 129, 0.2) 100%);
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 4px 15px rgba(22, 163, 74, 0.3),
                0 0 25px rgba(34, 197, 94, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover {
            transform: translateY(-3px) scale(1.05);
            background: linear-gradient(135deg, 
                rgba(22, 163, 74, 0.5) 0%, 
                rgba(34, 197, 94, 0.4) 50%, 
                rgba(16, 185, 129, 0.3) 100%);
            box-shadow: 
                0 8px 25px rgba(22, 163, 74, 0.4),
                0 0 40px rgba(34, 197, 94, 0.3),
                0 0 50px rgba(16, 185, 129, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            border-color: rgba(255, 255, 255, 0.6);
            color: #ffffff;
            text-decoration: none;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:active {
            transform: translateY(-1px) scale(1.02);
        }
    </style>
</head>
<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" data-tour-code="{{ $tour_code }}" data-page-type="tour_code" style="display:none;"></div>
    <script>
        // Make CSRF token globally available
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    <!-- Christmas Lights -->
    <div class="christmas-lights" id="christmasLights"></div>
    
    <!-- Snowflakes -->
    <div class="snowflakes" id="snowflakes"></div>
    
    <!-- Ice Particles -->
    <div class="ice-particles" id="iceParticles"></div>
    
    <!-- Main Card -->
    <div class="card">
        <div class="pill">✅ Tour Code Found</div>
        <h1>Tour Available</h1>
        <div class="tour-code">{{ $tour_code }}</div>
        <p>This tour code is valid and associated with a booking.</p>
        
        @if($booking)
        <div class="booking-info">
            <h3>Booking Details</h3>
            <div class="info-row">
                <span class="info-label">Booking ID:</span>
                <span class="info-value">#{{ $booking->id }}</span>
            </div>
            @if($booking->tour_final_link)
            <div class="info-row">
                <span class="info-label">Tour Link:</span>
                <span class="info-value">
                    <a href="{{ $booking->tour_final_link }}" target="_blank">
                        View Tour
                    </a>
                </span>
            </div>
            @endif
            @if($booking->status)
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            </div>
            @endif
            @if($booking->full_address)
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $booking->full_address }}</span>
            </div>
            @endif
        </div>
        @endif
        
        <a href="{{ route('qr.welcome') }}" class="btn">← Back to Home</a>
    </div>

    <script>
        // Create Christmas lights
        function createChristmasLights() {
            const lightsContainer = document.getElementById('christmasLights');
            const colors = ['#16a34a', '#22c55e', '#10b981', '#06b6d4', '#ffffff'];
            
            for (let i = 0; i < 30; i++) {
                const light = document.createElement('div');
                light.className = 'light';
                light.style.left = Math.random() * 100 + '%';
                light.style.top = Math.random() * 100 + '%';
                light.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                light.style.animationDelay = Math.random() * 2 + 's';
                light.style.animationDuration = (Math.random() * 1 + 1.5) + 's';
                lightsContainer.appendChild(light);
            }
        }
        
        // Create snowflakes
        function createSnowflakes() {
            const snowflakesContainer = document.getElementById('snowflakes');
            const snowflakeSymbols = ['❄', '❅', '❆', '✻', '✼', '❋'];
            
            for (let i = 0; i < 60; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.textContent = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.animationDuration = (Math.random() * 3 + 2) + 's';
                snowflake.style.animationDelay = Math.random() * 2 + 's';
                snowflake.style.opacity = Math.random() * 0.5 + 0.5;
                snowflakesContainer.appendChild(snowflake);
            }
        }
        
        // Create ice particles
        function createIceParticles() {
            const particlesContainer = document.getElementById('iceParticles');
            
            for (let i = 0; i < 40; i++) {
                const particle = document.createElement('div');
                particle.className = 'ice-particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 5 + 3) + 's';
                particle.style.animationDelay = Math.random() * 2 + 's';
                particle.style.width = (Math.random() * 3 + 2) + 'px';
                particle.style.height = particle.style.width;
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize effects
        createChristmasLights();
        createSnowflakes();
        createIceParticles();
        
        // Add interactive mouse effect
        document.addEventListener('mousemove', (e) => {
            const card = document.querySelector('.card');
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
        });
        
        document.addEventListener('mouseleave', () => {
            const card = document.querySelector('.card');
            card.style.transform = '';
        });
    </script>
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
