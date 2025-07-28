<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Password Required - GoalDocs</title>
    <meta name="description" content="" />
    
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
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row justify-content-center">
                            <div class="col-md-6 col-lg-4">
                                <div class="card">
                                    <div class="card-header text-center">
                                        <h4 class="card-title mb-0">
                                            <i class="ti ti-lock text-warning me-2"></i>
                                            Password Required
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <i class="ti ti-shield-lock display-1 text-warning mb-3"></i>
                                            <p class="text-muted">This shared resource is password protected. Please enter the password to continue.</p>
                                        </div>

                                        @if($error)
                                            <div class="alert alert-danger">
                                                <i class="ti ti-alert-circle me-2"></i>
                                                {{ $error }}
                                            </div>
                                        @endif

                                        <form method="GET" action="{{ route('shared.access', $share->share_token) }}">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ti ti-unlock me-2"></i>Access Resource
                                            </button>
                                        </form>

                                        <div class="mt-4 text-center">
                                            <small class="text-muted">
                                                <i class="ti ti-info-circle me-1"></i>
                                                Contact the person who shared this resource if you don't have the password.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Share Info -->
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="ti ti-info-circle me-2"></i>
                                            Share Information
                                        </h6>
                                        <div class="small text-muted">
                                            <div><strong>Resource:</strong> {{ $share->resource_name }}</div>
                                            <div><strong>Shared by:</strong> {{ $share->creator->name ?? 'Unknown' }}</div>
                                            <div><strong>Shared on:</strong> {{ $share->created_at->format('M d, Y \a\t g:i A') }}</div>
                                            @if($share->expires_at)
                                                <div><strong>Expires:</strong> {{ $share->expires_at->format('M d, Y \a\t g:i A') }}</div>
                                            @endif
                                            @if($share->max_downloads)
                                                <div><strong>Downloads:</strong> {{ $share->download_count }} / {{ $share->max_downloads }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                <div>
                    © <script>document.write(new Date().getFullYear())</script>
                    , made with ❤️ by <a href="#" target="_blank" class="fw-semibold">GoalDocs</a>
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
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <script>
        // Focus on password field
        document.getElementById('password').focus();
    </script>
</body>
</html> 