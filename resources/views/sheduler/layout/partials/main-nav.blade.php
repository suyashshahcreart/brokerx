<div class="main-nav">
     <!-- Sidebar Logo -->
     <div class="logo-box">
         <a href="#" class="logo-dark">
              <img src="{{ asset('images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
              <img src="{{ asset('images/logo-dark.png') }}" class="logo-lg" alt="logo dark">
          </a>

          <a href="#" class="logo-light">
               <img src="{{ asset('images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ asset('images/logo-light.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

     <!-- Menu Toggle Button (sm-hover) -->
     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
     </button>

     <div class="scrollbar" data-simplebar>

          <ul class="navbar-nav" id="navbar-nav">

               <li class="menu-title">Menu</li>

               <li class="nav-item">
                    <a class="nav-link" href="{{ route('any', 'orders')}}">
                         <span class="nav-icon">
                              <i class="ri-home-office-line"></i>
                         </span>
                         <span class="nav-text">Shadule Appointment</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="{{ route('any', 'transactions')}}">
                         <span class="nav-icon">
                              <i class="ri-arrow-left-right-line"></i>
                         </span>
                         <span class="nav-text">Change Appointment</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="{{ route('any', 'reviews')}}">
                         <span class="nav-icon">
                              <i class="ri-chat-quote-line"></i>
                         </span>
                         <span class="nav-text">Reviews</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="{{ route('any', 'messages')}}">
                         <span class="nav-icon">
                              <i class="ri-discuss-line"></i>
                         </span>
                         <span class="nav-text">Messages</span>
                    </a>
               </li>

          </ul>
     </div>
</div>
