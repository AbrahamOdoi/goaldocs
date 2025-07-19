<!DOCTYPE html>




<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr" data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Academy - Dashboard - App | Vuexy - Bootstrap Admin Template</title>

    
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
    <link rel="stylesheet" href="../../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
<link rel="stylesheet" href="../../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
<link rel="stylesheet" href="../../assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">
<link rel="stylesheet" href="../../assets/vendor/libs/apex-charts/apex-charts.css" />

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
            
            

<!-- Hour chart  -->
<div class="card bg-transparent shadow-none my-4 border-0">
  <div class="card-body row p-0 pb-3">
    <div class="col-12 col-md-8 card-separator">
      <h3>Welcome back, {{ $user->name }} 👋🏻</h3>
      <div class="col-12 col-lg-7">
        <p>Your progress this week is Awesome. let's keep it up and get a lot of points reward !</p>
      </div>
      <div class="d-flex justify-content-between flex-wrap gap-3 me-5">
        <div class="d-flex align-items-center gap-3 me-4 me-sm-0">
          <span class="bg-label-primary p-2 rounded">
            <i class='ti ti-device-laptop ti-xl'></i>
          </span>
          <div class="content-right">
            <p class="mb-0">Hours Spent</p>
            <h4 class="text-primary mb-0">34h</h4>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="bg-label-info p-2 rounded">
            <i class='ti ti-bulb ti-xl'></i>
          </span>
          <div class="content-right">
            <p class="mb-0">Test Results</p>
            <h4 class="text-info mb-0">82%</h4>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="bg-label-warning p-2 rounded">
            <i class='ti ti-discount-check ti-xl'></i>
          </span>
          <div class="content-right">
            <p class="mb-0">Course Completed </p>
            <h4 class="text-warning mb-0">14</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 ps-md-3 ps-lg-4 pt-3 pt-md-0">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div>
            <h5 class="mb-2">Time Spendings</h5>
            <p class="mb-5">Weekly report</p>
          </div>
          <div class="time-spending-chart">
            <h3 class="mb-2">231<span class="text-muted">h</span> 14<span class="text-muted">m</span> </h3>
            <span class="badge bg-label-success">+18.4%</span>
          </div>
        </div>
        <div id="leadsReportChart"></div>
      </div>
    </div>
  </div>
</div>
<!-- Hour chart End  -->

