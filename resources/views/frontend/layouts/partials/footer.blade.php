<!-- Frontend Footer (New Theme) -->
<footer class="footer bg-dark text-white py-5" id="footer">
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
</footer>