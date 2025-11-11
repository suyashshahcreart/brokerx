@extends('layouts.auth', ['title' => 'Login'])

@section('content')
    <div class="col-xl-5">

        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="##" class="logo-dark">
                        <img src="{{ asset('images/logo-dark.png') }}" height="32" alt="logo dark">
                    </a>

                    <a href="##" class="logo-light">
                        <img src="{{ asset('images/logo-light.png') }}" height="28" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Sheduler Log In</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email, mobile to Shedule Appoinment.</p>

                <div class="px-4">
                    <form method="POST" action="{{ route('schedulers.otp.verify') }}" class="authentication-form" id="scheduler-otp-form">

                        @csrf
                        @if (sizeof($errors) > 0)
                            @foreach ($errors->all() as $error)
                                <p class="text-danger mb-3">{{ $error }}</p>
                            @endforeach
                        @endif

                        @if(session('success'))
                            <p class="text-success">{{ session('success') }}</p>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="login-mobile">Email or Mobile</label>
                            <input type="text" id="login-mobile" name="identifier"
                                class="form-control bg-light bg-opacity-50 border-light py-2"
                                placeholder="Enter mobile number or email" required value="{{ old('identifier') }}">
                        </div>

                        <div class="mb-3 d-flex gap-2">
                            <button type="button" id="btn-send-otp" class="btn btn-outline-primary">Send OTP</button>
                            <button type="button" id="btn-resend-otp" class="btn btn-link" style="display:none;">Resend</button>
                        </div>

                        <div class="mb-3" id="otp-block" style="display:none;">
                            <label class="form-label" for="login-otp-code">One-Time Password</label>
                            <div class="input-group">
                                <input type="text" id="login-otp-code" name="code"
                                    class="form-control bg-light bg-opacity-50 border-light py-2"
                                    placeholder="Enter 6-digit code" inputmode="numeric" maxlength="6">
                            </div>
                            <small id="login-otp-text" class="form-text text-muted mt-1">We sent a 6-digit code to your mobile.</small>
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button id="btn-submit-login" class="btn btn-danger py-2 fw-medium" type="submit">Verify & Login</button>
                        </div>
                    </form>

                    <script>
                        (function(){
                            const sendBtn = document.getElementById('btn-send-otp');
                            const resendBtn = document.getElementById('btn-resend-otp');
                            const mobileInput = document.getElementById('login-mobile');
                            const otpBlock = document.getElementById('otp-block');
                            const otpText = document.getElementById('login-otp-text');

                            function csrf() { return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }

                            async function sendOtp(){
                                const identifier = mobileInput.value.trim();
                                if(!identifier){ alert('Please enter mobile or email'); return; }
                                sendBtn.disabled = true;
                                const res = await fetch("{{ route('schedulers.otp.send') }}", {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                                    body: JSON.stringify({ identifier })
                                });
                                const json = await res.json();
                                if(json.ok){
                                    otpBlock.style.display = 'block';
                                    otpText.textContent = 'Code sent. It will expire in ' + (json.ttl || 300) + ' seconds.';
                                    resendBtn.style.display = 'inline-block';
                                } else {
                                    alert(json.message || 'Unable to send OTP');
                                }
                                sendBtn.disabled = false;
                            }

                            sendBtn.addEventListener('click', sendOtp);
                            resendBtn.addEventListener('click', sendOtp);
                        })();
                    </script>

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

        <p class="mb-0 text-center text-white">New here? <a href="{{ route('register')}}"
                class="text-reset text-unline-dashed fw-bold ms-1">Sign Up</a>
        </p>
    </div>
@endsection