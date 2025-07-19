<!DOCTYPE html>



<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr" data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Account settings - Pages | Vuexy - Bootstrap Admin Template</title>

    
    <meta name="description" content="Start your development with a Dashboard for Bootstrap 5" />
    <meta name="keywords" content="dashboard, bootstrap 5 dashboard, bootstrap 5 design, bootstrap 5">
    <!-- Canonical SEO -->
    <link rel="canonical" href="https://1.envato.market/vuexy_admin">
    
    
    <!-- ? PROD Only: Google Tag Manager (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','GTM-5J3LMKC');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="../../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../../assets/vendor/fonts/tabler-icons.css"/>
    <link rel="stylesheet" href="../../assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/typeahead-js/typeahead.css" /> 
    

    <!-- Page CSS -->
    

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="../../assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../assets/js/config.js"></script>
    
</head>

<body>

  
  <!-- ?PROD Only: Google Tag Manager (noscript) (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP" height="0" width="0" style="display: none; visibility: hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  
  <!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar  ">
  <div class="layout-container">

<!-- Menu -->

{{-- Sidebar Partial --}}
@include('partials.sidebar')
<!-- / Menu -->


    <!-- Layout container -->
    <div class="layout-page">
      

<!-- Navbar -->

@include('partials.navbar')

<!-- / Navbar -->


      

      <!-- Content wrapper -->
      <div class="content-wrapper">

        <!-- Content -->
        
          <div class="container-xxl flex-grow-1 container-p-y">
            
            
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Account Settings / </span> Connections
</h4>

<div class="row">
  <div class="col-md-12">
    <!-- Accountbar -->
    @include('partials.accountbar')
    <!-- / Accountbar -->
    <div class="row">
      <div class="col-md-6 col-12 mb-md-0 mb-4">
        <div class="card">
          <h5 class="card-header pb-1">Connected Accounts</h5>
          <div class="card-body">
            <p class="mb-4">Display content from your connected accounts on your site</p>
            <!-- Connections -->
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/google.png" alt="google" class="me-3" height="30">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-9">
                  <h6 class="mb-0">Google</h6>
                  <small class="text-muted">Calendar and contacts</small>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end mt-sm-0 mt-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input float-end" type="checkbox" checked>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/slack.png" alt="slack" class="me-3" height="30">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-9">
                  <h6 class="mb-0">Slack</h6>
                  <small class="text-muted">Communication</small>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end mt-sm-0 mt-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input float-end" type="checkbox">
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/github.png" alt="github" class="me-3" height="30">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-9">
                  <h6 class="mb-0">Github</h6>
                  <small class="text-muted">Manage your Git repositories</small>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end mt-sm-0 mt-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input float-end" type="checkbox" checked>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/mailchimp.png" alt="mailchimp" class="me-3" height="30">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-9">
                  <h6 class="mb-0">Mailchimp</h6>
                  <small class="text-muted">Email marketing service</small>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end mt-sm-0 mt-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input float-end" type="checkbox" checked>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/asana.png" alt="asana" class="me-3" height="30">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-9">
                  <h6 class="mb-0">Asana</h6>
                  <small class="text-muted">Communication</small>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end mt-sm-0 mt-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input float-end" type="checkbox">
                  </div>
                </div>
              </div>
            </div>
            <!-- /Connections -->
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12">
        <div class="card">
          <h5 class="card-header pb-1">Social Accounts</h5>
          <div class="card-body">
            <p>Display content from social accounts on your site</p>
            <!-- Social Accounts -->
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/facebook.png" alt="facebook" class="me-3" height="38">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-7">
                  <h6 class="mb-0">Facebook</h6>
                  <small class="text-muted">Not Connected</small>
                </div>
                <div class="col-5 text-end mt-sm-0 mt-2">
                  <button class="btn btn-label-secondary btn-icon"><i class="ti ti-link ti-sm"></i></button>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/twitter.png" alt="twitter" class="me-3" height="38">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-7">
                  <h6 class="mb-0">Twitter</h6>
                  <a href="https://twitter.com/pixinvents" target="_blank">@Pixinvent</a>
                </div>
                <div class="col-5 text-end mt-sm-0 mt-2">
                  <button class="btn btn-label-danger btn-icon"><i class="ti ti-trash ti-sm"></i></button>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/instagram.png" alt="instagram" class="me-3" height="38">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-7">
                  <h6 class="mb-0">instagram</h6>
                  <a href="https://www.instagram.com/pixinvents/" target="_blank">@Pixinvent</a>
                </div>
                <div class="col-5 text-end mt-sm-0 mt-2">
                  <button class="btn btn-label-danger btn-icon"><i class="ti ti-trash ti-sm"></i></button>
                </div>
              </div>
            </div>
            <div class="d-flex mb-3">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/dribbble.png" alt="dribbble" class="me-3" height="38">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-7">
                  <h6 class="mb-0">Dribbble</h6>
                  <small class="text-muted">Not Connected</small>
                </div>
                <div class="col-5 text-end mt-sm-0 mt-2">
                  <button class="btn btn-label-secondary btn-icon"><i class="ti ti-link ti-sm"></i></button>
                </div>
              </div>
            </div>
            <div class="d-flex">
              <div class="flex-shrink-0">
                <img src="../../assets/img/icons/brands/behance.png" alt="behance" class="me-3" height="38">
              </div>
              <div class="flex-grow-1 row">
                <div class="col-7">
                  <h6 class="mb-0">Behance</h6>
                  <small class="text-muted">Not Connected</small>
                </div>
                <div class="col-5 text-end mt-sm-0 mt-2">
                  <button class="btn btn-label-secondary btn-icon"><i class="ti ti-link ti-sm"></i></button>
                </div>
              </div>
            </div>
            <!-- /Social Accounts -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


          </div>
          <!-- / Content -->

          
          

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
      <div>
        © <script>
        document.write(new Date().getFullYear())

        </script>
        , made with ❤️ by <a href="https://pixinvent.com" target="_blank" class="fw-medium">Pixinvent</a>
      </div>
      <div class="d-none d-lg-inline-block">
        
        <a href="https://themeforest.net/licenses/standard" class="footer-link me-4" target="_blank">License</a>
        <a href="https://1.envato.market/pixinvent_portfolio" target="_blank" class="footer-link me-4">More Themes</a>
        
        <a href="../../documentation/index.html" target="_blank" class="footer-link me-4">Documentation</a>
        
        
        <a href="https://pixinvent.ticksy.com/" target="_blank" class="footer-link d-none d-sm-inline-block">Support</a>
        
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->

          
          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    
    
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    
    
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    
  </div>
  <!-- / Layout wrapper -->

  
  

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  
  <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../../assets/vendor/libs/popper/popper.js"></script>
  <script src="../../assets/vendor/js/bootstrap.js"></script>
  <script src="../../assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="../../assets/vendor/libs/hammer/hammer.js"></script>
  <script src="../../assets/vendor/libs/i18n/i18n.js"></script>
  <script src="../../assets/vendor/libs/typeahead-js/typeahead.js"></script>
   <script src="../../assets/vendor/js/menu.js"></script>
  
  <!-- endbuild -->

  <!-- Vendors JS -->
  
  

  <!-- Main JS -->
  <script src="../../assets/js/main.js"></script>
  

  <!-- Page JS -->
  
  
  
</body>

</html>

<!-- beautify ignore:end -->

