@extends('frontend.layouts.base', ['title' => 'Gloom - Photography Portfolio Template'])

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('content')
    <div class="preloader-bg"></div>
    <div id="preloader">
        <div id="preloader-status">
            <div class="preloader-position loader"> <span></span> </div>
        </div>
    </div>

    <div class="progress-wrap cursor-pointer">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>

    <div class="cursor js-cursor"></div>

    <div class="social-ico-block">
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-youtube"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-tiktok"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-flickr"></i></a>
    </div>

    <section id="home" data-scroll-index="0" class="section-padding">
        <div id="viewer-wrapper">
            <div class="sidebar-menu" id="sidebarMenu">
                <h3 class="wow">PROP PIK Virtual Tour</h3>
                <nav class="scene-list">
                    <a href="#" class="scene-list-item active" data-scene-id="scene1">Lobby Area</a>
                    <a href="#" class="scene-list-item" data-scene-id="scene2">Extended Lobby</a>
                    <a href="#" class="scene-list-item" data-scene-id="scene3">Office Area</a>
                    <a href="#" class="scene-list-item" data-scene-id="scene4">Office Desk</a>
                </nav>
            </div>
            <button id="closeSidebar" class="toggle-btn">&times;</button>
            <button id="openSidebar" class="toggle-btn">&#9776;</button>
            <div id="panorama"></div>
            <div id="scene-thumbnails"></div>
        </div>
        <a class="viewer-logo" href="https://www.proppik.com" target="_blank" rel="noopener">
            <img src="{{ asset('frontend/images/logo.png') }}" alt="PROP PIK logo">
        </a>
    </section>

    <section class="section-padding cta-login" id="login" data-scroll-index="6">
        <div class="container">
            <div class="row align-items-center py-2 gap-2">
                <div class="col-lg-6 text-center text-lg-start">
                    <h6 class="wow" data-splitting>Ready to get started?</h6>
                    <h1 class="wow" data-splitting>Get Your Virtual Tour</h1>
                    <p class="mb-0">Share your details and we'll get in touch to plan your virtual tour with PROP PIK.</p>
                </div>
                <div class="col-lg-5 m-0 m-sm-3">
                    <form class="pricing-form-card" action="{{ route('frontend.setup') }}" method="get">
                        <div class="col-md-12 form-group">
                            <input type="text" name="prefillName" placeholder="Full Name *" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <input type="text" name="prefillPhone" pattern="[0-9]{10}" placeholder="Phone *" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Get Virtual Tour</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="process section-padding section-padding-top" id="benefits" data-scroll-index="1">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb-45 text-center">
                    <h6 class="wow" data-splitting>Benefit of</h6>
                    <h1 class="wow" data-splitting>PROP PIK</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="process-area">
                        <div class="process-item wow fadeInLeft" data-wow-delay=".2s">
                            <div class="process-step"> <span>01</span> </div>
                            <div class="process-content">
                                <h4 class="title">We Visit</h4>
                                <p class="desc">Our team comes to your location and captures high-quality photos of your property.</p>
                            </div>
                        </div>
                        <div class="process-item wow fadeInLeft" data-wow-delay=".4s">
                            <div class="process-step"> <span>02</span> </div>
                            <div class="process-content">
                                <h4 class="title">We Create</h4>
                                <p class="desc">PROP PIK software automatically processes the images to build a seamless 360° virtual tour.</p>
                            </div>
                        </div>
                        <div class="process-item wow fadeInLeft" data-wow-delay=".6s">
                            <div class="process-step"> <span>03</span> </div>
                            <div class="process-content">
                                <h4 class="title">You Showcase</h4>
                                <p class="desc">Share and showcase your property online with an immersive virtual experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding section-padding-top section-padding-bottom" id="portfolio" data-scroll-index="2">
        <div class="container">
            <div class="row mb-45">
                <div class="col-md-4">
                    <h6 class="wow" data-splitting>Capture the Moment</h6>
                    <h1 class="wow" data-splitting>Portfolio</h1>
                </div>
                <div class="col-md-7 offset-md-1 mt-45">
                    <p class="wow fadeInUp" data-wow-delay=".6s">Discover my professional services including photography, videography, retouching, aerials, lighting, and grading — crafted to capture your moments with precision and creativity.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="image-card">
                        <a href="#">
                            <img src="{{ asset('frontend/images/1.jpg') }}" alt="">
                            <div class="overlay"><p>View Image</p></div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="image-card">
                        <a href="#">
                            <img src="{{ asset('frontend/images/2.jpg') }}" alt="">
                            <div class="overlay"><p>View Image</p></div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="image-card">
                        <a href="#">
                            <img src="{{ asset('frontend/images/3.jpg') }}" alt="">
                            <div class="overlay"><p>View Image</p></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding section-padding-bottom" id="pricing" data-scroll-index="3">
        <div class="container">
            <div class="row mb-45">
                <div class="col-md-4">
                    <h6 class="wow" data-splitting>Simple & Transparent</h6>
                    <h1 class="wow" data-splitting>PROP PIK Pricing</h1>
                </div>
                <div class="col-md-7 offset-md-1 mt-45">
                    <p class="wow fadeInUp" data-wow-delay=".6s">Everything you need to capture, process, and publish immersive property tours with zero setup stress.</p>
                </div>
            </div>
