<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Tour Coming Soon - PROP PIK</title>
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
            /* height: 100%; */
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
            /* backdrop-filter: blur(20px); */
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(6, 182, 212, 0.1);
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .contact-section-wrapper {
            /* background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.2) 0%, 
                rgba(30, 64, 175, 0.15) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; */
            padding: 32px 24px;
            /* backdrop-filter: blur(20px);
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(6, 182, 212, 0.1); */
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
                rgba(30, 58, 138, 0.25) 0%, 
                rgba(30, 64, 175, 0.2) 50%, 
                rgba(15, 23, 42, 0.25) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 32px 36px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 40px rgba(6, 182, 212, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            /* backdrop-filter: blur(20px); */
            position: relative;
            overflow: hidden;
            width: 100%;
            /* height: 100%; */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card-content {
            overflow-y: auto;
            flex: 1;
            padding-right: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .card-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        .card-content::-webkit-scrollbar-thumb {
            background: rgba(6, 182, 212, 0.3);
            border-radius: 10px;
        }
        
        .card-content::-webkit-scrollbar-thumb:hover {
            background: rgba(6, 182, 212, 0.5);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .content-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--primary-blue-light) 0%, 
                var(--secondary-cyan) 50%, 
                var(--primary-blue-light) 100%);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            background: linear-gradient(135deg, 
                rgba(251, 191, 36, 0.15) 0%, 
                rgba(245, 158, 11, 0.1) 100%);
            color: var(--accent-gold);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid rgba(251, 191, 36, 0.25);
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
            color: var(--secondary-cyan);
            margin-bottom: 24px;
            padding: 14px 24px;
            background: linear-gradient(135deg, 
                rgba(6, 182, 212, 0.12) 0%, 
                rgba(59, 130, 246, 0.08) 100%);
            border-radius: 12px;
            border: 1px solid rgba(6, 182, 212, 0.25);
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
        
        .status-info {
            background: linear-gradient(135deg, 
                rgba(251, 191, 36, 0.12) 0%, 
                rgba(245, 158, 11, 0.08) 100%);
            border-left: 3px solid var(--accent-gold);
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 18px;
            margin-top: 15px;
            border: 1px solid rgba(251, 191, 36, 0.15);
            backdrop-filter: blur(10px);
        }
        
        .status-info h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Syne', sans-serif;
        }
        
        .status-info p {
            color: var(--text-light);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .notification-section {
            background: linear-gradient(135deg, 
                rgba(6, 182, 212, 0.12) 0%, 
                rgba(59, 130, 246, 0.08) 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 18px;
            border: 1px solid rgba(6, 182, 212, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .notification-section {
            text-align: center;
        }
        
        .notification-section h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .notification-section > p {
            color: var(--text-light);
            margin-bottom: 16px;
            font-size: 0.95rem;
            line-height: 1.6;
            text-align: center;
        }
        
        .notification-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .notification-form .row {
            justify-content: center;
            width: 100%;
        }
        
        .notification-input {
            flex: 1;
            min-width: 180px;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-white);
            backdrop-filter: blur(10px);
        }
        
        .notification-input::placeholder {
            color: var(--text-muted);
        }
        
        .notification-input:focus {
            outline: none;
            border-color: var(--secondary-cyan);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15);
            background: rgba(255, 255, 255, 0.12);
            color: var(--text-white);
        }
        
        .notification-input:focus::placeholder {
            color: var(--text-muted);
        }
        
        /* Bootstrap validation styles */
        .notification-input.is-valid {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }
        
        .notification-input.is-valid:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        
        .notification-input.is-invalid {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        
        .notification-input.is-invalid:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }
        
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #ef4444;
        }
        
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            backdrop-filter: blur(10px);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.15) 100%);
            color: var(--text-white);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: var(--text-white);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .notification-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, var(--primary-blue-light) 0%, var(--secondary-cyan) 100%);
            color: var(--text-white);
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
        }
        
        .notification-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.35);
            color: var(--text-white);
        }
        
        .notification-btn:active {
            transform: translateY(0);
        }
        
        .notification-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .notification-success {
            display: none;
            padding: 12px 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: var(--text-white);
            border-radius: 10px;
            margin-top: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        
        .notification-error {
            padding: 12px 16px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: var(--text-white);
            border-radius: 10px;
            margin-top: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }
        
        .contact-section {
            display: none;
        }
        
        .main-info-section {
            grid-column: 1 / -1;
        }
        
        .notification-section {
            grid-column: 1 / -1;
        }
        
        .contact-card {
            background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.15) 0%, 
                rgba(30, 64, 175, 0.1) 100%);
            border-radius: 12px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            text-align: left;
            display: flex;
            flex-direction: column;
        }
        
        .contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.15);
            border-color: rgba(6, 182, 212, 0.3);
            background: linear-gradient(135deg, 
                rgba(30, 58, 138, 0.2) 0%, 
                rgba(30, 64, 175, 0.15) 100%);
        }
        
        .contact-card h4 {
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .contact-card > p {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 0.95rem;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .contact-card a {
            color: var(--secondary-cyan);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }
        
        .contact-card a:hover {
            color: var(--primary-blue-light);
            text-shadow: 0 0 8px rgba(6, 182, 212, 0.5);
        }
        
        .contact-description {
            color: var(--text-muted) !important;
            font-size: 0.85rem !important;
            line-height: 1.5 !important;
            margin-top: 4px !important;
            margin-bottom: 0 !important;
            font-weight: 400 !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .action-buttons .row {
            width: 100%;
        }
        
        .action-buttons .row > div {
            display: flex;
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
            height: 100%;
            width: 100%;
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
        
        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto 1fr;
            }
            
            .col-logo {
                grid-column: 1 / -1;
                height: auto;
            }
            
            .logo-section {
                height: auto;
                min-height: 200px;
            }
            
            .col-contact {
                grid-column: 1;
            }
            
            .col-main {
                grid-column: 2;
            }
            
            .col-contact,
            .col-main {
                height: 100%;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar-contact-card {
                padding: 16px;
            }
            
            .sidebar-contact-card h4 {
                font-size: 0.9rem;
            }
            
            .sidebar-contact-card > p {
                font-size: 0.85rem;
            }
            
            .sidebar-contact-description {
                font-size: 0.75rem !important;
            }
        }
        
        /* Bootstrap Mobile Responsive Styles */
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
            
            .status-info {
                margin-top: 12px;
                padding: 12px 14px;
                border-radius: 10px;
            }
            
            .status-info h3 {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }
            
            .status-info p {
                font-size: 0.8rem;
                line-height: 1.5;
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
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            
            .notification-section {
                padding: 16px;
                margin-bottom: 16px;
                border-radius: 12px;
            }
            
            .notification-section h3 {
                font-size: 1rem;
                margin-bottom: 8px;
            }
            
            .notification-section > p {
                font-size: 0.85rem;
                margin-bottom: 12px;
                line-height: 1.5;
            }
            
            .notification-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .notification-input,
            .notification-btn {
                width: 100%;
            }
            
            .notification-input {
                padding: 12px 14px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
            
            .notification-btn {
                padding: 12px 24px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
            
            .notification-success,
            .notification-error {
                padding: 10px 14px;
                font-size: 0.85rem;
                margin-top: 10px;
                border-radius: 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                padding: 12px 24px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                gap: 12px;
            }
            
            .logo-section {
                padding: 20px 14px;
            }
            
            .logo-svg {
                height: 60px;
                max-width: 180px;
            }
            
            .tagline {
                font-size: 0.75rem;
            }
            
            .contact-section-wrapper {
                padding: 18px 14px;
            }
            
            .sidebar-contact-card {
                padding: 12px;
            }
            
            .card {
                padding: 20px 14px;
            }
            
            h1 {
                font-size: 1.25rem;
            }
            
            .tour-code-display {
                font-size: 0.95rem;
                padding: 8px 14px;
            }
            
            .status-badge {
                padding: 5px 12px;
                font-size: 0.75rem;
            }
            
            .message-text {
                font-size: 0.8rem;
            }
            
            .notification-section {
                padding: 14px;
            }
            
            .notification-section h3 {
                font-size: 0.95rem;
            }
            
            .notification-section > p {
                font-size: 0.8rem;
            }
            
            .notification-input,
            .notification-btn {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 375px) {
            body {
                padding: 8px;
            }
            
            .container {
                gap: 10px;
            }
            
            .logo-section {
                padding: 18px 12px;
            }
            
            .card {
                padding: 18px 12px;
            }
            
            .contact-section-wrapper {
                padding: 16px 12px;
            }
            
            h1 {
                font-size: 1.15rem;
            }
        }
        
        @media (max-height: 900px) {
            .logo-section {
                margin-bottom: 16px;
            }
            
            .card {
                padding: 28px 32px;
            }
            
            h1 {
                font-size: 1.75rem;
                margin-bottom: 12px;
            }
            
            .tour-code-display {
                margin-bottom: 14px;
                padding: 10px 18px;
                font-size: 1.3rem;
            }
            
            .message-text {
                margin-bottom: 14px;
                font-size: 0.9rem;
            }
            
            .status-info {
                margin-bottom: 14px;
                padding: 12px 16px;
            }
            
            .status-info h3 {
                font-size: 0.95rem;
            }
            
            .status-info p {
                font-size: 0.9rem;
            }
            
            .notification-section {
                margin-bottom: 14px;
                padding: 18px;
            }
            
            .notification-section h3 {
                font-size: 1.1rem;
            }
            
            .contact-section {
                margin-bottom: 14px;
                gap: 12px;
            }
            
            .contact-card {
                padding: 16px;
            }
            
            .contact-card h4 {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }
            
            .contact-card > p {
                font-size: 0.9rem;
            }
            
            .contact-description {
                font-size: 0.8rem !important;
            }
        }
        
        @media (max-height: 750px) {
            .logo-section {
                margin-bottom: 12px;
            }
            
            .logo-svg {
                height: 60px;
            }
            
            .tagline {
                font-size: 0.85rem;
            }
            
            .card {
                padding: 24px 28px;
            }
            
            .status-badge {
                padding: 6px 16px;
                font-size: 0.8rem;
                margin-bottom: 14px;
            }
            
            h1 {
                font-size: 1.6rem;
                margin-bottom: 10px;
            }
            
            .tour-code-display {
                margin-bottom: 12px;
                padding: 8px 16px;
                font-size: 1.15rem;
            }
            
            .message-text {
                margin-bottom: 12px;
                font-size: 0.85rem;
            }
            
            .status-info {
                margin-bottom: 12px;
                padding: 10px 14px;
            }
            
            .notification-section {
                margin-bottom: 12px;
                padding: 16px;
            }
            
            .contact-section {
                margin-bottom: 12px;
                gap: 10px;
            }
            
            .contact-card {
                padding: 14px;
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
                        @if($booking && $booking->status)
                            <div class="sidebar-contact-card pt-4">
                                <h4>
                                    <span>📋</span>
                                    <span>Current Status: {{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                </h4>
                                <p>
                                    @if($booking->status === 'pending')
                                        Your booking is being processed and the virtual tour is in the planning stage.
                                    @elseif($booking->status === 'confirmed' || $booking->status === 'scheduled')
                                        The photoshoot has been scheduled and our team will visit the property soon.
                                    @elseif($booking->status === 'in_progress')
                                        Our team is currently working on creating your virtual tour experience.
                                    @elseif($booking->status === 'completed')
                                        The virtual tour creation is complete and going through final quality checks.
                                    @else
                                        We're working on this tour and it will be live soon. We'll notify you once it's ready!
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Main Content Section - 50% on desktop, full width on mobile (Order: 2 on mobile, 3 on desktop) -->
                <div class="col-12 col-lg-6 col-main mb-3 mb-lg-0 order-2 order-lg-3">
                    <div class="logo-section card h-100">
                        <div class="text-center">
                            <div class="card-content">
                                <div class="content-grid">
                                    <div class="main-info-section">
                                        <div class="status-badge">
                                            <span>⏳</span>
                                            <span>Tour Coming Soon</span>
                                        </div>
                                        
                                        <h1>We're Working on This Tour</h1>
                                        
                                        <div class="tour-code-display">{{ $tour_code }}</div>
                                        
                                        <p class="message-text">
                                            Thank you for your interest! Our team is currently working on creating an amazing virtual tour experience for this property. 
                                            The tour is not live yet, but we're putting the finishing touches to make it perfect for you.
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="notification-section">
                                    <h3>
                                        <span>🔔</span>
                                        <span>Get Notified When Tour Goes Live</span>
                                    </h3>
                                    <p>
                                        Enter your phone number and we'll notify you as soon as this tour is ready to view.
                                    </p>
                                    <form class="notification-form needs-validation" id="notificationForm" novalidate>
                                        <div class="row g-2 justify-content-center">
                                            <div class="col-12 col-sm-8 col-md-7 col-lg-8">
                                                <input 
                                                    type="tel" 
                                                    class="form-control notification-input" 
                                                    id="notificationInput"
                                                    placeholder="Enter your phone number (10 digits)"
                                                    pattern="[0-9]{10}"
                                                    maxlength="10"
                                                    required
                                                >
                                                <div class="invalid-feedback" id="phoneInvalidFeedback">
                                                    Please enter a valid 10-digit phone number.
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4 col-md-5 col-lg-4">
                                                <button type="submit" class="btn w-100 notification-btn">Notify Me</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="alert alert-danger d-none mt-2" id="notificationError" role="alert">
                                        <strong>❌ Error:</strong> <span id="errorMessage">Please enter a valid 10-digit phone number.</span>
                                    </div>
                                    <div class="alert alert-success d-none mt-2" id="notificationSuccess" role="alert">
                                        <strong>✅ Success!</strong> Thank you! We'll notify you when the tour is live.
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <div class="row g-2 align-items-stretch">
                                        <div class="col-12 col-sm-6 d-flex">
                                            <a href="https://dev.proppik.com" class="btn w-100 btn-primary d-flex align-items-center justify-content-center" target="_blank" rel="noopener">
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
                                    <a href="https://dev.proppik.com" target="_blank" rel="noopener">
                                        dev.proppik.com
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
        
        // Phone number validation function
        function validatePhoneNumber(phone) {
            // Remove any spaces, dashes, or other characters
            const cleaned = phone.replace(/\D/g, '');
            // Check if it's exactly 10 digits
            return /^[0-9]{10}$/.test(cleaned);
        }
        
        // Notification form handler with Bootstrap validation
        const notificationForm = document.getElementById('notificationForm');
        const notificationInput = document.getElementById('notificationInput');
        const successMsg = document.getElementById('notificationSuccess');
        const errorMsg = document.getElementById('notificationError');
        const errorMessage = document.getElementById('errorMessage');
        
        // Bootstrap validation - prevent form submission if invalid
        notificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove previous validation states
            notificationForm.classList.remove('was-validated');
            successMsg.classList.add('d-none');
            errorMsg.classList.add('d-none');
            
            const phoneNumber = notificationInput.value.trim().replace(/\D/g, '');
            const submitBtn = this.querySelector('.notification-btn');
            
            // Validate phone number
            if (!phoneNumber || !validatePhoneNumber(phoneNumber)) {
                // Show Bootstrap validation feedback
                notificationForm.classList.add('was-validated');
                notificationInput.classList.add('is-invalid');
                notificationInput.classList.remove('is-valid');
                notificationInput.focus();
                return false;
            }
            
            // Valid phone number - remove invalid class, add valid class
            notificationInput.classList.remove('is-invalid');
            notificationInput.classList.add('is-valid');
            
            // Disable button during submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            // Get tour code from URL
            const tourCode = '{{ $tour_code }}';
            
            // Construct the correct URL - use relative path for qr.proppik.com domain
            const saveNotificationUrl = '/save-notification';
            
            // Send AJAX request
            fetch(saveNotificationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tour_code: tourCode,
                    phone_number: phoneNumber
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Server error occurred');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    successMsg.classList.remove('d-none');
                    errorMsg.classList.add('d-none');
                    notificationInput.value = '';
                    notificationInput.classList.remove('is-valid', 'is-invalid');
                    notificationForm.classList.remove('was-validated');
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        successMsg.classList.add('d-none');
                    }, 5000);
                } else {
                    // Show error message
                    errorMessage.textContent = data.message || 'Failed to save notification. Please try again.';
                    errorMsg.classList.remove('d-none');
                    successMsg.classList.add('d-none');
                    notificationInput.classList.add('is-invalid');
                    notificationInput.classList.remove('is-valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = error.message || 'An error occurred. Please try again later.';
                errorMsg.classList.remove('d-none');
                successMsg.classList.add('d-none');
                notificationInput.classList.add('is-invalid');
                notificationInput.classList.remove('is-valid');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Notify Me';
            });
            
            return false;
        });
        
        // Format phone number input (only allow digits) and real-time validation
        notificationInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            this.value = this.value.replace(/\D/g, '');
            
            // Hide error messages when user starts typing
            errorMsg.classList.add('d-none');
            successMsg.classList.add('d-none');
            
            // Remove was-validated class to hide invalid-feedback until form is submitted
            notificationForm.classList.remove('was-validated');
            
            // Real-time validation feedback (only visual, not showing invalid-feedback)
            const phoneNumber = this.value.trim();
            if (phoneNumber.length > 0) {
                if (validatePhoneNumber(phoneNumber)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    // Only show invalid class if user has entered 10 digits but they're invalid
                    // Don't show invalid-feedback until form is submitted
                    if (phoneNumber.length === 10) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
        
        // Clear validation on blur if empty
        notificationInput.addEventListener('blur', function() {
            if (this.value.trim().length === 0) {
                this.classList.remove('is-valid', 'is-invalid');
                notificationForm.classList.remove('was-validated');
            }
        });
        
        // Ensure error messages are hidden on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                errorMsg.classList.add('d-none');
                successMsg.classList.add('d-none');
                notificationInput.classList.remove('is-valid', 'is-invalid');
                notificationForm.classList.remove('was-validated');
            });
        } else {
            // DOM already loaded
            errorMsg.classList.add('d-none');
            successMsg.classList.add('d-none');
            notificationInput.classList.remove('is-valid', 'is-invalid');
            notificationForm.classList.remove('was-validated');
        }
        
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
