@extends('frontend.layouts.base', ['title' => 'Contact - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">

    <!-- Google Fonts (New Theme) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montagu+Slab:opsz,wght@16..144,100..700&family=Poppins:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS (New Theme) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Magnific Popup CSS (Theme dependency) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">

    <!-- New Theme CSS -->
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/styles.css') }}">
@endsection

@section('content')
    <!-- Contact Hero -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Get in touch</p>
                    <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                    <p class="lead mb-0">We’re here to answer your questions and plan your next virtual tour.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section (Design only; submit disabled) -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-5">
                    <div class="contact-info-wrapper h-100">
                        <div class="contact-info-header mb-4">
                            <h3 class="contact-info-title mb-2">Let’s talk</h3>
                            <p class="contact-info-subtitle mb-0">Reach out and we’ll get back quickly.</p>
                        </div>

                        <div class="contact-info-card mb-3">
                            <div class="contact-item d-flex gap-3">
                                <div class="contact-item-content">
                                    <h4 class="contact-item-title mb-2">Email</h4>
                                    <p class="contact-item-text mb-0">
                                        <a href="mailto:contact@proppik.com" class="text-decoration-none">contact@proppik.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-info-card mb-3">
                            <div class="contact-item d-flex gap-3">
                                <div class="contact-item-content">
                                    <h4 class="contact-item-title mb-2">Phone</h4>
                                    <p class="contact-item-text mb-0">+91 98 98 36 30 26</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-info-card">
                            <div class="contact-item d-flex gap-3">
                                <div class="contact-item-content">
                                    <h4 class="contact-item-title mb-2">Quick Links</h4>
                                    <p class="contact-item-text mb-0">
                                        <a class="text-decoration-none" href="{{ route('frontend.terms') }}">Terms</a>
                                        <span class="mx-2">·</span>
                                        <a class="text-decoration-none" href="{{ route('frontend.privacy-policy') }}">Privacy</a>
                                        <span class="mx-2">·</span>
                                        <a class="text-decoration-none" href="{{ route('frontend.refund-policy') }}">Refund</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="contact-form-wrapper">
                        <div class="contact-form-header mb-4">
                            <h3 class="contact-form-title mb-2">Contact PROP PIK</h3>
                            <p class="contact-form-subtitle">Design-only for now (submit disabled).</p>
                        </div>

                        <form id="contactForm" class="contact-form">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="fullName" class="form-label-custom">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="fullName" name="fullName" placeholder="Enter your full name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="companyName" class="form-label-custom">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="companyName" name="companyName" placeholder="Enter your company name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contactPerson" class="form-label-custom">Contact Person Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control-custom" id="contactPerson" name="contactPerson" placeholder="1234567890" pattern="[0-9]{10}" maxlength="10" required>
                                        <small class="text-muted d-block mt-1">Enter 10-digit phone number</small>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="address" class="form-label-custom">Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-custom" id="address" name="address" placeholder="Enter your complete address" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="requirement" class="form-label-custom">Requirement <span class="text-danger">*</span></label>
                                        <textarea class="form-control-custom" id="requirement" name="requirement" rows="3" style="resize: none;" placeholder="Describe your requirements in detail..." required></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1" type="checkbox" value="agree" id="agreeTerms" required>
                                        <label class="form-check-label small text-muted" for="agreeTerms">
                                            I agree to the
                                            <a href="{{ route('frontend.terms') }}" class="text-primary text-decoration-none">Terms &amp; Conditions</a>
                                            and
                                            <a href="{{ route('frontend.privacy-policy') }}" class="text-primary text-decoration-none">Privacy Policy</a>.
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div id="formMessage" class="alert d-none mb-3" role="alert"></div>
                                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 fw-bold position-relative">
                                        <span class="submit-text">Submit Inquiry</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script-bottom')
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <!-- Swiper JS (used by theme main.js) -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- jQuery (required for Magnific Popup) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Magnific Popup JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

    <!-- New Theme JavaScript -->
    <script type="module" src="{{ asset('proppik/assets/js/main.js') }}"></script>

    <script>
        // Design-only: disable submit for now
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('contactForm');
            if (form) form.addEventListener('submit', (e) => e.preventDefault());
        });
    </script>
@endsection


