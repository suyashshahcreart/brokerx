@extends('frontend.layouts.base', ['title' => 'Terms and Conditions - PROP PIK'])

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('content')
    <!-- Progress scroll totop -->
    <div class="progress-wrap cursor-pointer">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    <!-- Cursor -->
    <div class="cursor js-cursor"></div>
    <!-- Social Icons -->
    <div class="social-ico-block"> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-instagram"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-x-twitter"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-youtube"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-tiktok"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-flickr"></i></a> 
    </div>
    
    @include('frontend.layouts.partials.page-header', ['title' => 'Terms and Conditions'])
    
    <section class="page bg-light section-padding-bottom section-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="terms-content">
                        <h3>1. Acceptance of Terms</h3>
                        <p>By accessing and using PROP PIK services, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

                        <h3>2. Use License</h3>
                        <p>Permission is granted to temporarily access the materials on PROP PIK's website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                        <ul>
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to reverse engineer any software contained on PROP PIK's website</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                        </ul>

                        <h3>3. Virtual Tour Services</h3>
                        <p>PROP PIK provides virtual tour creation and hosting services. By using our services, you agree to:</p>
                        <ul>
                            <li>Provide accurate information about your property</li>
                            <li>Grant PROP PIK permission to create and host virtual tours of your property</li>
                            <li>Use the virtual tours in accordance with applicable laws and regulations</li>
                        </ul>

                        <h3>4. User Account</h3>
                        <p>You are responsible for maintaining the confidentiality of your account credentials. You agree to accept responsibility for all activities that occur under your account.</p>

                        <h3>5. Payment Terms</h3>
                        <p>Payment for services must be made in accordance with the pricing plan selected. All fees are non-refundable unless otherwise stated. PROP PIK reserves the right to change pricing with 30 days notice.</p>

                        <h3>6. Intellectual Property</h3>
                        <p>All content, features, and functionality of the PROP PIK service, including but not limited to text, graphics, logos, and software, are the exclusive property of PROP PIK and are protected by copyright, trademark, and other laws.</p>

                        <h3>7. Limitation of Liability</h3>
                        <p>In no event shall PROP PIK or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on PROP PIK's website.</p>

                        <h3>8. Privacy Policy</h3>
                        <p>Your use of PROP PIK is also governed by our Privacy Policy. Please review our Privacy Policy to understand our practices regarding the collection and use of your information.</p>

                        <h3>9. Modifications</h3>
                        <p>PROP PIK may revise these terms of service at any time without notice. By using this website you are agreeing to be bound by the then current version of these terms of service.</p>

                        <h3>10. Contact Information</h3>
                        <p>If you have any questions about these Terms and Conditions, please contact us at hello@proppik.com or +91 9876543210.</p>

                        <p class="mt-4"><strong>Last Updated:</strong> January 2025</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery-migrate-3.5.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/smooth-scroll.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/wow.js') }}"></script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
@endsection
