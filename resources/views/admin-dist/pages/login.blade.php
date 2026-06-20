<x-admin.layout title="Dashboard">
  <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main">
      <div class="auth-wrapper v1">
        <div class="auth-form">
          <div class="position-relative">
            <div class="auth-bg">
              <span class="r"></span>
              <span class="r s"></span>
              <span class="r s"></span>
              <span class="r"></span>
            </div>
            <div class="card mb-0">
              <div class="card-body">
                <div class="text-center">
                  <a href="#">
                    <img src="{{ asset('assets/images/vertical-logo.jpeg') }}" alt="img" width="180" height="40">
                  </a>
                </div>
                <h4 class="text-center f-w-500 mt-4 mb-3">Admin Login</h4>
                <div class="mb-3">
                  <input type="email" class="form-control" id="floatingInput" placeholder="Email Address" />
                </div>
                <div class="mb-3">
                  <input type="password" class="form-control" id="floatingInput1" placeholder="Password" />
                </div>
                <div class="text-center mt-4">
                  <button type="button" class="btn btn-primary shadow px-sm-4">Login</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </x-admin.layout>
