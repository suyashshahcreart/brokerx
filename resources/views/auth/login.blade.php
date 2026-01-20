@extends('layouts.auth', ['title' => 'Login'])

@section('content')
    <div class="col-xl-5">

        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="##" class="logo-dark">
                        <img src="{{ asset('proppik/assets/logo/logo.svg') }}" height="75" alt="logo dark">
                    </a>

                    <a href="##" class="logo-light">
                        <img src="{{ asset('proppik/assets/logo/w-logo.svg') }}" height="75" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Log In</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email, mobile and password to access admin
                    panel.</p>

                <div class="px-4">
                    <!-- Session Status Messages -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                            <i class='bx bx-check-circle me-2'></i>{{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <i class='bx bx-error-circle me-2'></i>
                            <strong>Login Failed!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login') }}" class="authentication-form" id="loginForm" novalidate data-otp-send="{{ route('otp.send') }}" data-otp-verify="{{ route('otp.verify') }}" data-email-otp-send="{{ route('email_otp.send') }}" data-email-otp-verify="{{ route('email_otp.verify') }}">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="login-identifier">Email or Mobile <span class="text-danger">*</span></label>
                            <input type="text" id="login-identifier" name="email"
                                   class="form-control bg-light bg-opacity-50 border-light py-2 @error('email') is-invalid @enderror"
                                   placeholder="Enter email or mobile"
                                   value="{{ old('email') }}"
                                   autocomplete="username"
                                   required>
                            <div class="invalid-feedback" id="identifier-error">
                                @error('email')
                                    {{ $message }}
                                @else
                                    Please enter a valid email or mobile number.
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3" id="password-block">
                            <a href="{{ route('admin.password.request') }}"
                               class="float-end text-muted text-unline-dashed ms-1">Reset
                                password</a>
                            <label class="form-label" for="login-password">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="login-password"
                                       class="form-control bg-light bg-opacity-50 border-light py-2 @error('password') is-invalid @enderror"
                                       placeholder="Enter your password" name="password"
                                       autocomplete="current-password"
                                       required>
                                <button class="d-none btn btn-light border-light" type="button" id="togglePassword" title="Show Password">
                                    <i class='bx bx-hide' id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password-error">
                                @error('password')
                                    {{ $message }}
                                @else
                                    Password is required.
                                @enderror
                            </div>
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
                            <button id="btn-submit-login" class="btn btn-danger py-2 fw-medium" type="submit">
                                <span class="btn-text">Sign In</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Protected by rate limiting (max 5 attempts per minute)</small>
                        </div>
                    </form>
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
