@extends('layouts.auth', ['title' => 'Forgot Password'])

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

                <h2 class="fw-bold text-uppercase text-center fs-18">Reset Password</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email address and we'll send you an email with instructions to reset your password.</p>

                <div class="px-4">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-line me-2"></i>{{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.password.email') }}" class="authentication-form">
                        @csrf

                        @if (sizeof($errors) > 0)
                            @foreach ($errors->all() as $error)
                                <p class="text-danger mb-3">{{ $error }}</p>
                            @endforeach
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   class="form-control bg-light bg-opacity-50 border-light py-2 @error('email') is-invalid @enderror"
                                   placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button class="btn btn-danger py-2 fw-medium" type="submit">Send Password Reset Link</button>
                        </div>
                    </form>
                </div> <!-- end col -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->

        <p class="mb-0 text-center text-white mt-2">
            Back to <a href="{{ route('admin.login') }}" class="text-reset text-unline-dashed fw-bold ms-1">Sign In</a>
        </p>
    </div>
@endsection

