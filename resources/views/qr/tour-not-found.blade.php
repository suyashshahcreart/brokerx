<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Tour Not Found - PROP PIK</title>
    <link rel="icon" type="image/png" href="https://www.proppik.com/assets/logo/fevi.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        :root {
            --primary-blue: #1e3a8a;
            --primary-blue-dark: #1e40af;
            --primary-blue-light: #3b82f6;
            --secondary-cyan: #06b6d4;
            --accent-gold: #fbbf24;
            --error-red: #ef4444;
            --text-white: #ffffff;
            --text-light: #f0f9ff;
            --text-muted: #cbd5e1;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e3a8a 30%, #1e40af 50%, #1e293b 70%, #0f172a 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
        }
        
        @media (min-width: 992px) {
            html, body {
                overflow: hidden;
            }
        }
        
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-white);
            position: relative;
        }
        
        @media (min-width: 992px) {
            body {
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                overflow: hidden;
            }
        }
        
        @media (max-width: 991.98px) {
            body {
                padding: 12px;
            }
        }
        
        /* Winter & Christmas Background Effects */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .particles {
                opacity: 0.5;
            }
            
            .christmas-lights {
                opacity: 0.4;
            }
        }
        
        /* Snowflakes */
        .snowflake {
            position: absolute;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1em;
            font-family: Arial, sans-serif;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
            animation: snowfall linear infinite;
            top: -10px;
        }
        
        @keyframes snowfall {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) translateX(var(--drift)) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Twinkling Christmas Lights */
        .christmas-lights {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
            overflow: hidden;
        }
        
        .light {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: twinkle 2s ease-in-out infinite;
            box-shadow: 0 0 10px currentColor, 0 0 20px currentColor;
        }
        
        @keyframes twinkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        /* Floating Ice Particles */
        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(200, 230, 255, 0.6);
            border-radius: 50%;
            animation: float 20s linear infinite;
            box-shadow: 0 0 6px rgba(200, 230, 255, 0.8);
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        .main-container {
            max-width: 1600px;
            width: 95%;
            position: relative;
            z-index: 10;
            height: 100%;
        }
        
        @media (min-width: 992px) {
            .main-container {
                height: 100vh;
            }
        }
        
        .col-logo,
        .col-contact,
        .col-main {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
        }
        
        @media (max-width: 991.98px) {
            .col-logo,
            .col-contact,
            .col-main {
                height: auto;
                justify-content: flex-start;
            }
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 0;
            background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.2) 0%, 
                rgba(30, 64, 175, 0.15) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(6, 182, 212, 0.1);
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .contact-section-wrapper {
            padding: 32px 24px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .sidebar-contact-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .sidebar-contact-card {
            background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.15) 0%, 
                rgba(30, 64, 175, 0.1) 100%);
            border-radius: 12px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            text-align: left;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2), 0 0 20px rgba(6, 182, 212, 0.1);
        }
        
        .sidebar-contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.15);
            border-color: rgba(6, 182, 212, 0.3);
            background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.2) 0%, 
                rgba(30, 64, 175, 0.15) 100%);
        }
        
        .sidebar-contact-card h4 {
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sidebar-contact-card > p {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .sidebar-contact-card a {
            color: var(--secondary-cyan);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }
        
        .sidebar-contact-card a:hover {
            color: var(--primary-blue-light);
            text-shadow: 0 0 8px rgba(6, 182, 212, 0.5);
        }
        
        .sidebar-contact-description {
            color: var(--text-muted) !important;
            font-size: 0.8rem !important;
            line-height: 1.5 !important;
            margin-top: 4px !important;
            margin-bottom: 0 !important;
            font-weight: 400 !important;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 16px;
            transition: transform 0.3s;
        }
        
        .logo-svg {
            height: 60px;
            width: auto;
            max-width: 100%;
            filter: brightness(0) invert(1);
            transition: all 0.3s;
        }
        
        .logo-container:hover .logo-svg {
            filter: brightness(0) invert(1) drop-shadow(0 0 15px rgba(6, 182, 212, 0.8));
            transform: scale(1.05);
        }
        
        .tagline {
            color: var(--text-light);
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            line-height: 1.5;
            text-align: center;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card {
            background: linear-gradient(135deg, 
                rgba(239, 68, 68, 0.15) 0%, 
                rgba(220, 38, 38, 0.1) 50%, 
                rgba(15, 23, 42, 0.25) 100%);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 24px;
            padding: 32px 36px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 40px rgba(239, 68, 68, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--error-red) 0%, 
                #dc2626 50%, 
                var(--error-red) 100%);
        }
        
        .card-content {
            overflow-y: auto;
            flex: 1;
            padding-right: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            background: linear-gradient(135deg, 
                rgba(239, 68, 68, 0.2) 0%, 
                rgba(220, 38, 38, 0.15) 100%);
            color: #fca5a5;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            backdrop-filter: blur(10px);
        }
        
        h1 {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 14px;
            color: var(--text-white);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        
        .tour-code-display {
            font-family: 'Inter', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fca5a5;
            margin-bottom: 24px;
            padding: 14px 24px;
            background: linear-gradient(135deg, 
                rgba(239, 68, 68, 0.12) 0%, 
                rgba(220, 38, 38, 0.08) 100%);
            border-radius: 12px;
            border: 1px solid rgba(239, 68, 68, 0.25);
            display: inline-block;
            letter-spacing: 1.5px;
        }
        
        .message-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--text-light);
            margin-bottom: 18px;
            font-weight: 400;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 24px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            min-height: 48px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue-light) 0%, var(--secondary-cyan) 100%);
            color: var(--text-white);
            border: none;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.35);
            color: var(--text-white);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: var(--text-white);
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 991.98px) {
            html, body {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
            }
            
            .main-container {
                height: auto;
                min-height: 100vh;
                padding: 12px 0;
            }
            
            .col-logo,
            .col-contact,
            .col-main {
                height: auto;
                justify-content: flex-start;
            }
            
            .logo-section {
                padding: 24px 16px;
                height: auto;
                min-height: auto;
                justify-content: flex-start;
                border-radius: 16px;
            }
            
            .logo-container {
                margin-bottom: 12px;
            }
            
            .logo-svg {
                height: 45px;
                max-width: 200px;
            }
            
            .tagline {
                font-size: 0.8rem;
                margin-bottom: 12px;
                line-height: 1.4;
            }
            
            .contact-section-wrapper {
                height: auto;
                min-height: auto;
                padding: 20px 16px;
                justify-content: flex-start;
            }
            
            .sidebar-contact-section {
                gap: 12px;
            }
            
            .sidebar-contact-card {
                padding: 14px;
                border-radius: 10px;
            }
            
            .sidebar-contact-card h4 {
                font-size: 0.85rem;
                margin-bottom: 6px;
            }
            
            .sidebar-contact-card > p {
                font-size: 0.8rem;
                margin-bottom: 6px;
            }
            
            .sidebar-contact-description {
                font-size: 0.7rem !important;
                line-height: 1.4 !important;
            }
            
            .main-content {
                height: auto;
                justify-content: flex-start;
                width: 100%;
            }
            
            .card {
                height: auto;
                min-height: auto;
                padding: 24px 16px;
                justify-content: flex-start;
                border-radius: 16px;
            }
            
            .card-content {
                overflow-y: visible;
                padding-right: 0;
                justify-content: flex-start;
            }
            
            .status-badge {
                padding: 6px 14px;
                font-size: 0.8rem;
                margin-bottom: 12px;
                border-radius: 50px;
            }
            
            h1 {
                font-size: 1.4rem;
                margin-bottom: 12px;
                line-height: 1.3;
            }
            
            .tour-code-display {
                font-size: 1rem;
                padding: 10px 16px;
                margin-bottom: 12px;
                border-radius: 10px;
                letter-spacing: 1px;
            }
            
            .message-text {
                font-size: 0.85rem;
                line-height: 1.6;
                margin-bottom: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
                margin-top: 16px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                padding: 12px 24px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
        }
    </style>
</head>
<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" data-tour-code="{{ $tour_code }}" data-page-type="tour_code" style="display:none;"></div>
    <script>
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    
    <!-- Winter & Christmas Effects -->
    <div class="particles" id="particles"></div>
    <div class="christmas-lights" id="christmasLights"></div>
    
    <div class="container-fluid main-container d-flex align-items-center">
        <div class="container">
            <div class="row g-0 g-lg-3 h-100 align-items-stretch">
                <!-- Logo Section - 25% on desktop, full width on mobile (Order: 1) -->
                <div class="col-12 col-lg-3 col-logo mb-3 mb-lg-0 order-1 order-lg-1">
                    <div class="logo-section card h-100">
                        <div class="logo-container">
                            <img src="https://www.proppik.com/assets/logo/logo.svg" alt="PROP PIK" class="logo-svg" />
                        </div>
                        <div class="tagline mb-4">Global Web Virtual Reality experiences</div>
                    </div>
                </div>
                
                <!-- Main Content Section - 50% on desktop, full width on mobile (Order: 2 on mobile, 3 on desktop) -->
                <div class="col-12 col-lg-6 col-main mb-3 mb-lg-0 order-2 order-lg-3">
                    <div class="logo-section card h-100">
                        <div class="text-center">
                            <div class="card-content">
                                <div class="status-badge" style="margin: 0 auto;">
                                    <span>❌</span>
                                    <span>Tour Code Not Found</span>
                                </div>
                                
                                <h1>Invalid Tour Code</h1>
                                
                                <div class="tour-code-display">{{ $tour_code }}</div>
                                
                                <p class="message-text">
                                    Sorry, the tour code you entered does not exist in our system.
                                </p>
                                <p class="message-text">
                                    Please check the code and try again, or contact support if you believe this is an error.
                                </p>
                                
                                <div class="action-buttons">
                                    <div class="row g-2 align-items-stretch">
                                        <div class="col-12 col-sm-6 d-flex">
                                            <a href="https://www.proppik.com/" class="btn w-100 btn-primary d-flex align-items-center justify-content-center" target="_blank" rel="noopener">
                                                <span>🏠</span>
                                                <span>Visit PROP PIK</span>
                                            </a>
                                        </div>
                                        <div class="col-12 col-sm-6 d-flex">
                                            <a href="{{ route('qr.welcome') }}" class="btn w-100 btn-secondary d-flex align-items-center justify-content-center">
                                                <span>←</span>
                                                <span>Back to Home</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Section - 25% on desktop, full width on mobile (Order: 3 on mobile, 2 on desktop) -->
                <div class="col-12 col-lg-3 col-logo order-3 order-lg-2">
                    <div class="logo-section card h-100">
                        <div class="sidebar-contact-section">
                            <div class="sidebar-contact-card">
                                <h4>🌐 Visit Our Website</h4>
                                <p>
                                    <a href="https://www.proppik.com/" target="_blank" rel="noopener">
                                        www.proppik.com
                                    </a>
                                </p>
                                <p class="sidebar-contact-description">
                                    Explore our services and view examples of our virtual tours.
                                </p>
                            </div>
                            
                            <div class="sidebar-contact-card">
                                <h4>📧 Email Us</h4>
                                <p>
                                    <a href="mailto:contact@proppik.com">contact@proppik.com</a>
                                </p>
                                <p class="sidebar-contact-description">
                                    Have questions? We'd be happy to help.
                                </p>
                            </div>
                            
                            <div class="sidebar-contact-card">
                                <h4>📞 Call Us</h4>
                                <p>
                                    <a href="tel:+919898363026">+91 98 98 36 30 26</a>
                                </p>
                                <p class="sidebar-contact-description">
                                    Available Monday–Saturday, 9:00 AM – 6:00 PM (IST)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Create snowflakes
        function createSnowflakes() {
            const particlesContainer = document.getElementById('particles');
            const snowflakeSymbols = ['❄', '❅', '❆', '✻', '✼', '✽'];
            const isMobile = window.innerWidth <= 768;
            const count = isMobile ? 15 : 30;
            
            for (let i = 0; i < count; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.textContent = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.fontSize = (Math.random() * 10 + 10) + 'px';
                snowflake.style.opacity = Math.random() * 0.5 + 0.5;
                snowflake.style.setProperty('--drift', (Math.random() * 200 - 100) + 'px');
                snowflake.style.animationDuration = (Math.random() * 10 + 10) + 's';
                snowflake.style.animationDelay = Math.random() * 5 + 's';
                particlesContainer.appendChild(snowflake);
            }
        }
        
        // Create floating ice particles
        function createIceParticles() {
            const particlesContainer = document.getElementById('particles');
            const isMobile = window.innerWidth <= 768;
            const count = isMobile ? 10 : 20;
            
            for (let i = 0; i < count; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 15 + 15) + 's';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particlesContainer.appendChild(particle);
            }
        }
        
        // Create twinkling Christmas lights
        function createChristmasLights() {
            const lightsContainer = document.getElementById('christmasLights');
            const colors = ['#ff0000', '#00ff00', '#ffff00', '#ff00ff', '#00ffff', '#ff8800', '#ffffff'];
            const isMobile = window.innerWidth <= 768;
            
            // Create lights along the top
            const topCount = isMobile ? 12 : 25;
            for (let i = 0; i < topCount; i++) {
                const light = document.createElement('div');
                light.className = 'light';
                light.style.left = (i * 4) + '%';
                light.style.top = Math.random() * 20 + '%';
                light.style.color = colors[Math.floor(Math.random() * colors.length)];
                light.style.animationDelay = Math.random() * 2 + 's';
                light.style.animationDuration = (Math.random() * 1.5 + 1) + 's';
                lightsContainer.appendChild(light);
            }
            
            // Create lights along the sides
            const sideCount = isMobile ? 8 : 15;
            for (let i = 0; i < sideCount; i++) {
                const light = document.createElement('div');
                light.className = 'light';
                light.style.left = Math.random() < 0.5 ? '2%' : '98%';
                light.style.top = (i * 6) + '%';
                light.style.color = colors[Math.floor(Math.random() * colors.length)];
                light.style.animationDelay = Math.random() * 2 + 's';
                light.style.animationDuration = (Math.random() * 1.5 + 1) + 's';
                lightsContainer.appendChild(light);
            }
        }
        
        // Initialize all winter & Christmas effects
        createSnowflakes();
        createIceParticles();
        createChristmasLights();
        
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
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="{{ url('/js/qr-location-tracker.js') }}"></script>
</body>
</html>
