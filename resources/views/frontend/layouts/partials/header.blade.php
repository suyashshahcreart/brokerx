<!-- Frontend Header / Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <div class="logo-wrapper">
            <a class="logo" href="{{ route('frontend.index') }}">
                <img src="{{ asset('frontend/images/logo.png') }}" class="logo-img" alt="Gloom" loading="lazy">
            </a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"><i class="ti-menu"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="0">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="1">Our Benefits</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="2">Our Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="3">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="4">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-scroll-nav="5">Testimonials</a></li>
                <li class="nav-item ms-0 ms-lg-3"><a class="btn btn-primary" href="{{ route('login') }}">Dashboard Login</a></li>
            </ul>
        </div>
    </div>
</nav>
