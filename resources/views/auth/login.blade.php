@extends('layouts.auth', ['title' => 'Login'])

@section('content')
    <div class="col-xl-5">

        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="{{ route('second', ['dashboards', 'analytics'])}}" class="logo-dark">
                        <img src="{{ asset('images/logo-dark.png') }}" height="32" alt="logo dark">
                    </a>

                    <a href="{{ route('second', ['dashboards', 'analytics'])}}" class="logo-light">
                        <img src="{{ asset('images/logo-light.png') }}" height="28" alt="logo light">
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Sign In</h2>
                <p class="text-muted text-center mt-1 mb-4">Enter your email address and password to access admin
                    panel.</p>

                <div class="px-4">
                    <form method="POST" action="{{ route('login') }}" class="authentication-form">

                        @csrf
                        @if (sizeof($errors) > 0)
                            @foreach ($errors->all() as $error)
                                <p class="text-danger mb-3">{{ $error }}</p>
                            @endforeach
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="example-email">Email</label>
                            <input type="email" id="example-email" name="email"
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="Enter your email" value="demo@user.com">
                        </div>
                        <div class="mb-3">
                            <a href="{{ route('second', ['auth', 'password'])}}"
                               class="float-end text-muted text-unline-dashed ms-1">Reset
                                password</a>
                            <label class="form-label" for="example-password">Password</label>
                            <input type="password" id="example-password"
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="Enter your password" name="password" value="password">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">Remember me</label>
                            </div>
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button class="btn btn-danger py-2 fw-medium" type="submit">Sign In</button>
                        </div>
                    </form>

                    <p class="mt-3 fw-semibold no-span">OR sign with</p>

                    <div class="text-center">
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='bx bxl-google fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='ri-facebook-fill fs-20'></i></a>
                        <a href="javascript:void(0);" class="btn btn-outline-light shadow-none"><i
                                class='bx bxl-github fs-20'></i></a>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->

        <p class="mb-0 text-center text-white">New here? <a href="{{ route('second', ['auth', 'signup'])}}"
            class="text-reset text-unline-dashed fw-bold ms-1">Sign Up</a>
    </p>
</div>
@endsection
