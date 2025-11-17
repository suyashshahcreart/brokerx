@extends('frontend.layouts.base', ['title' => 'Privacy Policy - PROP PIK'])

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
    
    @include('frontend.layouts.partials.page-header', ['title' => 'Privacy Policy'])
    
    <section class="page bg-light section-padding-bottom section-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="terms-content">
                        <h3>1. Introduction</h3>
                        <p>PROP PIK ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our virtual tour services. Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.</p>

                        <h3>2. Information We Collect</h3>
                        <p>We may collect information about you in a variety of ways. The information we may collect on the site includes:</p>
                        <ul>
                            <li><strong>Personal Data:</strong> Personally identifiable information, such as your name, email address, phone number, and property address, that you voluntarily give to us when you register with the site or when you choose to participate in various activities related to the site.</li>
                            <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the site, such as your IP address, browser type, operating system, access times, and the pages you have viewed directly before and after accessing the site.</li>
                            <li><strong>Financial Data:</strong> Financial information, such as data related to your payment method (e.g., valid credit card number, card brand, expiration date) that we may collect when you purchase or order our services.</li>
                            <li><strong>Property Data:</strong> Information about your property, including images, descriptions, and location data that you provide to us for virtual tour creation.</li>
                        </ul>

                        <h3>3. How We Use Your Information</h3>
                        <p>Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the site to:</p>
                        <ul>
                            <li>Create and manage your account</li>
                            <li>Process your transactions and send you related information</li>
                            <li>Create and host virtual tours of your property</li>
                            <li>Email you regarding your account or order</li>
                            <li>Fulfill and manage purchases, orders, payments, and other transactions</li>
                            <li>Generate a personal profile about you to make future visits more personalized</li>
                            <li>Increase the efficiency and operation of the site</li>
                            <li>Monitor and analyze usage and trends to improve your experience</li>
                            <li>Notify you of updates to the site</li>
                            <li>Perform other business activities as needed</li>
                        </ul>

                        <h3>4. Disclosure of Your Information</h3>
                        <p>We may share information we have collected about you in certain situations. Your information may be disclosed as follows:</p>
                        <ul>
                            <li><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others, we may share your information as permitted or required by any applicable law, rule, or regulation.</li>
                            <li><strong>Third-Party Service Providers:</strong> We may share your information with third parties that perform services for us or on our behalf, including payment processing, data analysis, email delivery, hosting services, customer service, and marketing assistance.</li>
                            <li><strong>Business Transfers:</strong> We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company.</li>
                            <li><strong>With Your Consent:</strong> We may disclose your personal information for any other purpose with your consent.</li>
                        </ul>

                        <h3>5. Security of Your Information</h3>
                        <p>We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable, and no method of data transmission can be guaranteed against any interception or other type of misuse.</p>

                        <h3>6. Data Retention</h3>
                        <p>We will retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy unless a longer retention period is required or permitted by law. We will delete or anonymize your personal information when it is no longer needed for these purposes.</p>

                        <h3>7. Your Privacy Rights</h3>
                        <p>Depending on your location, you may have the following rights regarding your personal information:</p>
                        <ul>
                            <li>The right to access – You have the right to request copies of your personal data</li>
                            <li>The right to rectification – You have the right to request that we correct any information you believe is inaccurate</li>
                            <li>The right to erasure – You have the right to request that we erase your personal data, under certain conditions</li>
                            <li>The right to restrict processing – You have the right to request that we restrict the processing of your personal data</li>
                            <li>The right to data portability – You have the right to request that we transfer the data that we have collected to another organization, or directly to you</li>
                        </ul>

                        <h3>8. Cookies and Tracking Technologies</h3>
                        <p>We may use cookies, web beacons, tracking pixels, and other tracking technologies on the site to help customize the site and improve your experience. When you access the site, your personal information is collected through various technologies, such as cookies. You may refuse to accept browser cookies by activating the appropriate setting on your browser.</p>

                        <h3>9. Children's Privacy</h3>
                        <p>Our services are not intended for children under the age of 18. We do not knowingly collect personal information from children under 18. If we learn that we have collected personal information from a child under 18, we will delete that information as quickly as possible.</p>

                        <h3>10. Changes to This Privacy Policy</h3>
                        <p>We may update this Privacy Policy from time to time in order to reflect changes to our practices or for other operational, legal, or regulatory reasons. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date.</p>

                        <h3>11. Contact Us</h3>
                        <p>If you have questions or comments about this Privacy Policy, please contact us at hello@proppik.com or +91 9876543210.</p>

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
