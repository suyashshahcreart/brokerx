@extends('frontend.layouts.base', ['title' => 'PROP PIK'])

@section('css')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Photo Sphere Viewer CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@photo-sphere-viewer/core@5/index.css">
    <!-- Magnific Popup CSS (theme dependency) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">

    <!-- Import Map for ES Modules (required by proppik/assets/js/main.js) -->
    <script type="importmap">
        {
          "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.152.2/build/three.module.js",
            "@photo-sphere-viewer/core": "https://cdn.jsdelivr.net/npm/@photo-sphere-viewer/core@5/index.module.js",
            "@photo-sphere-viewer/autorotate-plugin": "https://cdn.jsdelivr.net/npm/@photo-sphere-viewer/autorotate-plugin@5/index.module.js"
          }
        }
    </script>
@endsection

@section('content')
    <!-- Slider Container (New Theme) -->
    <section id="home" class="slider-container position-relative">
        <h1 class="demo-title text-center w-100">Drive growth using PROP PIK Web Virtual Reality powered by AI</h1>

        <!-- Swiper -->
        <div class="swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide position-relative">
                    <div class="slide-title">Babylon Club</div>
                    <div id="viewer1" class="viewer-container"></div>
                    <a href="https://htl.proppik.com/babylonahm/" target="_blank" rel="noopener" class="btn btn-primary slider-btn">View Tour</a>
                </div>
                <div class="swiper-slide position-relative">
                    <div class="slide-title">Kisna Canteen</div>
                    <div id="viewer2" class="viewer-container"></div>
                    <a href="https://rs.proppik.com/kisnaahm/" target="_blank" rel="noopener" class="btn btn-primary slider-btn">View Tour</a>
                </div>
                <div class="swiper-slide position-relative">
                    <div class="slide-title">Kohinoor Resort &amp; Spa</div>
                    <div id="viewer3" class="viewer-container"></div>
                    <a href="https://htl.proppik.com/kohinoor/" target="_blank" rel="noopener" class="btn btn-primary slider-btn">View Tour</a>
        </div>
    </div>

            <!-- Navigation buttons -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
    </div>

        <!-- Floating Section -->
        <div class="floating-section bg-white rounded-4 shadow-lg p-3 p-md-4">
            <button class="btn-close-floating position-absolute top-0 end-0 m-2" aria-label="Close">
                <span>&times;</span>
            </button>
            <div class="promo-slider-content">
                <h2 class="promo-main-title mb-2">Create Stunning Web Virtual Reality Experiences</h2>
                <p class="promo-description mb-3">Show your property as if the customer is truly inside it — from any device, anywhere.</p>
                <div class="promo-cta mb-3 p-2 rounded gap-2">
                    <span class="promo-arrow">➡️</span>
                    <span class="promo-cta-text small">Schedule now and get your PROP PIK Web Virtual Reality powered by AI.</span>
                </div>
                <div class="promo-buttons mt-3">
                    <a href="{{ route('frontend.setup') }}" class="btn btn-primary">Get Started</a>
    </div>
            </div>
        </div>
    </section>

    <!-- About Section (New Theme) -->
    <section id="about" class="about-section py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="about-text-content">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">About PROP PIK Global</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>Create Immersive</span><br>
                            <span>Web <span class="about-title-highlight">Virtual Reality</span></span>
                        </h2>
                        <p class="about-description mb-4">
                            We help real estate, hospitality, architecture, interiors, education, and businesses of all kinds
                            showcase their properties with exceptional clarity and impact.Enable your audience to explore and
                            experience your spaces from anywhere in the world through seamless and intelligent Web Virtual Reality.
                        </p>
                        <p class="about-description mb-4 fw-semibold">
                            Engage better, build trust faster, and grow your business with next-generation immersive experiences.
                        </p>
                        <a href="{{ route('frontend.setup') }}" class="btn btn-primary btn-lg">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-image-collage position-relative">
                        <div class="about-image-main">
                            <img src="{{ asset('proppik/assets/images/1.jpg') }}" alt="Main Image" class="img-fluid rounded">
                        </div>
                        <div class="about-image-overlay about-image-1 position-absolute">
                            <img src="{{ asset('proppik/assets/images/2.jpg') }}" alt="Overlay Image 1" class="img-fluid rounded">
                            <div class="about-decorative-shape shape-yellow"></div>
                        </div>
                        <div class="about-profile-icon profile-1">
                            <img src="{{ asset('proppik/assets/images/4.jpg') }}" alt="Profile 1" class="img-fluid rounded-circle">
                            <div class="profile-hover-image">
                                <img src="{{ asset('proppik/assets/images/4.jpg') }}" alt="Profile 1 Large" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="about-profile-icon profile-2 position-absolute">
                            <img src="{{ asset('proppik/assets/images/5.jpg') }}" alt="Profile 2" class="img-fluid rounded-circle">
                            <div class="profile-hover-image">
                                <img src="{{ asset('proppik/assets/images/5.jpg') }}" alt="Profile 2 Large" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="about-star star-1 position-absolute">⭐</div>
                        <div class="about-star star-2 position-absolute">⭐</div>
                        <div class="about-star star-3 position-absolute">⭐</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section (New Theme) -->
    <section id="why-choose-us" class="why-choose-us-section py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <!-- Left Side: Content with Video -->
                <div class="col-lg-6">
                    <div class="about-text-content">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">Transparent Process, Proven Results</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>Why Choose</span>
                            <span class="about-title-highlight">PROP PIK?</span>
                        </h2>
                        <p class="about-description mb-4">
                            We blend AI, innovation, and expertise to showcase your space with unmatched impact.Our professional team delivers high-quality Web Virtual Reality experiences that help you grow sales, increase bookings, and build trust effortlessly.With seamless execution and global standards, PROP PIK ensures powerful results every time.
                        </p>
                        <div class="why-choose-video-wrapper position-relative">
                            <div class="why-choose-video-circle">
                                <img src="{{ asset('proppik/assets/images/babylon/1.jpg') }}" alt="Professional Team" class="img-fluid rounded-circle">
                                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="video-play-btn popup-youtube d-none" aria-label="Play Video">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </a>
                                <div class="video-hover-ring"></div>
                            </div>
                            <div class="why-choose-decorative-shapes">
                                <div class="decorative-shape shape-1"></div>
                                <div class="decorative-shape shape-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Side: Feature Cards -->
                <div class="col-lg-6">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="guarantee-card">
                                <div class="guarantee-header">
                                    <div class="guarantee-icon">%</div>
                                </div>
                                <h3 class="guarantee-title">Affordable Pricing</h3>
                                <p class="guarantee-text">No hidden fees.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="guarantee-card">
                                <div class="guarantee-header">
                                    <div class="guarantee-icon">🔗</div>
                                </div>
                                <h3 class="guarantee-title">Easy Sharing</h3>
                                <p class="guarantee-text">Works on everywhere.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="guarantee-card">
                                <div class="guarantee-header">
                                    <div class="guarantee-icon">📸</div>
            </div>
                                <h3 class="guarantee-title">Team of PROP PIK Experts</h3>
                                <p class="guarantee-text">Experienced trained team.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="guarantee-card">
                                <div class="guarantee-header">
                                    <div class="guarantee-icon">🤖</div>
                                </div>
                                <h3 class="guarantee-title">AI-Boosted Editing</h3>
                                <p class="guarantee-text">Trusted technology.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="guarantee-card guarantee-card-full">
                                <div class="guarantee-header">
                                    <div class="guarantee-icon">🏢</div>
                                </div>
                                <h3 class="guarantee-title">Perfect for All Property Types</h3>
                                <p class="guarantee-text">Homes, shops, hotels, restaurants, salons, resorts, offices — PROP PIK captures them all.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section (New Theme) -->
    <section id="how-it-works" class="how-it-works-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-text-content">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Works</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>How It</span>
                            <span class="about-title-highlight">Works?</span>
                        </h2>
                        <p class="about-description mb-4">
                            Creating immersive Web Virtual Reality experiences is easier than you think.
                        </p>
                </div>
                </div>
            </div>

            <div class="shutter-accordion-wrapper">
                <div class="shutter-accordion" id="shutterAccordion">
                    <!-- Step 1 -->
                    <div class="shutter-panel active" data-step="1">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="collapsed-title">Sign Up</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-centers">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="content-title">1. Sign Up &amp; Add Your Property Details</h2>
                            <p class="content-description">
                                Create an account and enter details like property size, type, and location. Our intuitive platform makes it easy to get started in minutes.
                            </p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="shutter-panel" data-step="2">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span class="collapsed-title">Choose Package</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-center">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h2 class="content-title">2. Choose Your Package &amp; Pay Online</h2>
                            <p class="content-description">
                                Transparent pricing with no hidden fees. Secure payment processing.
                            </p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="shutter-panel" data-step="3">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="collapsed-title">Schedule</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-center">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h2 class="content-title">3. Schedule the Photoshoot</h2>
                            <p class="content-description">
                                Choose a convenient date &amp; time. Our photographer will be assigned instantly. Flexible scheduling to fit your timeline.
                            </p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="shutter-panel" data-step="4">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="collapsed-title">Photography</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-center">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h2 class="content-title">4. Professional PROP PIK Team</h2>
                            <p class="content-description">
                                Our trained PROP PIK team visits your location and shoots the entire property. Professional equipment ensures high-quality results.
                            </p>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="shutter-panel" data-step="5">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M3.636 5.636l.707.707m0 7.071H2.5M17.5 14h1.866M6.5 18H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2.5" />
                            </svg>
                            <span class="collapsed-title">AI Enhancement</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-center">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M3.636 5.636l.707.707m0 7.071H2.5M17.5 14h1.866M6.5 18H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2.5" />
                                </svg>
                            </div>
                            <h2 class="content-title">5. AI-Enhanced Virtual Tour Creation</h2>
                            <p class="content-description">
                                PROP PIKS stitches all photos, enhances them using AI tools, and creates a stunning virtual walkthrough. Seamless navigation and immersive experience.
                            </p>
                </div>
                    </div>
                    <!-- Step 6 -->
                    <div class="shutter-panel" data-step="6">
                        <div class="collapsed-content">
                            <svg class="collapsed-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="collapsed-title">Get Link</span>
                        </div>
                        <div class="panel-content">
                            <div class="mb-4 d-flex justify-content-center">
                                <svg class="content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h2 class="content-title">6. Final Virtual Tour Link Delivered</h2>
                            <p class="content-description">
                                You get a shareable link — ready to use on WhatsApp, social media, your website, and listings. Start sharing your virtual tour immediately.
                            </p>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Panorama Gallery Section (New Theme) -->
    <section id="gallery" class="panorama-gallery-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-text-content mb-5">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Gallery</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>Explore Our</span>
                            <span class="about-title-highlight">Virtual Tours</span>
                        </h2>
                        <p class="about-description mb-0">
                            Browse through immersive Web Virtual Reality creations built using our AI-powered platform.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="gallery-filters d-flex flex-wrap justify-content-center gap-3">
                        <button class="btn btn-filter active" data-filter="all">All</button>
                        <button class="btn btn-filter" data-filter="hospitality">Hospitality</button>
                        <button class="btn btn-filter" data-filter="commercial">Commercial</button>
                        <button class="btn btn-filter" data-filter="residential">Residential</button>
                    </div>
                </div>
            </div>

            <!-- Filtered Tours -->
            <div class="row g-4 mb-5" id="filtered-tours">
                <div class="col-md-6 col-lg-4 filtered-tour-item" data-category="hospitality">
                    <a href="https://htl.proppik.com/babylonahm/" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                            <img src="{{ asset('proppik/assets/images/babylon/1.jpg') }}" alt="PROP PIK Virtual Tour" class="panorama-image img-fluid w-100">
                            <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                                <h4 class="h5 mb-2 fw-bold">Babylon Club — Ahmedabad</h4>
                                <p class="mb-0 small opacity-75">Resort &amp; Club</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 filtered-tour-item" data-category="commercial">
                    <a href="https://rs.proppik.com/kisnaahm/" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                            <img src="{{ asset('proppik/assets/images/kisan-canteen/1.jpg') }}" alt="PROP PIK Virtual Tour" class="panorama-image img-fluid w-100">
                            <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                                <h4 class="h5 mb-2 fw-bold">Kisna Canteen — Ahmedabad</h4>
                                <p class="mb-0 small opacity-75">Restaurant</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-lg-4 filtered-tour-item" data-category="residential">
                    <a href="https://re.proppik.com/avadhahm/" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                            <img src="{{ asset('proppik/assets/images/avadhahm/1.jpg') }}" alt="PROP PIK Virtual Tour" class="panorama-image img-fluid w-100">
                            <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                                <h4 class="h5 mb-2 fw-bold">Avadh Green - Ahmedabad</h4>
                                <p class="mb-0 small opacity-75">Residential Flat</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 filtered-tour-item" data-category="commercial">
                    <a href="https://re.proppik.com/officeahm/" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                            <img src="{{ asset('proppik/assets/images/officeahm/1.jpg') }}" alt="PROP PIK Virtual Tour" class="panorama-image img-fluid w-100">
                            <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                                <h4 class="h5 mb-2 fw-bold">Mondeal Heights - Ahmedabad</h4>
                                <p class="mb-0 small opacity-75">Commercial Office</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 filtered-tour-item" data-category="commercial">
                    <a href="https://re.proppik.com/pragatya" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                            <img src="{{ asset('proppik/assets/images/pragatya/1.jpg') }}" alt="PROP PIK Virtual Tour" class="panorama-image img-fluid w-100">
                            <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                                <h4 class="h5 mb-2 fw-bold">Pragatyay - Ahmedabad</h4>
                                <p class="mb-0 small opacity-75">Commercial Office</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- View More Button as creative card (kept as placeholder until portfolio route exists) -->
                <div class="col-md-6 col-lg-4">
                    <a href="#" onclick="return false;">
                        <div class="blog-view-all-card h-100 d-flex align-items-center justify-content-center position-relative overflow-hidden rounded">
                            <div class="blog-view-all-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                            <div class="blog-view-all-content position-relative text-center p-4">
                                <svg class="blog-view-all-icon mb-3" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="5" cy="12" r="2" fill="currentColor" stroke="none"></circle>
                                    <circle cx="12" cy="12" r="2" fill="currentColor" stroke="none"></circle>
                                    <circle cx="19" cy="12" r="2" fill="currentColor" stroke="none"></circle>
                                </svg>
                                <p class="blog-view-all-text mb-4 text-white-50">See the full PROP PIK portfolio</p>
                                <span class="btn btn-light fw-bold">View More Portfolio</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Upcoming Tour Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="h4 mb-4 fw-bold text-center">Upcoming Tour</h3>
                </div>
            </div>
            <!-- Gallery Tiles -->
            <div class="row g-4" id="gallery-tiles">
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/temple.jpg') }}" alt="Temple" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Temple</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/dariy.jpg') }}" alt="Dairy" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Dairy</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/museum.jpg') }}" alt="Museum" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Museum</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/statue.jpg') }}" alt="Tallest Statue" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Tallest Statue</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/banquet.jpg') }}" alt="Banquet" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Banquet</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/factory.jpg') }}" alt="Factory" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Factory</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/water-park.jpg') }}" alt="Water Park" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Water Park</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 gallery-item">
                    <div class="panorama-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('proppik/assets/images/upcoming/mall.jpg') }}" alt="Mall" class="panorama-image img-fluid w-100">
                        <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                            <h4 class="h5 mb-2 fw-bold">Mall</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section (New Theme) -->
    <section id="testimonials" class="testimonials-section py-5">
        <div class="container">
            <div class="row align-items-start">
                <!-- Left Side: Static Content (40%) -->
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <div class="about-text-content">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Testimonials</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>What Our</span>
                            <span class="about-title-highlight">Clients Say</span>
                        </h2>
                        <p class="about-description mb-4">
                            See what our clients have to say about their experience with PROP PIK. We transcend expectations, creating bespoke virtual tour solutions for visionaries.
                        </p>
                    </div>

                    <!-- Statistics & Visual Element -->
                    <div class="testimonials-stats-box mt-5">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Happy Clients</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">98%</div>
                                    <div class="stat-label">Satisfaction Rate</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">1000+</div>
                                    <div class="stat-label">Virtual Tours</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">4.9★</div>
                                    <div class="stat-label">Average Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Scrolling Testimonials (60%) -->
                <div class="col-lg-7">
                    <div class="testimonials-scrolling-viewport">
                        <div class="row g-4">
                            <!-- Left Scrolling Column (Scrolls UP) -->
                            <div class="col-md-6 testimonials-scrolling-column testimonials-scroll-up d-none d-md-block">
                                <div class="testimonials-scroll-content">
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">PROP PIK has been responsive and professional throughout the entire virtual tour creation process. They supported us with our real estate listings and we are excited to see where it can take our business!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rajesh Kumar" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rajesh Kumar</h4>
                                                <p class="small text-muted mb-0">Real Estate Agent</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">I've been working with PROP PIK for a while now, and they're an absolute gem. Their platform is incredibly creative and technically sound. The virtual tours we create are stunning!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Priya Mehta" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Priya Mehta</h4>
                                                <p class="small text-muted mb-0">Architect, Design Studio</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Highly talented platform with extensive features that has vastly improved our property showcases this year. We have opted for the monthly subscription and would recommend to any real estate company.</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Anjali Sharma" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Anjali Sharma</h4>
                                                <p class="small text-muted mb-0">CEO, Property Solutions</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Duplicate set for seamless loop -->
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">PROP PIK has been responsive and professional throughout the entire virtual tour creation process. They supported us with our real estate listings and we are excited to see where it can take our business!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rajesh Kumar" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rajesh Kumar</h4>
                                                <p class="small text-muted mb-0">Real Estate Agent</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Scrolling Column (Scrolls DOWN) -->
                            <div class="col-md-6 testimonials-scrolling-column testimonials-scroll-down">
                                <div class="testimonials-scroll-content">
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Partnering with PROP PIK has reduced our marketing costs by 40%, boosted property viewings and client engagement, and helped us open doors to new markets!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rohit Patel" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rohit Patel</h4>
                                                <p class="small text-muted mb-0">CEO, Luxury Homes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Love the personal account relationship we have and we are made to feel like the best customer. The enthusiasm, cheerfulness, and speed of the team is awesome and the communication is always incredible!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Kavita Singh" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Kavita Singh</h4>
                                                <p class="small text-muted mb-0">Head of Marketing, Hotel Group</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">It's so refreshing to have a platform that breaks the stereotypical approach and works with their clients in a collaborative way. PROP PIK truly understands our needs.</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Vikram Desai" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Vikram Desai</h4>
                                                <p class="small text-muted mb-0">Creative Director, Art Gallery</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Duplicate set for seamless loop -->
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Partnering with PROP PIK has reduced our marketing costs by 40%, boosted property viewings and client engagement, and helped us open doors to new markets!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rohit Patel" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rohit Patel</h4>
                                                <p class="small text-muted mb-0">CEO, Luxury Homes</p>
                                            </div>
                                        </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section (New Theme) -->
    <section id="pricing" class="pricing-section py-5 d-none">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pricing-card-modern">
                        <div class="row g-0 h-100">
                            <div class="col-lg-5 pricing-left-section">
                                <div class="pricing-left-content">
                                    <div class="about-text-content">
                                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                                            <div class="about-headline-line"></div>
                                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Pricing</span>
                                        </div>
                                    </div>
                                    <h2 class="pricing-plan-name">Essential Plan</h2>
                                    <div class="pricing-price-large">₹599 <sup>*</sup></div>
                                    <a href="{{ route('frontend.setup') }}" class="btn btn-primary btn-lg">Book Your Virtual Tour Today</a>
                                </div>
                            </div>
                            <div class="col-lg-7 pricing-right-section">
                                <div class="pricing-right-content">
                                    <h3 class="pricing-included-title">What's Included</h3>
                                    <div class="pricing-features-list row">
                                        <div class="col-lg-6">
                                            <div class="pricing-feature-item">
                                                <div class="pricing-feature-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                                        <circle cx="12" cy="13" r="4" />
                                                    </svg>
                                                </div>
                                                <div class="pricing-feature-content">
                                                    <h4 class="pricing-feature-title">Panoramic Shoot</h4>
                                                    <p class="pricing-feature-desc">Professional 360° photography</p>
                    </div>
                </div>
            </div>
                                        <div class="col-lg-6">
                                            <div class="pricing-feature-item">
                                                <div class="pricing-feature-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                                    </svg>
                                                </div>
                                                <div class="pricing-feature-content">
                                                    <h4 class="pricing-feature-title">Shareable Link</h4>
                                                    <p class="pricing-feature-desc">Easy sharing across platforms</p>
                </div>
            </div>
        </div>
                                        <div class="col-lg-6">
                                            <div class="pricing-feature-item">
                                                <div class="pricing-feature-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M3.636 5.636l.707.707m0 7.071H2.5M17.5 14h1.866M6.5 18H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2.5" />
                                                    </svg>
                                                </div>
                                                <div class="pricing-feature-content">
                                                    <h4 class="pricing-feature-title">Photo Enhancement</h4>
                                                    <p class="pricing-feature-desc">AI-powered image enhancement</p>
                                                </div>
                        </div>
                    </div>
                                        <div class="col-lg-6">
                                            <div class="pricing-feature-item">
                                                <div class="pricing-feature-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                        <circle cx="12" cy="10" r="3" />
                                                    </svg>
                                                </div>
                                                <div class="pricing-feature-content">
                                                    <h4 class="pricing-feature-title">1 Year Hosting</h4>
                                                    <p class="pricing-feature-desc">Trusted technology</p>
                                                </div>
                                    </div>
                                </div>
                                        <div class="pricing-info-box">
                                            <p class="pricing-info-text">Perfect for all property types: Homes, shops, hotels, restaurants, salons, resorts, offices – PROP PIK captures them all.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section (New Theme) -->
    <section id="blog" class="blog-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-text-content mb-5">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Blog</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>Latest</span>
                            <span class="about-title-highlight">Insights</span>
                        </h2>
                        <p class="about-description">
                            Stay updated with the latest trends, tips, and insights about virtual tours and property marketing.
                        </p>
                    </div>
                </div>
            </div>
            <div id="blog-posts" class="row g-4">
                <div class="col-12 text-center text-muted">Loading latest posts...</div>
            </div>
        </div>
    </section>

    <!-- Contact Section (New Theme) -->
    <section id="contact" class="contact-section py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="about-text-content mb-5">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK CONTACT</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>Get In </span>
                            <span class="about-title-highlight">Touch</span>
                        </h2>
                        <p class="about-description">
                            Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                        </p>
                    </div>

                    <div class="contact-info">
                        <div class="contact-item-card">
                            <div class="contact-item-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4 class="contact-item-title mb-2">Address</h4>
                                <div class="contact-item-text mb-2 d-flex gap-1 flex-column">
                                    <div><strong>USA:</strong></div>
                                    <div>Phoenix, Arizona || Nashville, Tennessee</div>
                                </div>
                                <div class="contact-item-text mb-2 d-flex gap-1 flex-column">
                                    <div><strong>India:</strong></div>
                                    <div>Ahmedabad, Gujarat</div>
                                </div>
                            </div>
                        </div>
                        <div class="contact-item-card">
                            <div class="contact-item-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4 class="contact-item-title mb-2">Email</h4>
                                <p class="contact-item-text mb-0">contact@proppik.com</p>
                            </div>
                        </div>
                        <div class="contact-item-card">
                            <div class="contact-item-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4 class="contact-item-title mb-2">Phone</h4>
                                <p class="contact-item-text mb-0">+1 (623) 290-5830<br>+91 98 98 36 30 26</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="contact-form-wrapper">
                        <div class="contact-form-header mb-4">
                            <h3 class="contact-form-title mb-2">Talk to the PROP PIK Team</h3>
                            <p class="contact-form-subtitle">Share your details and we'll get in touch to plan your virtual tour within 24 hours.</p>
                        </div>
                        <form id="contactForm" class="contact-form">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="fullName" class="form-label-custom">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="fullName" name="fullName" placeholder="Enter your full name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="companyName" class="form-label-custom">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="companyName" name="companyName" placeholder="Enter your company name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contactPerson" class="form-label-custom">Contact Person Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control-custom" id="contactPerson" name="contactPerson" placeholder="1234567890" pattern="[0-9]{10}" maxlength="10" required>
                                        <small class="text-muted d-block mt-1">Enter 10-digit phone number</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="address" class="form-label-custom">Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="address" name="address" placeholder="Enter your complete address" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="requirement" class="form-label-custom">Requirement <span class="text-danger">*</span></label>
                                        <textarea class="form-control-custom" id="requirement" name="requirement" rows="3" style="resize: none;" placeholder="Describe your requirements in detail..." required></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1" type="checkbox" value="agree" id="agreeTerms" required>
                                        <label class="form-check-label small text-muted" for="agreeTerms">
                                            I agree to the <a href="{{ route('frontend.terms') }}" class="text-primary text-decoration-none">Terms &amp; Conditions</a> and <a href="{{ route('frontend.privacy-policy') }}" class="text-primary text-decoration-none">Privacy Policy</a>.
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="formMessage" class="alert d-none mb-3" role="alert"></div>
                                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 fw-bold position-relative">
                                        <span class="submit-text">Request a Callback</span>
                                        <span class="submit-loader d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Sending...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>
    </section>
