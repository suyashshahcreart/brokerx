<!-- Frontend Footer (New Theme) -->
<!-- <footer class="footer bg-dark text-white py-5" id="footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-column">
                    <img src="{{ asset('proppik/assets/logo/w-logo.svg') }}" alt="PROP PIK" class="footer-logo mb-3" height="40">
                    <p class="text-white-50 mb-3">Global Web Virtual Reality experiences that let you see spaces like never before.</p>
                    <div class="social-links d-flex gap-3">
                        <a href="https://www.facebook.com/proppikglobal" target="_blank" class="text-white-50 text-decoration-none">Facebook</a>
                        <a href="https://www.instagram.com/proppikglobal" target="_blank" class="text-white-50 text-decoration-none">Instagram</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="row">
                    <div class="col-6">
                        <div class="footer-column">
                            <h3 class="h6 mb-3">Quick Links</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('frontend.index') }}#about" class="text-white-50 text-decoration-none">About Us</a></li>
                                <li class="mb-2"><a href="{{ route('frontend.index') }}#why-choose-us" class="text-white-50 text-decoration-none">Why Choose Us</a></li>
                                <li class="mb-2"><a href="{{ route('frontend.index') }}#how-it-works" class="text-white-50 text-decoration-none">How It Works</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="footer-column">
                            <h3 class="h6 mb-3">More</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('frontend.index') }}#gallery" class="text-white-50 text-decoration-none">Gallery</a></li>
                                <li class="mb-2"><a href="{{ route('frontend.contact') }}" class="text-white-50 text-decoration-none">Contact</a></li>
                                <li class="mb-2"><a href="{{ route('frontend.setup') }}" class="text-white-50 text-decoration-none">Book Now</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="footer-column">
                    <h3 class="h6 mb-3">Legal</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('frontend.privacy-policy') }}" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('frontend.refund-policy') }}" class="text-white-50 text-decoration-none">Refund Policy</a></li>
                        <li class="mb-2"><a href="{{ route('frontend.terms') }}" class="text-white-50 text-decoration-none">Terms and Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom text-center pt-4 border-top border-secondary">
            <p class="text-white-50 mb-0">&copy; {{ date('Y') }} PROP PIK Global. All rights reserved.</p>
        </div>
    </div>
</footer> -->

<!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-column">
                        <img src="{{ asset('proppik/assets/logo/w-logo.svg') }}" alt="PROP PIK" class="footer-logo mb-3" height="40">
                        <p class="text-white-50 mb-3">Global Web Virtual Reality experiences that let you see spaces like never before.</p>
                        <div class="social-links d-flex gap-2">
                            <a href="https://www.facebook.com/proppikglobal" target="_blank" class="social-link" aria-label="Facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1h3z" />
                                </svg>
                            </a>
                            <a href="https://www.instagram.com/proppikglobal" target="_blank" class="social-link" aria-label="Instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="row">
                        <div class="col-6">
                            <div class="footer-column">
                                <h3 class="h6 mb-3">Quick Links</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><a href="https://proppik.com/#about" class="text-white-50 text-decoration-none">About Us</a></li>
                                    <li class="mb-2"><a href="https://proppik.com/#why-choose-us" class="text-white-50 text-decoration-none">Why Choose Us</a></li>
                                    <li class="mb-2"><a href="https://proppik.com/#how-it-works" class="text-white-50 text-decoration-none">How It Works</a></li>
                                    @auth
                                        <li class="mb-2"><a href="{{ route('frontend.booking-dashboard') }}" class="text-white-50 text-decoration-none">My Bookings</a></li>
                                    @endauth
                                    @guest
                                        <li class="mb-2"><a href="{{ route('frontend.login') }}" class="text-white-50 text-decoration-none">Login</a></li>
                                    @endguest
                                </ul>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="footer-column">
                                <h3 class="h6 mb-3">Quick Links</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><a href="https://proppik.com/#gallery" class="text-white-50 text-decoration-none">Gallery</a></li>
                                    <li class="mb-2"><a href="https://proppik.com/#blog" class="text-white-50 text-decoration-none">Blog</a></li>
                                    <li class="mb-2"><a href="https://proppik.com/#testimonials" class="text-white-50 text-decoration-none">Testimonials</a></li>
                                    <li class="mb-2"><a href="https://proppik.com/#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-column">
                        <h3 class="h6 mb-3">Legal</h3>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="https://proppik.com/privacy-policy.html" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                            <li class="mb-2"><a href="https://proppik.com/refund-policy.html" class="text-white-50 text-decoration-none">Refund Policy</a></li>
                            <li class="mb-2"><a href="https://proppik.com/terms-and-conditions.html" class="text-white-50 text-decoration-none">Terms and Conditions</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center pt-4 border-top border-secondary">
                <p class="text-white-50 mb-0">&copy; <span>{{ date('Y') }}</span> PROP PIK Global PVT LTD. All rights reserved.</p>
            </div>
        </div>
    </footer>