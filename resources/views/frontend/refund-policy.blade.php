@extends('frontend.layouts.base', ['title' => 'Refund Policy - PROP PIK'])

@section('css')
    {{-- Uses global new-theme CSS from base layout --}}
@endsection

@section('content')
    <!-- Refund Hero (New Theme) -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Policy</p>
                    <h1 class="display-5 fw-bold mb-3">Refund Policy</h1>
                    <p class="lead mb-0">How refunds and cancellations are handled at PROP PIK Global.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Refund Policy (New Theme) -->
    <section id="refund-policy" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h3 mb-4">PROP PIK Global - Refund Policy</h2>
                            <p class="text-muted">Effective date: January 1, 2025</p>

                            <h3 class="h5 mt-4">1. Overview</h3>
                            <p>This Refund Policy explains how refunds and cancellations are handled for PROP PIK’s virtual tour services, subscriptions, and related offerings.</p>

                            <h3 class="h5 mt-4">2. Eligibility for Refunds</h3>
                            <ul class="mb-3">
                                <li><strong>Project-based services:</strong> Eligible for partial refunds only if work has not progressed beyond agreed milestones. Discovery, travel, and incurred production costs are non-refundable.</li>
                                <li><strong>Subscriptions / hosting:</strong> Prepaid subscription periods are non-refundable once started. You may cancel future renewals at any time.</li>
                                <li><strong>Custom or rush work:</strong> Non-refundable once production begins.</li>
                            </ul>

                            <h3 class="h5 mt-4">3. Cancellation</h3>
                            <ul class="mb-3">
                                <li>Cancel in writing via email to <a href="mailto:contact@proppik.com">contact@proppik.com</a>.</li>
                                <li>For on-site shoots, cancellations or reschedules within 48 hours of the scheduled time may incur a fee to cover allocated resources.</li>
                                <li>If you cancel after deliverable handoff, no refund is provided.</li>
                            </ul>

                            <h3 class="h5 mt-4">4. Deposits &amp; Upfront Fees</h3>
                            <p>Deposits secure scheduling and pre-production resources and are non-refundable unless PROP PIK cancels without offering a reasonable reschedule.</p>

                            <h3 class="h5 mt-4">5. Quality Issues</h3>
                            <p>If you identify a material issue with delivered assets, notify PROP PIK within 7 days of delivery with details. We will attempt a reasonable remedy or re-export. Refunds are considered only if a remedy is not feasible.</p>

                            <h3 class="h5 mt-4">6. Payment Disputes &amp; Chargebacks</h3>
                            <p>Please contact PROP PIK first to resolve billing issues. Unjustified chargebacks may result in suspension of services and additional recovery fees.</p>

                            <h3 class="h5 mt-4">7. Timing of Refunds</h3>
                            <p>Approved refunds are processed to the original payment method within 7-14 business days. Processing times may vary by bank or payment provider.</p>

                            <h3 class="h5 mt-4">8. Taxes, Fees &amp; Currency</h3>
                            <p>Refunds exclude non-recoverable payment gateway fees, taxes, and currency conversion charges unless legally required.</p>

                            <h3 class="h5 mt-4">9. No-Show Policy</h3>
                            <p>Client no-shows for scheduled shoots may be charged a no-show fee and are typically ineligible for refunds of deposits or reserved time blocks.</p>

                            <h3 class="h5 mt-4">10. Force Majeure</h3>
                            <p>Delays or cancellations caused by events outside reasonable control (e.g., extreme weather, natural disasters, regulatory restrictions) may be rescheduled where possible; refunds are handled on a case-by-case basis.</p>

                            <h3 class="h5 mt-4">11. Changes to This Policy</h3>
                            <p>We may update this Refund Policy periodically. Material changes will be posted with an updated effective date.</p>

                            <h3 class="h5 mt-4">12. Contact</h3>
                            <p>For refund questions, contact us at <a href="mailto:contact@proppik.com">contact@proppik.com</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script-bottom')
    {{-- No page-specific scripts required --}}
@endsection
