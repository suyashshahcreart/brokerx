<!-- Navigation Bar (same structure as demo/proppik-website/index.html) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('frontend.index') }}">
            <img src="{{ asset('proppik/assets/logo/logo.svg') }}" alt="Prop Pik" class="logo-img">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}#why-choose-us">Why Choose Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}#gallery">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.index') }}#contact">Contact Us</a>
                </li>

                @auth
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link fw-medium text-uppercase d-flex align-items-center gap-2 dropdown-toggle" href="#" id="profileDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user"></i>
                            <span>My Bookings</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('frontend.booking-dashboard') }}">
                                    <i class="fa-solid fa-table-cells"></i>
                                    <span>My Bookings</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('frontend.profile') }}">
                                    <i class="fa-regular fa-user"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('frontend.logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('frontend-logout-form').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Logout</span>
                                </a>
                                <form id="frontend-logout-form" action="{{ route('frontend.logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

                @guest
                    
                    <li class="nav-item">
                        <a class="nav-link fw-medium text-uppercase" href="{{ route('frontend.login') }}">My Bookings</a>
                    </li>
                @endguest

                <li class="nav-item ms-3">
                    <a href="{{ route('frontend.setup') }}" class="btn btn-primary">Book Now</a>
                </li>

                
            </ul>
        </div>
    </div>
</nav>