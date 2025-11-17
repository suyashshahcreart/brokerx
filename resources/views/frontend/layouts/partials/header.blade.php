<!-- Frontend Header / Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <div class="logo-wrapper">
            <a class="logo" href="{{ route('frontend.index') }}">
                <img src="{{ asset('frontend/images/logo.png') }}" class="logo-img" alt="PROP PIK" loading="lazy">
            </a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"><i class="ti-menu"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}#benefits">Our Benefits</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}#portfolio">Our Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}#blog">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.index') }}#testimonials">Testimonials</a></li>
                <li class="nav-item ms-0 ms-lg-3"><a class="btn btn-primary" href="{{ route('frontend.setup') }}">Get Virtual Tour</a></li>
            </ul>
        </div>
    </div>
</nav>
