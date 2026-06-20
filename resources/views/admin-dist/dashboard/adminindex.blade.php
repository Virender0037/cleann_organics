<!doctype html>
<html lang="en">
  <head>
    <title>Cleann Organics</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="description"
      content="Datta able is trending dashboard template made using Bootstrap 5 design framework. Datta able is available in Bootstrap, React, CodeIgniter, Angular,  and .net Technologies."
    />
    <meta
      name="keywords"
      content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard"
    />
    <meta name="author" content="Codedthemes" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
 <!-- [Font] Family -->
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
<!-- [phosphor Icons] https://phosphoricons.com/ -->
<link rel="stylesheet" href="../assets/fonts/phosphor/regular/style.css" />
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
<link rel="stylesheet" href="../assets/css/style-preset.css" />
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-14K1GBX9FG"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag() {
    dataLayer.push(arguments);
  }
  gtag('js', new Date());

  gtag('config', 'G-14K1GBX9FG');
</script>
<!-- WiserNotify -->
<script>
  !(function () {
    if (window.t4hto4) console.log('WiserNotify pixel installed multiple time in this page');
    else {
      window.t4hto4 = !0;
      var t = document,
        e = window,
        n = function () {
          var e = t.createElement('script');
          (e.type = 'text/javascript'),
            (e.async = !0),
            (e.src = 'https://pt.wisernotify.com/pixel.js?ti=1jclj6jkfc4hhry'),
            document.body.appendChild(e);
        };
      'complete' === t.readyState ? n() : window.attachEvent ? e.attachEvent('onload', n) : e.addEventListener('load', n, !1);
    }
  })();
</script>
<!-- Microsoft clarity -->
<script type="text/javascript">
  (function (c, l, a, r, i, t, y) {
    c[a] =
      c[a] ||
      function () {
        (c[a].q = c[a].q || []).push(arguments);
      };
    t = l.createElement(r);
    t.async = 1;
    t.src = 'https://www.clarity.ms/tag/' + i;
    y = l.getElementsByTagName(r)[0];
    y.parentNode.insertBefore(t, y);
  })(window, document, 'clarity', 'script', 'gkn6wuhrtb');
