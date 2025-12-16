<!-- Frontend Header / Navbar -->
<style>
    /* User Dropdown Custom Styles */
    .navbar .nav-item.dropdown .nav-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .navbar .nav-item.dropdown .nav-link i {
        font-size: 1.1rem;
        line-height: 1;
        vertical-align: middle;
    }
    
    .navbar .dropdown-menu {
        min-width: 200px;
        padding: 0.75rem 0;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-top: 0.5rem;
    }
    
    .navbar .dropdown-menu .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #495057;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
        gap: 0.75rem;
    }
    
    .navbar .dropdown-menu .dropdown-item i {
        font-size: 1.25rem;
        width: 20px;
        text-align: center;
        line-height: 1;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    
    .navbar .dropdown-menu .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        color: #667eea;
        padding-left: 1.5rem;
        transform: translateX(2px);
    }
    
    .navbar .dropdown-menu .dropdown-item:hover i {
        color: #667eea;
        transform: scale(1.1);
    }
    
    .navbar .dropdown-menu .dropdown-item.text-danger {
        color: #dc3545;
    }
    
    .navbar .dropdown-menu .dropdown-item.text-danger i {
        color: #dc3545;
    }
    
    .navbar .dropdown-menu .dropdown-item.text-danger:hover {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.08) 0%, rgba(220, 53, 69, 0.12) 100%);
        color: #dc3545;
    }
    
    .navbar .dropdown-menu .dropdown-item.text-danger:hover i {
        color: #dc3545;
        transform: scale(1.1);
    }
    
    .navbar .dropdown-menu .dropdown-divider {
        margin: 0.5rem 0;
        border-color: rgba(0, 0, 0, 0.08);
    }
    
    .navbar .dropdown-toggle::after {
        display: inline-block;
        margin-left: 0.5rem;
        vertical-align: 0.255em;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-bottom: 0;
        border-left: 0.3em solid transparent;
        transition: transform 0.2s ease;
    }
    
    .navbar .dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
    }
    
    /* Active state for dropdown */
    .navbar .nav-item.dropdown.show .nav-link {
        color: var(--color-primary) !important;
    }
    
    .navbar .nav-item.dropdown.show .nav-link i {
        color: var(--color-primary) !important;
    }
    
    /* Smooth dropdown animation */
    @media (min-width: 992px) {
        .navbar .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .navbar .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    }
</style>

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
                @auth
                    <li class="nav-item dropdown profile-menu">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-user-line"></i>
                            <span>Profile</span>
                        </a>
                        <ul class="dropdown-menu profile-dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('frontend.booking-dashboard') }}">
                                    <i class="ri-dashboard-line"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('frontend.profile') }}">
                                    <i class="ri-user-line"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form id="logout-form" method="POST" action="{{ route('admin.logout') }}" style="display: none;">
                                    @csrf
                                </form>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="ri-logout-box-line"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('frontend.login') }}">Login</a></li>
                @endauth
                <li class="nav-item ms-0 ms-lg-3"><a class="btn btn-primary" href="{{ route('frontend.setup') }}">Get Virtual Tour</a></li>
            </ul>
        </div>
    </div>
</nav>
