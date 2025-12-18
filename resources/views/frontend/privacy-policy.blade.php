@extends('frontend.layouts.base', ['title' => 'Privacy Policy - PROP PIK'])

@section('css')
    {{-- Uses global new-theme CSS from base layout --}}
@endsection

@section('content')
    <!-- Privacy Hero (New Theme) -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Policy</p>
                    <h1 class="display-5 fw-bold mb-3">Privacy Policy</h1>
                    <p class="lead mb-0">How PROP PIK Global collects, uses, and protects your data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Policy (New Theme) -->
    <section id="privacy-policy" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h3 mb-4">PROP PIK Global - Privacy Policy</h2>
                            <p class="text-muted">Effective date: January 1, 2025</p>

                            <h3 class="h5 mt-4">1. Overview</h3>
                            <p>This Privacy Policy explains how PROP PIK collects, uses, discloses, and safeguards personal data when you use our websites, virtual tour services, and related offerings.</p>

                            <h3 class="h5 mt-4">2. Data We Collect</h3>
                            <ul class="mb-3">
                                <li><strong>Contact data:</strong> Name, email, phone, company, role.</li>
                                <li><strong>Usage data:</strong> Pages viewed, clicks, device/browser info, IP address, approximate location.</li>
                                <li><strong>Content data:</strong> Media and project details you provide for tours.</li>
                                <li><strong>Payment data:</strong> Processed via third-party gateways; we do not store full card details.</li>
                            </ul>

                            <h3 class="h5 mt-4">3. How We Use Data</h3>
                            <ul class="mb-3">
                                <li>Deliver and improve virtual tour services and support.</li>
                                <li>Communicate about projects, updates, and service notices.</li>
                                <li>Analytics to enhance performance and user experience.</li>
                                <li>Security, fraud prevention, and legal compliance.</li>
                                <li>Marketing (where permitted), with opt-out available.</li>
                            </ul>

                            <h3 class="h5 mt-4">4. Legal Bases (where applicable)</h3>
                            <p>We rely on consent, contract necessity, legitimate interests (e.g., service improvement, security), and legal obligations, depending on jurisdiction.</p>

                            <h3 class="h5 mt-4">5. Sharing &amp; Disclosure</h3>
                            <ul class="mb-3">
                                <li>Service providers (hosting, analytics, communications, payments) under confidentiality terms.</li>
                                <li>Legal/regulatory requests or to protect rights, safety, or security.</li>
                                <li>Business transactions (e.g., merger or acquisition) with notice where required.</li>
                            </ul>

                            <h3 class="h5 mt-4">6. Cookies &amp; Tracking</h3>
                            <p>We may use cookies, pixels, and similar technologies for functionality, analytics, and, where allowed, marketing. You can adjust browser settings to limit cookies; some features may be affected.</p>

                            <h3 class="h5 mt-4">7. Data Retention</h3>
                            <p>We retain data as long as needed for the purposes above, to fulfill contracts, meet legal obligations, or resolve disputes. Retention periods vary by data type and legal requirements.</p>

                            <h3 class="h5 mt-4">8. Security</h3>
                            <p>We use technical and organizational measures to protect data (encryption in transit, access controls, least-privilege practices). No system is fully secure; notify us of suspected issues.</p>

                            <h3 class="h5 mt-4">9. International Transfers</h3>
                            <p>Data may be processed in countries other than where you reside. We apply safeguards appropriate to the transfer mechanism and applicable law.</p>

                            <h3 class="h5 mt-4">10. Your Rights</h3>
                            <p>Subject to local laws, you may have rights to access, correct, delete, restrict, or object to processing, and to withdraw consent where processing is based on consent. Contact us to exercise these rights.</p>

                            <h3 class="h5 mt-4">11. Children</h3>
                            <p>Our services are not directed to children under 16 (or as defined by local law). We do not knowingly collect their data. If you believe a child has provided data, contact us to delete it.</p>

                            <h3 class="h5 mt-4">12. Third-Party Links</h3>
                            <p>Our sites may link to third-party services. Their privacy practices govern their data handling; review their policies separately.</p>

                            <h3 class="h5 mt-4">13. Changes to This Policy</h3>
                            <p>We may update this Privacy Policy periodically. Material changes will be posted with an updated effective date.</p>

                            <h3 class="h5 mt-4">14. Contact</h3>
                            <p>For privacy questions or requests, contact us at <a href="mailto:contact@proppik.com">contact@proppik.com</a>.</p>
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
