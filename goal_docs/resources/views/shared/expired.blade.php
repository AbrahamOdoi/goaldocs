<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Share Unavailable - GoalDocs</title>
    <meta name="description" content="This shared resource is no longer available" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    
    <!-- Page CSS -->
    
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    
    <style>
        .expired-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        .expired-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-top: -2rem;
            position: relative;
            z-index: 10;
        }
        .expired-icon {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
        .reason-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #ff6b6b;
        }
        .contact-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="expired-header">
        <div class="container-xxl">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <i class="ti ti-clock-off expired-icon"></i>
                    <h1 class="mb-3">Share Unavailable</h1>
                    <p class="lead mb-0">This shared resource is no longer accessible</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Expired Content -->
                <div class="expired-content">
                    <div class="text-center mb-4">
                        <h4 class="mb-3">Why is this share unavailable?</h4>
                        <p class="text-muted">This could be due to one of the following reasons:</p>
                    </div>

                    <!-- Reason Cards -->
                    <div class="reason-card">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-calendar-off me-3 text-danger" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">Share Expired</h6>
                                <p class="mb-0 text-muted">This share had an expiration date that has passed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="reason-card">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-download-off me-3 text-danger" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">Download Limit Reached</h6>
                                <p class="mb-0 text-muted">The maximum number of downloads for this share has been reached.</p>
                            </div>
                        </div>
                    </div>

                    <div class="reason-card">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-shield-off me-3 text-danger" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">Share Revoked</h6>
                                <p class="mb-0 text-muted">The person who shared this resource has revoked access.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-card">
                        <h5 class="mb-3">
                            <i class="ti ti-user me-2"></i>
                            Need Access?
                        </h5>
                        <p class="mb-3">If you need access to this resource, please contact the person who originally shared it with you.</p>
                        
                        @if(isset($share) && $share->creator)
                            <div class="d-flex align-items-center">
                                <i class="ti ti-mail me-2"></i>
                                <span>Contact: {{ $share->creator->name ?? 'Unknown' }}</span>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <small class="opacity-75">
                                <i class="ti ti-info-circle me-1"></i>
                                Share details are only shown if you previously had access to this resource.
                            </small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                            <i class="ti ti-arrow-left me-2"></i>
                            Go Back
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="ti ti-home me-2"></i>
                            Go Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme mt-5">
        <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
                <div>
                    © <script>document.write(new Date().getFullYear())</script>
                    , made with ❤️ by <a href="#" target="_blank" class="fw-semibold">GoalDocs</a>
                </div>
                <div>
                    <small class="text-muted">Secure file sharing platform</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/vendor/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/vendor/js/dashboards-analytics.js') }}"></script>
</body>
</html> 