@extends('layouts.auth', ['title' => 'Register'])

@section('content')

    <div class="col-xl-8">
        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="{{ route('second', ['dashboards', 'analytics'])}}" class="logo-dark">
                        <img src="/images/logo-dark.png" height="32" alt="logo dark">
                    </a>

                    <a href="{{ route('second', ['dashboards', 'analytics'])}}" class="logo-light">
                        <img src="/images/logo-light.png" height="28" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Sheduler Register</h2>
                <p class="text-muted text-center mt-1 mb-4">New to our platform? Sign up now! It only takes a minute.</p>

                <div class="px-4">
                    <form action="{{ route('schedulers.register.store') }}" method="post" class="authentication-form"
                        data-otp-send="{{ route('schedulers.otp.send') }}"
                        data-otp-verify="{{ route('schedulers.otp.verify') }}">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="firstname">Firstname <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="first-name" name="firstname" value="{{ old('firstname') }}"
                                    class="form-control bg-light bg-opacity-50 border-light py-2 @error('firstname') is-invalid @enderror"
                                    placeholder="Enter your first name" required>
                                @error('firstname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="lastname">Lastname <span class="text-danger">*</span></label>
                                <input type="text" id="last-name" name="lastname" value="{{ old('lastname') }}"
                                    class="form-control bg-light bg-opacity-50 border-light py-2 @error('lastname') is-invalid @enderror"
                                    placeholder="Enter your last name" required>
                                @error('lastname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                                <div class="input-group" id="mobile-input-group">
                                    <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}"
                                        class="form-control bg-light bg-opacity-50 border-light py-2 @error('mobile') is-invalid @enderror"
                                        placeholder="Enter your mobile number" required>
                                    <button type="button" id="btn-verify-mobile"
                                        class="btn btn-outline-success border-light">Verify</button>
                                    <span id="mobile-verified-badge"
                                        class="input-group-text bg-success text-white fs-5 d-none"><i
                                            class='bx bx-check-circle me-1'></i>Verified</span>
                                </div>
                                <div id="otp-block" class="mt-2" style="display:none;">
                                    <div class="input-group">
                                        <input type="text" id="otp-code"
                                            class="form-control bg-light bg-opacity-50 border-light py-2"
                                            placeholder="Enter 6-digit code" inputmode="numeric" maxlength="6">
                                        <button type="button" id="btn-submit-otp"
                                            class="btn btn-success border-light">Submit OTP</button>
                                    </div>
                                    <small class="form-text d-flex align-items-center gap-2 mt-1">
                                        <span>Code sent to</span>
                                        <strong id="otp-mobile-display"></strong>
                                        <button type="button" id="btn-change-mobile" class="btn btn-link p-0 ms-2">Change
                                            mobile number</button>
                                    </small>
                                </div>
                                <small id="mobile-verify-text"
                                    class="form-text d-flex align-items-center gap-1 mt-1"></small>
                                @error('mobile')
                                    <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                                @enderror
                                <small id="mobile-change-after"
                                    class="form-text d-flex align-items-center gap-1 mt-1 d-none">
                                    <button type="button" id="btn-change-mobile-after" class="btn btn-link p-0">Change
                                        mobile number</button>
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="example-email">Email</label>
                                <input type="email" id="example-email" name="email" value="{{ old('email') }}"
                                    class="form-control bg-light bg-opacity-50 border-light py-2 @error('email') is-invalid @enderror"
                                    placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- <div class="mb-3">
                            <label class="form-label" for="license-number">Broker License Number <span class="text-danger">*</span></label>
                            <input type="text" id="license-number" name="license_number" value="{{ old('license_number') }}" class="form-control bg-light bg-opacity-50 border-light py-2 @error('license_number') is-invalid @enderror" placeholder="Enter your broker license number" required>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> -->
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">I accept Terms and Condition</label>
                            </div>
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button id="btn-submit-register" class="btn btn-danger py-2" type="submit" disabled>Create
                                Account</button>
                        </div>
                    </form>

                    <script src="{{ asset('js/scheduler-otp.js') }}"></script>
                    <!-- <p class="mt-3 fw-semibold no-span">OR sign with</p>
                    <div class="text-center">
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i class='bx bxl-google fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i class='ri-facebook-fill fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i class='bx bxl-github fs-20'></i></a>
                    </div> -->
                </div> <!-- end col -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->

        <p class="mb-0 text-center text-white">I already have an account <a href="{{ route('second', ['auth', 'login'])}}"
                class="text-reset text-unline-dashed fw-bold ms-1">Sign In</a></p>
    </div> <!-- end col -->
@endsection