</script>

  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->

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
      <a href="{{route('admin-dashboard')}}" class="b-brand text-primary">
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
          <a href="{{route('admin-dashboard')}}" class="pc-link">
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
                <a class="pc-link" href="#">
                    <span class="pc-micon">
                        <i class="ph ph-folders"></i>
                    </span>
                    Categories
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#">
                    <span class="pc-micon">
                        <i class="ph ph-package"></i>
                    </span>
                    Products
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#">
                    <span class="pc-micon">
                        <i class="ph ph-cube"></i>
                    </span>
                    Variants
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#">
                    <span class="pc-micon">
                        <i class="ph ph-star"></i>
                    </span>
                    Reviews
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#">
                    <span class="pc-micon">
                        <i class="ph ph-receipt"></i>
                    </span>
                    Tax Rates
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
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-package"></i>
                      </span>
                      Stock Levels
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-warning-circle"></i>
                      </span>
                      Low Stock
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
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
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-receipt"></i>
                        </span>
                        Orders
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-credit-card"></i>
                        </span>
                        Payments
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-ticket"></i>
                        </span>
                        Coupons
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
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
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-user"></i>
                        </span>
                        Customers
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-map-pin-line"></i>
                        </span>
                        Addresses
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-heart"></i>
                        </span>
                        Wishlists
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
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-map-trifold"></i>
                        </span>
                        Shipping Zones
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-currency-circle-dollar"></i>
                        </span>
                        Shipping Rates
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
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-file-text"></i>
                        </span>
                        Pages
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-newspaper"></i>
                        </span>
                        Blogs
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-folders"></i>
                        </span>
                        Blog Categories
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-tag"></i>
                        </span>
                        Blog Tags
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-question"></i>
                        </span>
                        FAQs
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-users"></i>
                        </span>
                        Team Members
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-chat-circle-text"></i>
                        </span>
                        Testimonials
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
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
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-chart-line"></i>
                      </span>
                      Sales Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-receipt"></i>
                      </span>
                      Orders Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-package"></i>
                      </span>
                      Products Report
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-users-three"></i>
                      </span>
                      Customer Report
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
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-users"></i>
                      </span>
                      Users
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
                      <span class="pc-micon">
                          <i class="ph ph-shield-check"></i>
                      </span>
                      Roles
                  </a>
              </li>

              <li class="pc-item">
                  <a class="pc-link" href="#">
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
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-gear"></i>
                        </span>
                        General Settings
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
                        <span class="pc-micon">
                            <i class="ph ph-magnifying-glass"></i>
                        </span>
                        SEO Settings
                    </a>
                </li>

                <li class="pc-item">
                    <a class="pc-link" href="#">
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
        <span class="badge bg-success pc-h-badge">3</span>
      </a>
      <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header d-flex align-items-center justify-content-between">
          <h5 class="m-0">Notifications</h5>
          <a href="#!" class="btn btn-link btn-sm">Mark all read</a>
        </div>
        <div class="dropdown-body text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
          <p class="text-span">Today</p>
          <div class="card bg-transparent mb-0">
            <div class="card-body">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img class="img-radius avatar rounded-0" src="../assets/images/user/avatar-1.png" alt="Generic placeholder image" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="float-end text-sm text-muted">2 min ago</span>
                  <h5 class="text-body mb-2">UI/UX Design</h5>
                  <p class="mb-0">
                    Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of
                    type and scrambled it to make a type
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="card bg-transparent mb-0">
            <div class="card-body">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img class="img-radius avatar rounded-0" src="../assets/images/user/avatar-2.png" alt="Generic placeholder image" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="float-end text-sm text-muted">1 hour ago</span>
                  <h5 class="text-body mb-2">Message</h5>
                  <p class="mb-0">Lorem Ipsum has been the industry's standard dummy text ever since the 1500.</p>
                </div>
              </div>
            </div>
          </div>
          <p class="text-span">Yesterday</p>
          <div class="card bg-transparent mb-0">
            <div class="card-body">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img class="img-radius avatar rounded-0" src="../assets/images/user/avatar-3.png" alt="Generic placeholder image" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="float-end text-sm text-muted">2 hour ago</span>
                  <h5 class="text-body mb-2">Forms</h5>
                  <p class="mb-0"
                    >Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of
                    type and scrambled it to make a type</p
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="card bg-transparent mb-0">
            <div class="card-body">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img class="img-radius avatar rounded-0" src="../assets/images/user/avatar-4.png" alt="Generic placeholder image" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="float-end text-sm text-muted">12 hour ago</span>
                  <h5 class="text-body mb-2">Challenge invitation</h5>
                  <p class="mb-2"><span class="text-dark">Jonny aber</span> invites to join the challenge</p>
                  <button class="btn btn-sm btn-outline-secondary me-2">Decline</button>
                  <button class="btn btn-sm btn-primary">Accept</button>
                </div>
              </div>
            </div>
          </div>
          <div class="card bg-transparent mb-0">
            <div class="card-body">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img class="img-radius avatar rounded-0" src="../assets/images/user/avatar-5.png" alt="Generic placeholder image" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="float-end text-sm text-muted">5 hour ago</span>
                  <h5 class="text-body mb-2">Security</h5>
                  <p class="mb-0"
                    >Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of
                    type and scrambled it to make a type</p
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="text-center py-2">
          <a href="#!" class="link-danger">Clear all Notifications</a>
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
              <img src="../assets/images/user/avatar-2.png" alt="user-image" class="user-avatar wid-35" />
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-white mb-1">Carson Darrin 🖖</h6>
              <span class="text-white text-opacity-75">carson.darrin@company.io</span>
            </div>
          </div>
        </div>
        <div class="dropdown-body">
          <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
            <a href="#" class="dropdown-item">
              <span>
                <i class="ph ph-lock-key align-middle me-2"></i>
                <span>Change Password</span>
              </span>
            </a>
            <div class="d-grid my-2">
              <button class="btn btn-primary"> <i class="ph ph-sign-out align-middle me-2"></i>Logout </button>
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
    <script src="../assets/js/plugins/apexcharts.min.js"></script>

    <script src="../assets/js/plugins/jsvectormap.min.js"></script>
    <script src="../assets/js/plugins/world.js"></script>

    <script src="../assets/js/dashboard/dashboard-default.js"></script>
    <!-- [Page Specific JS] end -->
    <!-- Required Js -->
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
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
            

  </body>
  <!-- [Body] end -->
</html>