<!-- Topic and Instructors -->
<div class="row mb-4 g-4">
  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Topic you are interested in</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="topic" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="topic">
            <a class="dropdown-item" href="javascript:void(0);">Highest Views</a>
            <a class="dropdown-item" href="javascript:void(0);">See All</a>
          </div>
        </div>
      </div>
      <div class="card-body row g-3">
        <div class="col-md-6">
          <div id="horizontalBarChart"></div>
        </div>
        <div class="col-md-6 d-flex justify-content-around align-items-center">
          <div>
            <div class="d-flex align-items-baseline">
              <span class="text-primary me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">UI Design</p>
                <h5>35%</h5>
              </div>
            </div>
            <div class="d-flex align-items-baseline my-3">
              <span class="text-success me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">Music</p>
                <h5>14%</h5>
              </div>
            </div>
            <div class="d-flex align-items-baseline">
              <span class="text-danger me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">React</p>
                <h5>10%</h5>
              </div>
            </div>
          </div>

          <div>
            <div class="d-flex align-items-baseline">
              <span class="text-info me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">UX Design</p>
                <h5>20%</h5>
              </div>
            </div>
            <div class="d-flex align-items-baseline my-3">
              <span class="text-secondary me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">Animation</p>
                <h5>12%</h5>
              </div>
            </div>
            <div class="d-flex align-items-baseline">
              <span class="text-warning me-2"><i class='ti ti-circle-filled fs-6'></i></span>
              <div>
                <p class="mb-2">SEO</p>
                <h5>9%</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Popular Instructors</h5>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="popularInstructors" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="popularInstructors">
            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Share</a>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-borderless border-top">
          <thead class="border-bottom">
            <tr>
              <th>Instructors</th>
              <th class="text-end">courses</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="pt-2">
                <div class="d-flex justify-content-start align-items-center mt-lg-4">
                  <div class="avatar me-3 avatar-sm">
                    <img src="../../assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="d-flex flex-column">
                    <h6 class="mb-0">Maven Analytics</h6>
                    <small class="text-truncate text-muted">Business Intelligence</small>
                  </div>
                </div>
              </td>
              <td class="text-end pt-2">
                <div class="user-progress mt-lg-4">
                  <p class="mb-0 fw-medium">33</p>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div class="d-flex justify-content-start align-items-center">
                  <div class="avatar me-3 avatar-sm">
                    <img src="../../assets/img/avatars/2.png" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="d-flex flex-column">
                    <h6 class="mb-0">Zsazsa McCleverty</h6>
                    <small class="text-truncate text-muted">Digital Marketing</small>
                  </div>
                </div>
              </td>
              <td class="text-end">
                <div class="user-progress">
                  <p class="mb-0 fw-medium">52</p>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div class="d-flex justify-content-start align-items-center">
                  <div class="avatar me-3 avatar-sm">
                    <img src="../../assets/img/avatars/3.png" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="d-flex flex-column">
                    <h6 class="mb-0">Nathan Wagner</h6>
                    <small class="text-truncate text-muted">UI/UX Design</small>
                  </div>
                </div>
              </td>
              <td class="text-end">
                <div class="user-progress">
                  <p class="mb-0 fw-medium">12</p>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div class="d-flex justify-content-start align-items-center">
                  <div class="avatar me-3 avatar-sm">
                    <img src="../../assets/img/avatars/4.png" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="d-flex flex-column">
                    <h6 class="mb-0">Emma Bowen</h6>
                    <small class="text-truncate text-muted">React Native</small>
                  </div>
                </div>
              </td>
              <td class="text-end">
                <div class="user-progress">
                  <p class="mb-0 fw-medium">8</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>


  <div class="col-12 col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Top Courses</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="topCourses" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="topCourses">
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Download</a>
            <a class="dropdown-item" href="javascript:void(0);">View All</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex mb-4 pb-1 align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-video ti-md"></i></span>
            </div>
            <div class="row w-100 align-items-center">
              <div class="col-sm-8 col-lg-12 col-xxl-8 mb-1 mb-sm-0 mb-lg-1 mb-xxl-0">
                <p class="mb-0 fw-medium">Videography Basic Design Course</p>
              </div>
              <div class="col-sm-4 col-lg-12 col-xxl-4 d-flex justify-content-sm-end justify-content-md-start justify-content-xxl-end">
                <div class="badge bg-label-secondary">1.2k Views</div>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1 align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-info"><i class="ti ti-code ti-md"></i></span>
            </div>
            <div class="row w-100 align-items-center">
              <div class="col-sm-8 col-lg-12 col-xxl-8 mb-1 mb-sm-0 mb-lg-1 mb-xxl-0">
                <p class="mb-0 fw-medium">Basic Front-end Development Course</p>
              </div>
              <div class="col-sm-4 col-lg-12 col-xxl-4 d-flex justify-content-sm-end justify-content-md-start justify-content-xxl-end">
                <div class="badge bg-label-secondary">834 Views</div>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1 align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-success"><i class="ti ti-camera ti-md"></i></span>
            </div>
            <div class="row w-100 align-items-center">
              <div class="col-sm-8 col-lg-12 col-xxl-8 mb-1 mb-sm-0 mb-lg-1 mb-xxl-0">
                <p class="mb-0 fw-medium">Basic Fundamentals of Photography</p>
              </div>
              <div class="col-sm-4 col-lg-12 col-xxl-4 d-flex justify-content-sm-end justify-content-md-start justify-content-xxl-end">
                <div class="badge bg-label-secondary">3.7k Views</div>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1 align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-brand-dribbble ti-md"></i></span>
            </div>
            <div class="row w-100 align-items-center">
              <div class="col-sm-8 col-lg-12 col-xxl-8 mb-1 mb-sm-0 mb-lg-1 mb-xxl-0">
                <p class="mb-0 fw-medium">Advance Dribble Base Visual Design</p>
              </div>
              <div class="col-sm-4 col-lg-12 col-xxl-4 d-flex justify-content-sm-end justify-content-md-start justify-content-xxl-end">
                <div class="badge bg-label-secondary">2.5k Views</div>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-danger"><i class="ti ti-microphone-2 ti-md"></i></span>
            </div>
            <div class="row w-100 align-items-center">
              <div class="col-sm-8 col-lg-12 col-xxl-8 mb-1 mb-sm-0 mb-lg-1 mb-xxl-0">
                <p class="mb-0 fw-medium">Your First Singing Lesson</p>
              </div>
              <div class="col-sm-4 col-lg-12 col-xxl-4 d-flex justify-content-sm-end justify-content-md-start justify-content-xxl-end">
                <div class="badge bg-label-secondary">948 Views</div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>


  <div class="col-12 col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="bg-label-primary rounded-3 text-center mb-3 pt-4">
          <img class="img-fluid" src="../../assets/img/illustrations/girl-with-laptop.png" alt="Card girl image" width="140" />
        </div>
        <h4 class="mb-2 pb-1">Upcoming Webinar</h4>
        <p class="small">Next Generation Frontend Architecture Using Layout Engine And React Native Web.</p>
        <div class="row mb-3 g-3">
          <div class="col-6">
            <div class="d-flex">
              <div class="avatar flex-shrink-0 me-2">
                <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-calendar-event ti-md"></i></span>
              </div>
              <div>
                <h6 class="mb-0 text-nowrap">17 Nov 23</h6>
                <small>Date</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex">
              <div class="avatar flex-shrink-0 me-2">
                <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-clock ti-md"></i></span>
              </div>
              <div>
                <h6 class="mb-0 text-nowrap">32 minutes</h6>
                <small>Duration</small>
              </div>
            </div>
          </div>
        </div>
        <a href="javascript:void(0);" class="btn btn-primary w-100">Join the event</a>
      </div>
    </div>
  </div>


  <div class="col-12 col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Assignment Progress</h5>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          <li class="d-flex mb-3 pb-1">
            <div class="chart-progress me-3" data-color="primary" data-series="72" data-progress_variant="true"></div>
            <div class="row w-100 align-items-center">
              <div class="col-9">
                <div class="me-2">
                  <h6 class="mb-2">User experience Design</h6>
                  <small>120 Tasks</small>
                </div>
              </div>
              <div class="col-3 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                  <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1">
            <div class="chart-progress me-3" data-color="success" data-series="48" data-progress_variant="true"></div>
            <div class="row w-100 align-items-center">
              <div class="col-9">
                <div class="me-2">
                  <h6 class="mb-2">Basic fundamentals</h6>
                  <small>32 Tasks</small>
                </div>
              </div>
              <div class="col-3 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                  <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1">
            <div class="chart-progress me-3" data-color="danger" data-series="15" data-progress_variant="true"></div>
            <div class="row w-100 align-items-center">
              <div class="col-9">
                <div class="me-2">
                  <h6 class="mb-2">React native components</h6>
                  <small>182 Tasks</small>
                </div>
              </div>
              <div class="col-3 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                  <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
          </li>
          <li class="d-flex">
            <div class="chart-progress me-3" data-color="info" data-series="24" data-progress_variant="true"></div>
            <div class="row w-100 align-items-center">
              <div class="col-9">
                <div class="me-2">
                  <h6 class="mb-2">Basic of music theory</h6>
                  <small>56 Tasks</small>
                </div>
              </div>
              <div class="col-3 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                  <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

</div>
<!--  Topic and Instructors  End-->

<!-- Course datatable -->
<div class="card mb-4">
  <div class="table-responsive mb-3">
    <table class="table datatables-academy-course">
      <thead class="border-top">
        <tr>
          <th></th>
          <th></th>
          <th>Course Name</th>
          <th>Time</th>
          <th class="w-25">Progress</th>
          <th class="w-25">Status</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Course datatable End -->


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
  <script src="../../assets/vendor/libs/moment/moment.js"></script>
<script src="../../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="../../assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="../../assets/js/main.js"></script>
  

  <!-- Page JS -->
  <script src="../../assets/js/app-academy-dashboard.js"></script>
  
</body>

</html>

<!-- beautify ignore:end -->

