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
                    <a class="nav-link" href="{{ route('root') }}">
                         <span class="nav-icon">
                              <i class="ri-dashboard-2-line"></i>
                         </span>
                         <span class="nav-text">Dashboards</span>
                    </a>
               </li>


               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarProperty" data-bs-toggle="collapse" role="button"
                         aria-expanded="false" aria-controls="sidebarProperty">
                         <span class="nav-icon">
                              <i class="ri-community-line"></i>
                         </span>
                         <span class="nav-text"> Property </span>
                    </a>
                    <div class="collapse" id="sidebarProperty">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Property Grid</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Property List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Property Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Add Property</a>
                              </li>
                         </ul>
                    </div>
               </li> <!-- end Pages Menu -->

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAgents" data-bs-toggle="collapse" role="button"
                         aria-expanded="false" aria-controls="sidebarAgents">
                         <span class="nav-icon">
                              <i class="ri-group-line"></i>
                         </span>
                         <span class="nav-text"> Agents </span>
                    </a>
                    <div class="collapse" id="sidebarAgents">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">List View</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Grid View</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Agent Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Add Agent</a>
                              </li>
                         </ul>
                    </div>
               </li> <!-- end Pages Menu -->

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCustomers" data-bs-toggle="collapse" role="button"
                         aria-expanded="false" aria-controls="sidebarCustomers">
                         <span class="nav-icon">
                              <i class="ri-contacts-book-3-line"></i>
                         </span>
                         <span class="nav-text"> Customers </span>
                    </a>
                    <div class="collapse" id="sidebarCustomers">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">List View</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Grid View</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Customer Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="#">Add Customer</a>
                              </li>
                         </ul>
                    </div>
               </li> <!-- end Pages Menu -->

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBooking" data-bs-toggle="collapse" role="button"
                         aria-expanded="false" aria-controls="sidebarCustomers">
                         <span class="nav-icon">
                              <i class="ri-contacts-book-3-line"></i>
                         </span>
                         <span class="nav-text"> Bookings </span>
                    </a>
                    <div class="collapse" id="sidebarBooking">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="{{ route('admin.bookings.index') }}">List View</a>
                              </li>
                         </ul>
                    </div>
               </li> <!-- end Pages Menu -->

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarPortfolio" data-bs-toggle="collapse" role="button"
                         aria-expanded="false" aria-controls="sidebarCustomers">
                         <span class="nav-icon">
                              <i class="ri-profile-line"></i>
                         </span>
                         <span class="nav-text"> Portfolios </span>
                    </a>
                    <div class="collapse" id="sidebarPortfolio">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="{{ route('admin.portfolios.index') }}">List View</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="{{ route('admin.portfolios.create') }}">Create Portfolio</a>
                              </li>
                         </ul>
                    </div>
               </li> <!-- end Pages Menu -->

               @canany(['user_view', 'role_view', 'permission_view', 'activity_view', 'media_view'])
                    <li class="menu-title">System</li>

                    @can('user_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.users.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-user-line"></i>
                                   </span>
                                   <span class="nav-text">Users</span>
                              </a>
                         </li>
                    @endcan

                    @can('role_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.roles.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-id-card-line"></i>
                                   </span>
                                   <span class="nav-text">Roles</span>
                              </a>
                         </li>
                    @endcan

                    @can('permission_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.permissions.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-lock-2-line"></i>
                                   </span>
                                   <span class="nav-text">Permissions</span>
                              </a>
                         </li>
                    @endcan

                    @can('activity_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.activity.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-book-line"></i>
                                   </span>
                                   <span class="nav-text">Activity Log</span>
                              </a>
                         </li>
                    @endcan

                    @can('media_view')
                         <li class="nav-item">
                              <a class="nav-link" href="#">
                                   <span class="nav-icon">
                                        <i class="ri-image-line"></i>
                                   </span>
                                   <span class="nav-text">Media Library</span>
                              </a>
                         </li>
                    @endcan

                    @can('holiday_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-calendar-event-line"></i>
                                   </span>
                                   <span class="nav-text">Holidays</span>
                              </a>
                         </li>
                    @endcan

                    @can('qr_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.qr.index') }}">
                                   <span class="nav-icon">
                                        <i class="ri-qr-code-line"></i>
                                   </span>
                                   <span class="nav-text">QR Codes</span>
                              </a>
                         </li>
                    @endcan
                    
                    @can('setting_view')
                         <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.settings.index') }}">
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