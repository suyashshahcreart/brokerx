<div class="main-nav">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="#" class="logo-dark">
               <img src="{{ asset('images/proppik-logo-sm.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ asset('images/proppik-logo.jpg') }}" class="logo-lg" alt="logo dark">
          </a>

          <a href="#" class="logo-light">
               <img src="{{ asset('images/proppik-logo-sm.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ asset('images/proppik-logo-light.png') }}" class="logo-lg" alt="logo light">
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
                    <a class="nav-link {{ request()->routeIs('admin.root') ? 'active' : '' }}"
                         href="{{ route('root') }}">
                         <span class="nav-icon">
                              <i class="ri-dashboard-2-line"></i>
                         </span>
                         <span class="nav-text">Dashboards</span>
                    </a>
               </li>


               @can('user_view')
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                              href="{{ route('admin.users.index') }}">
                              <span class="nav-icon">
                                   <i class="ri-user-line"></i>
                              </span>
                              <span class="nav-text">Customers</span>
                         </a>
                    </li>
               @endcan

               @can('booking_view')
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
                              href="{{ route('admin.bookings.index') }}">
                              <span class="nav-icon">
                                   <i class="ri-contacts-book-3-line"></i>
                              </span>
                              <span class="nav-text">Bookings</span>
                         </a>
                    </li>
               @endcan

               @can('photographer_visit_view')
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.photographer-visits.*') ? 'active' : '' }}" href="{{ route('admin.photographer-visits.index') }}">
                              <span class="nav-icon">
                                   <i class="ri-camera-line"></i>
                              </span>
                              <span class="nav-text">Photographer Visits</span>
                         </a>
                    </li>
               @endcan

               @can('photographer_visit_job_view')
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.photographer-visit-jobs.*') ? 'active' : '' }}" href="{{ route('admin.photographer-visit-jobs.index') }}">
                              <span class="nav-icon">
                                   <i class="ri-briefcase-line"></i>
                              </span>
                              <span class="nav-text">Photographer Jobs</span>
                         </a>
                    </li>
               @endcan

               @can('tour_view')
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.tours.*') ? 'active' : '' }}" href="{{ route('admin.tours.index') }}">
                              <span class="nav-icon">
                                   <i class="ri-map-pin-line"></i>
                              </span>
                              <span class="nav-text">Tours</span>
                         </a>
                    </li>
               @endcan


               {{-- @can('portfolio_view') --}}
               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.portfolios.*') ? 'active' : '' }}"
                         href="{{ route('admin.portfolios.index') }}">
                         <span class="nav-icon">
                              <i class="ri-profile-line"></i>
                         </span>
                         <span class="nav-text">Portfolio</span>
                    </a>
               </li>
               {{-- @endcan --}}


               @canany(['user_view', 'role_view', 'permission_view', 'activity_view', 'media_view'])
                    <li class="menu-title">System</li>

                    @can('user_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                   href="{{ route('admin.users.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-user-line"></i>
                                   </span>
                                   <span class="nav-text">Users</span>
                              </a>
                         </li>
                    @endcan

                    @can('role_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                                   href="{{ route('admin.roles.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-id-card-line"></i>
                                   </span>
                                   <span class="nav-text">Roles</span>
                              </a>
                         </li>
                    @endcan

                    @can('permission_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                                   href="{{ route('admin.permissions.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-lock-2-line"></i>
                                   </span>
                                   <span class="nav-text">Permissions</span>
                              </a>
                         </li>
                    @endcan

                    @can('activity_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}"
                                   href="{{ route('admin.activity.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-book-line"></i>
                                   </span>
                                   <span class="nav-text">Activity Log</span>
                              </a>
                         </li>
                    @endcan

                    @can('holiday_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}"
                                   href="{{ route('admin.holidays.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-calendar-event-line"></i>
                                   </span>
                                   <span class="nav-text">Holidays</span>
                              </a>
                         </li>
                    @endcan

                    @can('qr_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.qr.*') ? 'active' : '' }}" href="{{ route('admin.qr.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-qr-code-line"></i>
                                   </span>
                                   <span class="nav-text">QR Codes</span>
                              </a>
                         </li>
                    @endcan
                    
                    @can('setting_view')
                         <li class="nav-item">
                              <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                                   href="{{ route('admin.settings.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-home-gear-line"></i>
                                   </span>
                                   <span class="nav-text">Settings</span>
                              </a>
                         </li>
                    @endcan
               @endcanany

          </ul>
     </div>
</div>