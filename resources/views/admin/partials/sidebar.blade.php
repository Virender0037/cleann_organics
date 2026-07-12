 <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="{{route('admin.dashboard')}}" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="{{ asset('assets/images/vertical-logo.jpeg') }}" alt="img" width="180" height="40">
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label data-i18n="Navigation">Navigation</label>
        </li>
        <li class="pc-item">
          <a href="{{route('admin.dashboard')}}" class="pc-link">
            <span class="pc-micon">
              <i class="ph ph-house-line"></i>
            </span>
            <span class="pc-mtext" data-i18n="Dashboard">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label data-i18n="pages">MANAGEMENT</label>
          <i class="ph ph-shield-checkered"></i>
        </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-package"></i>
                </span>
                <span class="pc-mtext">Catalog</span>
                <span class="pc-arrow">
                    <i data-feather="ph ph-caret-down"></i>
                </span>
            </a>
          <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.categories.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-folders"></i>
                    </span>
                    Categories
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.products.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-package"></i>
                    </span>
                    Products
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.variants.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-cube"></i>
                    </span>
                    Variants
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.reviews.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-star"></i>
                    </span>
                    Reviews
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.tax-rates.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-receipt"></i>
                    </span>
                    Tax Rates
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.catalog.tags.index')}}">
                    <span class="pc-micon">
                        <i class="ph ph-tag"></i>
                    </span>
                    Tags
                </a>
            </li>
        </ul>
          </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
              <span class="pc-micon">
                  <i class="ph ph-stack"></i>
              </span>
              <span class="pc-mtext">Inventory</span>
          </a>

          <ul class="pc-submenu">

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.inventory.stock-levels.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-package"></i>
                      </span>
                      Stock Levels
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.inventory.low-stock.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-warning-circle"></i>
                      </span>
                      Low Stock
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.inventory.out-of-stock.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-x-circle"></i>
                      </span>
                      Out of Stock
                  </a>
              </li>

          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-shopping-cart"></i>
                </span>
                <span class="pc-mtext">Sales</span>
            </a>

            <ul class="pc-submenu">

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.sales.orders.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-receipt"></i>
                        </span>
                        Orders
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.sales.payments.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-credit-card"></i>
                        </span>
                        Payments
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.sales.coupons.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-ticket"></i>
                        </span>
                        Coupons
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.sales.returns.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </span>
                        Returns
                    </a>
                </li>

            </ul>
        </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-users-three"></i>
                </span>
                <span class="pc-mtext">Customers</span>
            </a>

            <ul class="pc-submenu">

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.customers.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-user"></i>
                        </span>
                        Customers
                    </a>
                </li>

            </ul>
        </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-truck"></i>
                </span>
                <span class="pc-mtext">Shipping</span>
            </a>

            <ul class="pc-submenu">

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.shipping.zones.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-map-trifold"></i>
                        </span>
                        Shipping Zones
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.shipping.rates.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-currency-circle-dollar"></i>
                        </span>
                        Shipping Rates
                    </a>
                </li>

                <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.shipping.methods.index') }}">
                    <span class="pc-micon"><i class="ph ph-truck"></i></span>
                    Shipping Methods
                </a>
                </li>

            </ul>
         </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-note-pencil"></i>
                </span>
                <span class="pc-mtext">CMS</span>
            </a>

            <ul class="pc-submenu">

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.pages.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-file-text"></i>
                        </span>
                        Pages
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.blogs.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-newspaper"></i>
                        </span>
                        Blogs
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.blog-categories.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-folders"></i>
                        </span>
                        Blog Categories
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.blog-tags.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-tag"></i>
                        </span>
                        Blog Tags
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.faqs.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-question"></i>
                        </span>
                        FAQs
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.team-members.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-users"></i>
                        </span>
                        Team Members
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.testimonials.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-chat-circle-text"></i>
                        </span>
                        Testimonials
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{route('admin.cms.contact-messages.index')}}">
                        <span class="pc-micon">
                            <i class="ph ph-envelope-simple"></i>
                        </span>
                        Contact Messages
                    </a>
                </li>

            </ul>
          </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
              <span class="pc-micon">
                  <i class="ph ph-chart-bar"></i>
              </span>
              <span class="pc-mtext">Reports</span>
          </a>

          <ul class="pc-submenu">

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.sales.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-chart-line"></i>
                      </span>
                      Sales Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.orders.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-receipt"></i>
                      </span>
                      Orders Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.products.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-package"></i>
                      </span>
                      Products Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.customers.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Customer Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.inventory.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Inventory Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.payments.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Payments Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.coupons.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Coupons Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.reports.returns.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Returns Report
                  </a>
              </li>

          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
              <span class="pc-micon">
                  <i class="ph ph-user-gear"></i>
              </span>
              <span class="pc-mtext">Administration</span>
          </a>

          <ul class="pc-submenu">

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.administration.users.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-users"></i>
                      </span>
                      Users
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.administration.roles.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-shield-check"></i>
                      </span>
                      Roles
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="{{route('admin.administration.permissions.index')}}">
                      <span class="pc-micon">
                          <i class="ph ph-key"></i>
                      </span>
                      Permissions
                  </a>
              </li>

          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
                <span class="pc-micon">
                    <i class="ph ph-gear-six"></i>
                </span>
                <span class="pc-mtext">Settings</span>
            </a>

            <ul class="pc-submenu">

                <li class="pc-item">
                    <a class="pc-link" href="{{ route('admin.settings.general.index') }}">
                        <span class="pc-micon">
                            <i class="ph ph-gear"></i>
                        </span>
                        General Settings
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{ route('admin.settings.seo.index') }}">
                        <span class="pc-micon">
                            <i class="ph ph-magnifying-glass"></i>
                        </span>
                        SEO Settings
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="{{ route('admin.settings.email.index') }}">
                        <span class="pc-micon">
                            <i class="ph ph-envelope"></i>
                        </span>
                        Email Settings
                    </a>
                </li>

            </ul>
        </li>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->
 <!-- [ Header Topbar ] start -->
