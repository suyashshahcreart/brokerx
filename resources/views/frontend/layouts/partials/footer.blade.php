<!-- Frontend Footer -->
<footer class="footer-section" id="footer">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-12 mb-4 mb-lg-0">
                <a href="{{ route('frontend.index') }}"><img src="{{ asset('frontend/images/logo-w.png') }}" alt=""></a>
            </div>
            <div class="col-lg-3 col-md-12 mb-4 mb-lg-0">
                <h5>Get in touch</h5>
                <p>hello@proppik.com<br>+91 9876543210</p>
                <h5 class="mt-4">Locations</h5>
                <p>San Francisco — California<br>Palo Alto — Santa Clara</p>
            </div>
            <div class="col-lg-3 col-md-12 mb-4 mb-lg-0">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('frontend.terms') }}">Terms and Conditions</a></li>
                    <li><a href="{{ route('frontend.refund-policy') }}">Refund Policy</a></li>
                    <li><a href="{{ route('frontend.privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('frontend.login') }}">Login</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-12">
                <h5>Follow Us</h5>
                <ul class="footer-social-link">
                    <li><a href="#" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
                    <li><a href="#" target="_blank"><i class="fa-brands fa-facebook"></i></a></li>
                    <li><a href="#" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
                    <li><a href="#" target="_blank"><i class="fa-brands fa-tiktok"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-10 text-center">
                <p class="mb-0 copyright">© {{ date('Y') }} PROP PIK is a product of <a href="https://www.proppik.com" target="_blank">PROP PIK</a>.</p>
            </div>
        </div>
    </div>
</footer>