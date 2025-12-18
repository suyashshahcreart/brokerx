@extends('frontend.layouts.base', ['title' => 'Terms and Conditions - PROP PIK'])

@section('css')
    {{-- Uses global new-theme CSS from base layout --}}
@endsection

@section('content')
    <!-- Terms Hero (New Theme) -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Policy</p>
                    <h1 class="display-5 fw-bold mb-3">Terms &amp; Conditions</h1>
                    <p class="lead mb-0">Terms for using PROP PIK’s global virtual tour services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms & Conditions (New Theme) -->
    <section id="terms" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h3 mb-4">PROP PIK Global - Terms &amp; Conditions</h2>
                            <p class="text-muted">Effective date: January 1, 2025</p>

                            <h3 class="h5 mt-4">1. Acceptance of Terms</h3>
                            <p>By accessing or using PROP PIK products, websites, virtual tour services, or related offerings (“Services”), you agree to these Terms &amp; Conditions and our Privacy Policy. If you are using the Services on behalf of an organization, you represent that you have authority to bind that organization.</p>

                            <h3 class="h5 mt-4">2. Services</h3>
                            <p>PROP PIK provides virtual tour creation, hosting, and related digital experiences for real estate, hospitality, retail, commercial, and other spaces. Service scope, deliverables, and timelines are defined in individual proposals, statements of work, or order forms.</p>

                            <h3 class="h5 mt-4">3. Accounts &amp; Access</h3>
                            <p>You are responsible for maintaining the confidentiality of account credentials and for all activities under your account. Notify PROP PIK immediately of any unauthorized use or security incident.</p>

                            <h3 class="h5 mt-4">4. Payments &amp; Refunds</h3>
                            <ul class="mb-3">
                                <li>Fees are due as specified in the applicable order, invoice, or subscription plan.</li>
                                <li>Taxes, payment gateway fees, and currency conversion charges (if any) are borne by the customer.</li>
                                <li>Refunds, if applicable, follow the Refund Policy linked in the footer.</li>
                            </ul>

                            <h3 class="h5 mt-4">5. Customer Content &amp; Licenses</h3>
                            <p>You grant PROP PIK a non-exclusive, worldwide, royalty-free license to host, display, and process content you provide solely to deliver the Services. You represent you have rights to all submitted content and that it does not infringe third-party rights.</p>

                            <h3 class="h5 mt-4">6. Acceptable Use</h3>
                            <ul class="mb-3">
                                <li>No unlawful, harmful, or fraudulent use.</li>
                                <li>No infringement of intellectual property or privacy rights.</li>
                                <li>No uploading of malicious code or attempting to disrupt or gain unauthorized access to the Services.</li>
                            </ul>

                            <h3 class="h5 mt-4">7. Intellectual Property</h3>
                            <p>All PROP PIK technology, software, designs, and trademarks remain the property of PROP PIK or its licensors. These Terms do not transfer any ownership rights to you.</p>

                            <h3 class="h5 mt-4">8. Data &amp; Privacy</h3>
                            <p>Personal data is handled according to our <a href="{{ route('frontend.privacy-policy') }}">Privacy Policy</a>. By using the Services, you consent to data processing as described there.</p>

                            <h3 class="h5 mt-4">9. Third-Party Services</h3>
                            <p>The Services may integrate third-party platforms (e.g., hosting, analytics, payment gateways). PROP PIK is not responsible for third-party terms or availability.</p>

                            <h3 class="h5 mt-4">10. Disclaimers</h3>
                            <p>The Services are provided “as is” and “as available.” PROP PIK disclaims all warranties, express or implied, including fitness for a particular purpose and non-infringement.</p>

                            <h3 class="h5 mt-4">11. Limitation of Liability</h3>
                            <p>To the maximum extent permitted by law, PROP PIK is not liable for indirect, incidental, consequential, or punitive damages, or any loss of profits or data. PROP PIK’s total liability for any claim is limited to the amount paid by you for the Services giving rise to the claim in the 3 months preceding the event.</p>

                            <h3 class="h5 mt-4">12. Indemnity</h3>
                            <p>You agree to indemnify and hold harmless PROP PIK, its affiliates, and personnel from claims arising from your use of the Services, violation of these Terms, or infringement of rights of any third party.</p>

                            <h3 class="h5 mt-4">13. Suspension &amp; Termination</h3>
                            <p>PROP PIK may suspend or terminate access for violations of these Terms, non-payment, legal requirements, or security risks. Upon termination, your right to use the Services ceases; provisions that by nature should survive will survive (including payment obligations, IP, warranties, and liability clauses).</p>

                            <h3 class="h5 mt-4">14. Governing Law &amp; Disputes</h3>
                            <p>These Terms are governed by the laws of India. Courts in Ahmedabad, Gujarat shall have exclusive jurisdiction, unless otherwise required by applicable law.</p>

                            <h3 class="h5 mt-4">15. Changes to Terms</h3>
                            <p>We may update these Terms periodically. Material changes will be posted on this page with an updated effective date. Continued use of the Services after changes means you accept the revised Terms.</p>

                            <h3 class="h5 mt-4">16. Contact</h3>
                            <p>For questions about these Terms, contact us at <a href="mailto:contact@proppik.com">contact@proppik.com</a>.</p>
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
