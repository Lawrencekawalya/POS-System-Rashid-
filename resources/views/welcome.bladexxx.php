<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Scent of Elegance | Luxury Perfumes</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|montserrat:300,400,500,600" rel="stylesheet" />
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(145deg, #0b0c1e 0%, #1a1b2f 100%);
            overflow-x: hidden;
            color: white;
        }

        /* Animated background particles */
        .bg-particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(212, 175, 55, 0.2);
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
            animation: float-particle 15s infinite linear;
        }

        @keyframes float-particle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Luxury gradient overlays */
        .bg-gradient-orb {
            position: absolute;
            width: 80vw;
            height: 80vw;
            max-width: 800px;
            max-height: 800px;
            background: radial-gradient(circle at 30% 30%, rgba(212, 175, 55, 0.15), transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .orb-1 {
            top: -20%;
            right: -10%;
        }

        .orb-2 {
            bottom: -20%;
            left: -10%;
            background: radial-gradient(circle at 70% 70%, rgba(156, 126, 88, 0.15), transparent 70%);
        }

        /* Header navigation */
        .nav-container {
            width: 100%;
            max-width: 1280px;
            margin-top: 2rem;
            margin-bottom: 0rem;
            position: relative;
            z-index: 10;
        }

        .nav-links {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .nav-link {
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 30px;
            transition: all 0.3s ease;
            background: rgba(10, 10, 20, 0.3);
            backdrop-filter: blur(10px);
        }

        .nav-link:hover {
            border-color: rgba(212, 175, 55, 0.8);
            color: white;
            background: rgba(212, 175, 55, 0.1);
            transform: translateY(-2px);
        }

        /* Main container */
        .main-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            flex-grow: 1;
            padding: 2rem 1rem; /* Added padding for mobile breathing room */
            z-index: 10;
        }

        .card-container {
            display: flex;
            flex-direction: column-reverse; /* Logo on top for mobile */
            width: 100%;
            max-width: 1100px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            border-radius: 20px;
            overflow: hidden; /* Ensures the sharp corners of children don't poke out */
        }

        @media (min-width: 1024px) {
            .card-container {
                flex-direction: row; /* Side by side for desktop */
                height: 800px; /* Fixed height for a consistent luxury look */
            }
            
            .contact-card, .logo-card {
                flex: 1; /* Equal width */
                height: 100%;
            }
        }

        /* LEFT CARD - Contact Information (Luxury Redesign) */
        .contact-card {
            flex: 1;
            padding: 3rem;
            background: rgba(20, 18, 28, 0.75);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 0 0 20px 20px;
            box-shadow: 
                0 30px 50px -30px rgba(0, 0, 0, 0.8),
                inset 0 1px 1px rgba(255, 255, 255, 0.1);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 1024px) {
            .contact-card {
                border-radius: 20px 0 0 20px;
            }
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.8), transparent);
        }

        .card-glow {
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
            border-radius: 50%;
            filter: blur(40px);
            pointer-events: none;
        }

        .glow-1 {
            top: -50px;
            right: -50px;
        }

        .glow-2 {
            bottom: -50px;
            left: -50px;
        }

        /* Shop name styling */
        .shop-name {
            margin-bottom: 2.5rem;
            position: relative;
        }

        .shop-name h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.7rem;
            font-weight: 700;
            letter-spacing: 4px;
            background: linear-gradient(135deg, #fff 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .shop-tagline {
            font-size: 0.9rem;
            letter-spacing: 3px;
            color: rgba(212, 175, 55, 0.7);
            text-transform: uppercase;
            font-weight: 300;
            position: relative;
            padding-left: 2rem;
            text-transform: capitalize; /* Changed from uppercase */
            font-style: italic; /* Optional: adds a touch of class */
        }

        .shop-tagline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 1.5rem;
            height: 1px;
            background: rgba(212, 175, 55, 0.4);
        }

        /* Section titles */
        .section-title {
            font-size: 0.7rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(212, 175, 55, 0.8);
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        /* Contact items */
        .contact-items {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .contact-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4af37;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-icon {
            background: rgba(212, 175, 55, 0.2);
            border-color: #d4af37;
            transform: scale(1.05);
        }

        .contact-details {
            flex: 1;
        }

        .contact-label {
            font-size: 0.7rem;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .contact-value {
            font-size: 1rem;
            color: white;
            font-weight: 400;
            margin-bottom: 0.15rem;
        }

        .contact-sub {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Phone numbers grid */
        .phone-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .phone-item {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .phone-item:hover {
            border-color: rgba(212, 175, 55, 0.4);
            background: rgba(212, 175, 55, 0.05);
            transform: translateY(-2px);
        }

        .phone-number {
            font-size: 1.1rem;
            font-weight: 500;
            color: white;
            margin-bottom: 0.25rem;
        }

        .phone-type {
            font-size: 0.7rem;
            color: rgba(212, 175, 55, 0.7);
            letter-spacing: 1px;
        }

        /* Email styling */
        .email-group {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 12px;
            padding: 1rem;
        }

        .email-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .email-row:not(:last-child) {
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }

        .email-address {
            color: white;
            font-size: 0.95rem;
        }

        .email-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            background: rgba(212, 175, 55, 0.15);
            border-radius: 20px;
            color: #d4af37;
            letter-spacing: 0.5px;
        }

        /* Address styling */
        .address-block {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 12px;
            padding: 1rem;
        }

        .address-line {
            color: white;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .address-city {
            color: rgba(212, 175, 55, 0.7);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        /* Social media icons */
        .social-section {
            margin-top: 2rem;
        }

        .social-grid {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .social-icon {
            width: 44px;
            height: 44px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4af37;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-icon:hover {
            background: #d4af37;
            color: #0b0c1e;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        }

        /* Business hours */
        .hours-container {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .hours-item {
            text-align: center;
        }

        .hours-days {
            color: rgba(212, 175, 55, 0.7);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .hours-time {
            color: white;
        }

        /* RIGHT CARD - Logo Card (Luxury Redesign) */
        .logo-card {
            position: relative;
            width: 100%;
            min-height: 450px;
            background: linear-gradient(145deg, #1a1b2f, #0f1120);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 20px 20px 0 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
        }

        @media (min-width: 1024px) {
            .logo-card {
                border-radius: 0 20px 20px 0;
            }
        }

        /* Animated background for logo card */
        .logo-bg-animation {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 30%, rgba(212, 175, 55, 0.15), transparent 70%);
            animation: pulse-glow 4s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        .floating-particles {
            position: absolute;
            inset: 0;
        }

        .float-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #d4af37;
            border-radius: 50%;
            filter: blur(2px);
            opacity: 0.3;
        }

        .float-particle:nth-child(1) { top: 20%; left: 30%; animation: float 8s infinite; }
        .float-particle:nth-child(2) { top: 60%; right: 25%; animation: float 10s infinite; }
        .float-particle:nth-child(3) { bottom: 30%; left: 40%; animation: float 12s infinite; }
        .float-particle:nth-child(4) { top: 40%; right: 40%; animation: float 9s infinite; }
        .float-particle:nth-child(5) { bottom: 20%; right: 30%; animation: float 11s infinite; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, -20px); }
        }

        /* Logo container */
        .logo-container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 2rem;
        }

        .logo-image-wrapper {
            width: 400px;
            height: 400px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .logo-ring {
            position: absolute;
            inset: -10px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            animation: spin 12s linear infinite;
        }

        .logo-ring::before {
            content: '';
            position: absolute;
            inset: -5px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 50%;
            border-top-color: #d4af37;
            animation: spin 8s linear infinite reverse;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(212, 175, 55, 0.5);
            box-shadow: 
                0 0 30px rgba(212, 175, 55, 0.3),
                inset 0 0 20px rgba(212, 175, 55, 0.2);
            transition: transform 0.5s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .brand-initials {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 6px;
            margin-bottom: 0.5rem;
        }

        .brand-year {
            font-size: 0.8rem;
            letter-spacing: 8px;
            color: rgba(212, 175, 55, 0.5);
            text-transform: uppercase;
        }

        /* Decorative lines */
        .decor-line {
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
            margin: 1rem auto;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .logo-card {
                min-height: 300px; /* Reduces the height for mobile so it's tighter */
            }
            .contact-card {
                padding: 2rem;
            }
            
            .phone-grid {
                grid-template-columns: 1fr;
            }
            
            .shop-name h1 {
                font-size: 2.2rem;
            }
            
            .logo-image-wrapper {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="bg-particles">
        <div class="particle" style="left: 10%; width: 1px; height: 1px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 30%; width: 3px; height: 3px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 50%; width: 2px; height: 2px; animation-delay: 4s;"></div>
        <div class="particle" style="left: 70%; width: 4px; height: 4px; animation-delay: 6s;"></div>
        <div class="particle" style="left: 90%; width: 6px; height: 4px; animation-delay: 8s;"></div>
    </div>
    
    <div class="bg-gradient-orb orb-1"></div>
    <div class="bg-gradient-orb orb-2"></div>

    <!-- Navigation -->
    <div class="nav-container">
        <nav class="nav-links">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                @else
                    <a href="{{ route('pos.index') }}" class="nav-link">POS</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="nav-link">Log in</a>
            @endauth
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="card-container">
            
            <!-- LEFT CARD: Contact Information (Luxury Edition) -->
            <div class="contact-card">
                <div class="card-glow glow-1"></div>
                <div class="card-glow glow-2"></div>
                
                <!-- Shop Name -->
                <div class="shop-name">
                    <h1>SCENT OF ELEGANCE</h1>
                    <div class="shop-tagline">Blume to confidence</div>
                </div>

                <!-- Contact Details -->
                <div class="contact-items">
                    <!-- Phone Numbers Section -->
                    <div>
                        <div class="section-title">CONNECT WITH US</div>
                        <div class="phone-grid">
                            <div class="phone-item">
                                <i class="fas fa-phone-alt" style="color: #d4af37; margin-bottom: 0.5rem;"></i>
                                <div class="phone-number">+1 (555) 123-4567</div>
                                <div class="phone-type">MAIN & ORDERS</div>
                            </div>
                            <div class="phone-item">
                                <i class="fab fa-whatsapp" style="color: #d4af37; margin-bottom: 0.5rem;"></i>
                                <div class="phone-number">+1 (555) 987-6543</div>
                                <div class="phone-type">WHATSAPP / SUPPORT</div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Section -->
                    <div>
                        <div class="section-title">EMAIL</div>
                        <div class="email-group">
                            <div class="email-row">
                                <span class="email-address">hello@scentofelegance.com</span>
                                <span class="email-badge">PRIMARY</span>
                            </div>
                            <div class="email-row">
                                <span class="email-address">support@scentofelegance.com</span>
                                <span class="email-badge">SUPPORT</span>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div>
                        <div class="section-title">BOUTIQUE</div>
                        <div class="address-block">
                            <div class="address-line"><i class="fas fa-map-marker-alt" style="color: #d4af37; margin-right: 0.5rem;"></i> 123 Luxury Lane</div>
                            <div class="address-city">Beverly Hills, CA 90210</div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Icons -->
                <div class="social-section">
                    <div class="section-title">FOLLOW THE JOURNEY</div>
                    <div class="social-grid">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-x"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Business Hours -->
                <div class="hours-container">
                    <div class="hours-item">
                        <div class="hours-days">MON - FRI</div>
                        <div class="hours-time">9:00 - 20:00</div>
                    </div>
                    <div class="hours-item">
                        <div class="hours-days">SAT - SUN</div>
                        <div class="hours-time">10:00 - 18:00</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT CARD: Logo Only (Luxury Edition) -->
            <div class="logo-card">
                <div class="logo-bg-animation"></div>
                
                <!-- Floating particles -->
                <div class="floating-particles">
                    <div class="particle" style="left: 10%; width: 1px; height: 1px; animation-delay: 0s;"></div>
                    <div class="particle" style="left: 30%; width: 3px; height: 3px; animation-delay: 2s;"></div>
                    <div class="particle" style="left: 50%; width: 2px; height: 2px; animation-delay: 4s;"></div>
                    <div class="particle" style="left: 70%; width: 4px; height: 4px; animation-delay: 6s;"></div>
                    <div class="particle" style="left: 90%; width: 6px; height: 4px; animation-delay: 8s;"></div>
                </div>

                <!-- Logo Container -->
                <div class="logo-container">
                    <div class="logo-image-wrapper">
                        <div class="logo-ring"></div>
                        <img src="{{ asset('images/logo-1.jpeg') }}" alt="Scent of Elegance" class="logo-image">
                    </div>
                    
                    <div class="decor-line"></div>
                    <div class="brand-initials">AR</div>
                    <div class="brand-year">EST. 2026</div>
                    <div class="decor-line"></div>
                    
                    <div style="margin-top: 1.5rem; font-size: 0.8rem; color: rgba(212,175,55,0.4); letter-spacing: 2px;">
                        LUXURY SCENTS FOR THE DISCERNING NOSE
                    </div>
                </div>

                <!-- Decorative overlay -->
                <div class="pointer-events-none absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-e-lg bg-gradient-to-br from-white/5 via-transparent to-transparent"></div>
            </div>
        </div>
    </div>

    @if (Route::has('login'))
        <div style="height: 3rem;"></div>
    @endif
</body>

</html>