@endsection

@section('script-bottom')
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- jQuery (required for Magnific Popup) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Magnific Popup JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

    <!-- New Theme JavaScript (Panorama viewers + slider interactions) -->
    <script type="module" src="{{ asset('proppik/assets/js/main.js') }}"></script>

    <script>
        async function loadRecentPosts() {
            const container = document.getElementById('blog-posts');
            if (!container) return;
            try {
                const response = await fetch('https://www.proppik.com/blog/wp-json/wp/v2/posts?per_page=5&_embed&fields=id,title,link,_embedded,date');
                const posts = await response.json();

                if (!Array.isArray(posts) || posts.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center text-muted">No posts found.</div>';
                    return;
                }

                const formatDate = (iso) => {
                    const d = new Date(iso);
                    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
                };

                let html = '';
                posts.forEach(post => {
                    const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || 'https://via.placeholder.com/640x360?text=PROP+PIK';
                    const date = post.date ? formatDate(post.date) : '';
                    const title = post.title?.rendered || 'Untitled';

                    html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="blog-card h-100 d-flex flex-column">
                                <div class="blog-image-wrapper position-relative overflow-hidden">
                                    <img src="${image}" alt="Blog Image" class="blog-image img-fluid w-100" loading="lazy">
                                    <div class="blog-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                                </div>
                                <div class="blog-content p-4 bg-white rounded-bottom d-flex flex-column flex-grow-1">
                                    <div class="blog-date small text-muted mb-2">${date}</div>
                                    <h3 class="blog-title h5 mb-2 fw-bold flex-grow-0">
                                        <a href="${post.link}" target="_blank" rel="noopener" class="text-decoration-none">${title}</a>
                                    </h3>
                                    <a href="${post.link}" target="_blank" rel="noopener" class="btn btn-primary btn-sm mt-3 align-self-start">Read More</a>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-view-all-card h-100 d-flex align-items-center justify-content-center position-relative overflow-hidden rounded">
                            <div class="blog-view-all-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                            <div class="blog-view-all-content position-relative text-center p-4">
                                <svg class="blog-view-all-icon mb-3" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p class="blog-view-all-text mb-4 text-white-50">Explore more articles and insights</p>
                                <a href="https://www.proppik.com/blog/" target="_blank" rel="noopener" class="btn btn-light fw-bold">View All Posts</a>
                            </div>
                        </div>
                    </div>
                `;

                container.innerHTML = html;
            } catch (error) {
                console.error('Error fetching posts:', error);
                container.innerHTML = '<div class="col-12 text-center text-danger">Unable to load posts right now.</div>';
            }
        }

        window.addEventListener('DOMContentLoaded', loadRecentPosts);
    </script>

    <script>
        // Home contact form is design-only for now (disable submit)
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('contactForm');
            if (!form) return;
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const msg = document.getElementById('formMessage');
                if (msg) {
                    msg.classList.remove('d-none', 'alert-danger');
                    msg.classList.add('alert-info');
                    msg.textContent = 'Form submission is disabled for now (design only).';
                }
            });
        });
    </script>
@endsection