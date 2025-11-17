@extends('frontend.layouts.base', ['title' => 'Refund Policy - PROP PIK'])

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
    
    @include('frontend.layouts.partials.page-header', ['title' => 'Refund Policy'])
    
    <section class="page bg-light section-padding-bottom section-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="terms-content">
                        <h3>1. Refund Eligibility</h3>
                        <p>At PROP PIK, we strive to provide exceptional virtual tour services. We understand that circumstances may change, and we have established the following refund policy to address various situations. Refunds are considered on a case-by-case basis and are subject to the terms outlined below.</p>

                        <h3>2. Service Cancellation Before Production</h3>
                        <p>If you cancel your virtual tour service before we begin production (photography/videography), you may be eligible for a full or partial refund:</p>
                        <ul>
                            <li><strong>Full Refund:</strong> If cancellation is requested within 24 hours of payment and before any production work has commenced, you will receive a full refund.</li>
                            <li><strong>Partial Refund:</strong> If cancellation is requested more than 24 hours after payment but before production begins, a processing fee of 10% may be deducted from your refund.</li>
                            <li><strong>No Refund:</strong> If cancellation is requested after production has started, no refund will be provided.</li>
                        </ul>

                        <h3>3. Service Cancellation After Production</h3>
                        <p>Once production has begun, refunds are generally not available as we have already invested time, resources, and equipment in creating your virtual tour. However, in exceptional circumstances such as:</p>
                        <ul>
                            <li>Technical failure on our part that prevents delivery of the service</li>
                            <li>Inability to access the property due to circumstances beyond your control (with proper documentation)</li>
                            <li>Service not delivered within the agreed timeframe without reasonable cause</li>
                        </ul>
                        <p>We may consider a partial refund or credit toward future services. All such requests must be submitted in writing within 30 days of the scheduled delivery date.</p>

                        <h3>4. Unsatisfactory Service</h3>
                        <p>If you are not satisfied with the quality of the virtual tour delivered, please contact us within 7 days of delivery. We will:</p>
                        <ul>
                            <li>Review your concerns and the delivered work</li>
                            <li>Offer to make reasonable revisions to address your concerns</li>
                            <li>If revisions cannot resolve the issue and the work does not meet our stated quality standards, we may offer a partial refund or re-shoot at no additional cost</li>
                        </ul>
                        <p>Refunds for unsatisfactory service are only considered after we have been given a reasonable opportunity to address your concerns.</p>

                        <h3>5. Subscription and Recurring Payments</h3>
                        <p>For subscription-based services:</p>
                        <ul>
                            <li>You may cancel your subscription at any time, and it will not renew for the next billing cycle</li>
                            <li>Refunds for the current billing period are not available unless the service is discontinued by PROP PIK</li>
                            <li>You will continue to have access to your virtual tours until the end of your current billing period</li>
                        </ul>

                        <h3>6. Processing Time</h3>
                        <p>If your refund request is approved:</p>
                        <ul>
                            <li>Refunds will be processed within 7-14 business days</li>
                            <li>Refunds will be issued to the original payment method used for the transaction</li>
                            <li>You will receive an email confirmation once the refund has been processed</li>
                            <li>Please note that it may take additional time for the refund to appear in your account, depending on your financial institution</li>
                        </ul>

                        <h3>7. Non-Refundable Items</h3>
                        <p>The following are not eligible for refunds:</p>
                        <ul>
                            <li>Services that have been fully completed and delivered</li>
                            <li>Add-on services or upgrades that have been implemented</li>
                            <li>Custom development work that has been completed</li>
                            <li>Services cancelled due to violation of our Terms and Conditions</li>
                            <li>Processing fees and transaction charges</li>
                        </ul>

                        <h3>8. Chargebacks</h3>
                        <p>If you file a chargeback with your credit card company or payment provider, we reserve the right to dispute the chargeback and provide evidence of service delivery. We encourage you to contact us directly to resolve any issues before initiating a chargeback, as this allows us to address your concerns more effectively.</p>

                        <h3>9. How to Request a Refund</h3>
                        <p>To request a refund, please contact us:</p>
                        <ul>
                            <li><strong>Email:</strong> hello@proppik.com</li>
                            <li><strong>Phone:</strong> +91 9876543210</li>
                            <li>Include your order number, reason for refund request, and any relevant documentation</li>
                            <li>We will review your request and respond within 5-7 business days</li>
                        </ul>

                        <h3>10. Changes to This Policy</h3>
                        <p>PROP PIK reserves the right to modify this Refund Policy at any time. Any changes will be posted on this page with an updated revision date. Your continued use of our services after any changes constitutes acceptance of the new policy.</p>

                        <h3>11. Contact Information</h3>
                        <p>If you have any questions about this Refund Policy or need to request a refund, please contact us at hello@proppik.com or +91 9876543210.</p>

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
