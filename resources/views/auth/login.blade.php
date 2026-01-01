@extends('layouts.auth', ['title' => 'Login'])

@section('content')
    <div class="col-xl-5">

        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="##" class="logo-dark">
                        <img src="{{ asset('images/proppik-logo.jpg') }}" height="32" alt="logo dark">
                    </a>

                    <a href="##" class="logo-light">
                        <img src="{{ asset('images/logo-light.png') }}" height="28" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Log In</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email, mobile and password to access admin
                    panel.</p>

                <div class="px-4">
                    <form method="POST" action="{{ route('admin.login') }}" class="authentication-form" data-otp-send="{{ route('otp.send') }}" data-otp-verify="{{ route('otp.verify') }}" data-email-otp-send="{{ route('email_otp.send') }}" data-email-otp-verify="{{ route('email_otp.verify') }}">

                        @csrf
                        @if (sizeof($errors) > 0)
                            @foreach ($errors->all() as $error)
                                <p class="text-danger mb-3">{{ $error }}</p>
                            @endforeach
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="login-identifier">Email or Mobile</label>
                            <input type="text" id="login-identifier" name="email"
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="Enter email or mobile">
                        </div>
                        <div class="mb-3">
                            <a href="{{ route('admin.password.request') }}"
                               class="float-end text-muted text-unline-dashed ms-1">Reset
                                password</a>
                            <label class="form-label" for="login-password">Password</label>
                            <input type="password" id="login-password"
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="Enter your password" name="password">
                            <small class="form-text mt-1 d-none"><button type="button" id="login-toggle-otp" class="btn btn-link p-0 d-none">Verify with OTP</button></small>
                        </div>
                        <div class="mb-3 d-none" id="otp-login-block" style="display:none;">
                            <label class="form-label" for="login-otp-code">One-Time Password</label>
                            <div class="input-group">
                                <input type="text" id="login-otp-code" name="otp_code" class="form-control bg-light bg-opacity-50 border-light py-2" placeholder="Enter 6-digit code" inputmode="numeric" maxlength="6">
                            </div>
                            <small id="login-otp-text" class="form-text d-flex align-items-center gap-1 mt-1 d-none"></small>
                            <div class="d-flex justify-content-between mt-1 d-none">
                                <small class="form-text"><button type="button" id="login-toggle-password" class="btn btn-link p-0">Use password instead</button></small>
                                <small class="form-text"><button type="button" id="login-change-identifier" class="btn btn-link p-0">Change email or mobile</button></small>
                            </div>
                        </div>
                        <input type="hidden" name="otp_verified" id="login-otp-verified" value="0">
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">Remember me</label>
                            </div>
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button id="btn-submit-login" class="btn btn-danger py-2 fw-medium" type="submit">Sign In</button>
                        </div>
                    </form>

                    <!-- <p class="mt-3 fw-semibold no-span">OR sign with</p>
                    <div class="text-center">
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='bx bxl-google fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='ri-facebook-fill fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='bx bxl-github fs-20'></i></a>
                    </div> -->
                </div> <!-- end col -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->

        <p class="mb-0 text-center text-white d-none">New here? <a href="{{ route('admin.register') }}"

            class="text-reset text-unline-dashed fw-bold ms-1">Sign Up</a>
        </p>
        <p class="mb-0 text-center text-white mt-2 d-none">
            <a href="{{ route('admin.photographer.login') }}" class="text-reset text-unline-dashed">
                <i class='bx bx-camera me-1'></i>Login as Photographer
            </a>
        </p>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/auth-login.js') }}"></script>
@endpush