<header class="pc-header">
  <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ph ph-list"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ph ph-list"></i>
      </a>
    </li>
  </ul>
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph ph-bell"></i>
      </a>
      <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header d-flex align-items-center justify-content-between">
          <h5 class="m-0">Notifications</h5>
        </div>
        <div class="dropdown-body text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
          <div class="text-center text-muted py-4">
            No notifications available.
          </div>
        </div>
      </div>
    </li>
    <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        data-bs-auto-close="outside"
        aria-expanded="false"
      >
        <i class="ph ph-user-circle"></i>
      </a>
      <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">
        <div class="dropdown-header d-flex align-items-center justify-content-between bg-primary">
          <div class="d-flex my-2">
            <div class="flex-shrink-0">
              <img src="{{ auth()->user()?->avatar ? \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) : 'https://placehold.co/35x35' }}" alt="user-image" class="user-avatar wid-35" />
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-white mb-1">{{ auth()->user()?->name ?? 'Guest' }}</h6>
              <span class="text-white text-opacity-75">{{ auth()->user()?->email }}</span>
            </div>
          </div>
        </div>
        <div class="dropdown-body">
          <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
            <a href="{{ route('profile.edit') }}" class="dropdown-item">
              <span>
                <i class="ph ph-lock-key align-middle me-2"></i>
                <span>Change Password</span>
              </span>
            </a>
            <div class="d-grid my-2">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100"> <i class="ph ph-sign-out align-middle me-2"></i>Logout </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </li>
  </ul>
</div>
 </div>
</header>
<!-- [ Header ] end -->
    <!-- [Page Specific JS] start -->
    <!-- apexcharts js -->
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/world.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/dashboard-default.js') }}"></script>
    <!-- [Page Specific JS] end -->
    <!-- Required Js -->
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script defer src="https://fomo.codedthemes.com/pixel/yRevReYmxkh1j4z7Hc4tgbOKeXSu5Bm1"></script>

       
    <script>
      layout_change('light');
    </script>
       
    <script>
      change_box_container('false');
    </script>
     
    <script>
      layout_caption_change('true');
    </script>
       
    <script>
      layout_rtl_change('false');
    </script>
     
    <script>
      preset_change('preset-1');
    </script>
     
    <script>
      layout_theme_sidebar_change('false');
    </script>