From The Blog            <div class="row align-items-center gy-4">
                <div class="col-lg-5 wow fadeInUp" data-wow-delay=".2s">
                    <div class="pricing-card">
                        <div class="pricing-left">
                            <small>Get PROP PIK at</small>
                            <h2>₹599</h2>
                            <small>per month*</small>
                        </div>
                        <div class="pricing-right">
                            <h3>PROP PIK Virtual Tour</h3>
                            <p>Create a virtual tour of your property with PROP PIK.</p>
                            <ul class="pricing-benefits">
                                <li>On-site capture by our experts</li>
                                <li>Unlimited property tours</li>
                            </ul>
                            <a class="btn btn-primary mt-20" href="{{ route('frontend.setup') }}">Get PROP PIK Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 ms-lg-auto wow fadeInUp" data-wow-delay=".4s">
                    <div class="pricing-form-card h-100" id="pricing-contact-form">
                        <h5>Talk to the PROP PIK Team</h5>
                        <p class="mb-4">Share your details and we’ll get in touch to plan your virtual tour within 24 hours.</p>
                        <form method="post" class="contact__form" action="mail.php">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success contact__msg" style="display: none" role="alert"> Your message was sent successfully. </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input name="name" type="text" placeholder="Full Name *" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <input name="email" type="email" placeholder="Email Address *" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <input name="phone" type="text" placeholder="Phone *" required>
                                </div>
                                <div class="col-md-12 form-group">
                                    <input name="subject" type="text" placeholder="Property Location / Project Name *" required>
                                </div>
                                <div class="col-md-12 form-group">
                                    <textarea name="message" id="message" cols="30" rows="4" placeholder="Tell us about the space you'd like to showcase *" required></textarea>
                                </div>
                                <div class="col-md-12">
                                    <a class="btn btn-primary">Request a Callback</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding section-padding-top section-padding-bottom bg-light" id="blog" data-scroll-index="4">
        <div class="container">
            <div class="row mb-45">
                <div class="col-md-4">
                    <h6 class="wow" data-splitting>Latest Insights</h6>
                    <h1 class="wow" data-splitting>From The Blog</h1>
                </div>
                <div class="col-md-7 offset-md-1 mt-45">
                    <p class="wow fadeInUp" data-wow-delay=".6s">Stories, tutorials, and updates on how PROP PIK captures immersive spaces and builds compelling property experiences.</p>
                </div>
            </div>
            <div class="row g-4" id="blog-grid">
                <div class="col-12">
                    <div class="image-card loading">
                        <div class="overlay"><p>Loading latest posts…</p></div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a class="btn btn-outline-primary" href="https://darkred-rook-441483.hostingersite.com/protfolio/" target="_blank" rel="noopener">View All Posts</a>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" data-scroll-index="5" class="testimonials">
        <div class="background bg-img bg-fixed section-padding section-padding-top section-padding-bottom" data-overlay-dark="5" data-background="{{ asset('frontend/images/demo.jpg') }}">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-30">
                        <h4 class="wow" data-splitting>Let’s create a virtual tour of your property together.</h4>
                        <div class="btn-wrap mt-30 text-left wow fadeInUp" data-wow-delay=".6s">
                            <div class="btn-link"><a class="white" href="mailto:hello@pROP PIK.com">hello@pROP PIK.com</a><span class="btn-block color3 animation-bounce"></span></div>
                        </div>
                    </div>
                    <div class="col-md-5 offset-md-2">
                        <div class="testimonials-box">
                            <h5>What Are Clients Saying?</h5>
                            <div class="owl-carousel owl-theme">
                                <div class="item">
                                    <p>PROP PIK is a great way to showcase your property. It's easy to use and looks great.</p>
                                    <span class="quote"><img src="{{ asset('frontend/images/quot.png') }}" alt="" loading="lazy"></span>
                                    <div class="info">
                                        <div class="author-img"> <img src="{{ asset('frontend/images/team/1.jpg') }}" alt="" loading="lazy"></div>
                                        <div class="cont"><h6>Emily Brown</h6> <span>Customer</span></div>
                                    </div>
                                </div>
                                <div class="item">
                                    <p>PROP PIK is a great way to showcase your property. It's easy to use and looks great.</p>
                                    <span class="quote"><img src="{{ asset('frontend/images/quot.png') }}" alt="" loading="lazy"></span>
                                    <div class="info">
                                        <div class="author-img"> <img src="{{ asset('frontend/images/team/2.jpg') }}" alt="" loading="lazy"></div>
                                        <div class="cont"><h6>Jason White</h6> <span>Customer</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="scrolling scrolling-ticker">
        <div class="wrapper">
            <div class="content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Capture</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Create</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Showcase</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Explore</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Scan</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Stitch</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Publish</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">View</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Experience</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Transform</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Present</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Share</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Visualize</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Engage</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Impress</span>
            </div>
            <div class="content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Capture</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Create</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Showcase</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Explore</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Scan</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Stitch</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Publish</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">View</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Experience</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Transform</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Present</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Share</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Visualize</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Engage</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Impress</span>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery-migrate-3.5.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/YouTubePopUp.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.isotope.v3.0.2.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/smooth-scroll.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/wow.js') }}"></script>
    <script>
        // Ensure owl carousel is loaded before custom.js
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.owlCarousel === 'undefined') {
            console.error('Owl Carousel plugin is not loaded!');
        }
    </script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>

    <script>
        const scenes = {
            scene1: { type: 'equirectangular', panorama: '{{ asset('frontend/images/1.jpg') }}', title: 'Lobby', hfov: 110 },
            scene2: { type: 'equirectangular', panorama: '{{ asset('frontend/images/2.jpg') }}', title: 'Extended Lobby', hfov: 110 },
            scene3: { type: 'equirectangular', panorama: '{{ asset('frontend/images/3.jpg') }}', title: 'Office Area', hfov: 110 },
            scene4: { type: 'equirectangular', panorama: '{{ asset('frontend/images/4.jpg') }}', title: 'Office Desk', hfov: 110 },
        };
        const sceneOrder = ['scene1', 'scene2', 'scene3', 'scene4'];
        let currentSceneIndex = 0;
        let timer; const interval = 10000;
        const viewer = pannellum.viewer('panorama', { default: { firstScene: 'scene1', autoLoad: true, showZoomCtrl: true, showFullscreenCtrl: true, autoRotate: -1.5, hfov: 110 }, scenes });
        let gyroEnabled = false; let gyroControl;
        const enableGyro = () => {
            if (gyroEnabled || !viewer.startOrientation || !viewer.stopOrientation) return;
            const start = () => { try { viewer.startOrientation(); gyroEnabled = true; if (gyroControl) gyroControl.classList.add('active'); } catch (err) { console.warn('Motion sensors unavailable', err); } };
            if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission().then(response => { if (response === 'granted') start(); }).catch(err => console.warn('Motion permission denied', err));
            } else { start(); }
        };
        const disableGyro = () => { if (!gyroEnabled || !viewer.stopOrientation) return; viewer.stopOrientation(); gyroEnabled = false; if (gyroControl) gyroControl.classList.remove('active'); };
        if (viewer.addCustomControl && viewer.startOrientation && viewer.stopOrientation) {
            gyroControl = viewer.addCustomControl({ cssClass: 'pnlm-gyro-toggle', tooltip: 'Toggle motion control', clickHandler: () => { if (!gyroEnabled) { enableGyro(); } else { disableGyro(); } } });
        }
        let fullscreenControl;
        const getFullscreenTarget = () => typeof viewer.getContainer === 'function' ? viewer.getContainer() : document.getElementById('panorama');
        const isFullscreen = () => { if (typeof viewer.isFullscreen === 'function') { try { return viewer.isFullscreen(); } catch (err) { console.warn('Unable to determine fullscreen state', err); } } return !!(document.fullscreenElement || document.webkitFullscreenElement); };
        const fallbackEnterFullscreen = () => { const target = getFullscreenTarget(); if (!target) return; if (target.requestFullscreen) { return target.requestFullscreen(); } if (target.webkitRequestFullscreen) { return target.webkitRequestFullscreen(); } };
        const fallbackExitFullscreen = () => { if (document.exitFullscreen) { return document.exitFullscreen(); } if (document.webkitExitFullscreen) { return document.webkitExitFullscreen(); } };
        const enterFullscreen = () => { const logBlocked = err => console.warn('Fullscreen request blocked', err); if (typeof viewer.enterFullscreen === 'function') { try { const result = viewer.enterFullscreen(); if (result && typeof result.catch === 'function') { result.catch(err => { logBlocked(err); fallbackEnterFullscreen(); }); } return result; } catch (err) { logBlocked(err); } } return fallbackEnterFullscreen(); };
        const exitFullscreen = () => { const logBlocked = err => console.warn('Exit fullscreen blocked', err); if (typeof viewer.exitFullscreen === 'function') { try { const result = viewer.exitFullscreen(); if (result && typeof result.catch === 'function') { result.catch(err => { logBlocked(err); fallbackExitFullscreen(); }); } return result; } catch (err) { logBlocked(err); } } return fallbackExitFullscreen(); };
        const toggleFullscreen = () => { if (isFullscreen()) { exitFullscreen(); } else { enterFullscreen(); } };
        if (viewer.addCustomControl) {
            const fullscreenSupported = Boolean(document.fullscreenEnabled || document.webkitFullscreenEnabled || typeof viewer.enterFullscreen === 'function' || (getFullscreenTarget() && (getFullscreenTarget().requestFullscreen || getFullscreenTarget().webkitRequestFullscreen)));
            if (fullscreenSupported) {
                fullscreenControl = viewer.addCustomControl({ cssClass: 'pnlm-fullscreen-toggle', tooltip: 'Toggle fullscreen', clickHandler: () => toggleFullscreen() });
                ['fullscreenchange', 'webkitfullscreenchange'].forEach(eventName => { document.addEventListener(eventName, () => { if (!fullscreenControl) return; if (isFullscreen()) { fullscreenControl.classList.add('active'); } else { fullscreenControl.classList.remove('active'); } }); });
            }
        }
        const sidebar = document.getElementById('sidebarMenu'); const openBtn = document.getElementById('openSidebar'); const closeBtn = document.getElementById('closeSidebar');
        function hideSidebar() { sidebar.classList.add('hidden'); openBtn.style.display = 'flex'; closeBtn.style.display = 'none'; }
        function showSidebar() { sidebar.classList.remove('hidden'); openBtn.style.display = 'none'; closeBtn.style.display = 'flex'; }
        function loadScene(id) { viewer.loadScene(id); document.querySelectorAll('.scene-list-item').forEach(el => el.classList.remove('active')); document.querySelectorAll('.thumb').forEach(el => el.classList.remove('active')); const listItem = document.querySelector(`.scene-list-item[data-scene-id="${id}"]`); if (listItem) listItem.classList.add('active'); const thumb = document.querySelector(`#scene-thumbnails [data-scene-id="${id}"]`); if (thumb) thumb.classList.add('active'); currentSceneIndex = sceneOrder.indexOf(id); resetTimer(); }
        function nextScene() { currentSceneIndex = (currentSceneIndex + 1) % sceneOrder.length; loadScene(sceneOrder[currentSceneIndex]); }
        function resetTimer() { clearInterval(timer); timer = setInterval(nextScene, interval); }
        document.querySelectorAll('.scene-list-item').forEach(item => { item.addEventListener('click', e => { e.preventDefault(); loadScene(item.dataset.sceneId); if (window.innerWidth <= 768) hideSidebar(); }); });
        closeBtn.addEventListener('click', hideSidebar); openBtn.addEventListener('click', showSidebar);
        document.addEventListener('DOMContentLoaded', () => {
            const thumbContainer = document.getElementById('scene-thumbnails');
            sceneOrder.forEach(id => { const scene = scenes[id]; const thumb = document.createElement('div'); thumb.className = 'thumb'; thumb.setAttribute('data-scene-id', id); const img = document.createElement('img'); img.src = scene.panorama; img.alt = scene.title; const label = document.createElement('div'); label.textContent = scene.title; thumb.appendChild(img); thumb.appendChild(label); thumb.addEventListener('click', () => loadScene(id)); thumbContainer.appendChild(thumb); });
            const firstThumb = document.querySelector('.thumb'); if (firstThumb) firstThumb.classList.add('active');
            resetTimer(); if (window.innerWidth <= 768) { hideSidebar(); } else { showSidebar(); }
            enableGyro(); enterFullscreen();
        });
        viewer.on('mousedown', () => { clearInterval(timer); viewer.stopAutoRotate(); });
        viewer.on('mouseup', () => { viewer.startAutoRotate(-1.5); resetTimer(); });
        viewer.on('touchstart', () => { clearInterval(timer); viewer.stopAutoRotate(); });
        viewer.on('touchend', () => { viewer.startAutoRotate(-1.5); resetTimer(); });
    </script>
@endsection