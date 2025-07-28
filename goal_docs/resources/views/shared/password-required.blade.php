<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Password Required - GoalDocs</title>
    <meta name="description" content="Password protected shared resource" />
    
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
        .password-header {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            color: #333;
            padding: 3rem 0;
            text-align: center;
        }
        .password-form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-top: -2rem;
            position: relative;
            z-index: 10;
        }
        .password-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .password-input {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        .password-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        .password-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.2rem;
        }
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .share-info-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .error-alert {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .lock-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="password-header">
        <div class="container-xxl">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <i class="ti ti-shield-lock lock-icon"></i>
                    <h1 class="mb-3">Password Protected</h1>
                    <p class="lead mb-0">This shared resource requires a password to access</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <!-- Password Form -->
                <div class="password-form-card">
                    @if($error)
                        <div class="error-alert">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-alert-circle me-2"></i>
                                <strong>{{ $error }}</strong>
                            </div>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('shared.access', $share->share_token) }}">
                        <div class="password-input-group">
                            <i class="ti ti-lock password-icon"></i>
                            <input type="password" class="password-input" id="password" name="password" placeholder="Enter the password" required autofocus>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="ti ti-unlock me-2"></i>
                            Access Resource
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="ti ti-info-circle me-1"></i>
                            Contact the person who shared this resource if you don't have the password
                        </small>
                    </div>
                </div>

                <!-- Share Information -->
                <div class="share-info-card">
                    <h5 class="mb-3">
                        <i class="ti ti-info-circle me-2"></i>
                        Share Information
                    </h5>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <strong>Resource:</strong> {{ $share->resource_name }}
                        </div>
                        <div class="col-12 mb-2">
                            <strong>Shared by:</strong> {{ $share->creator->name ?? 'Unknown' }}
                        </div>
                        <div class="col-12 mb-2">
                            <strong>Shared on:</strong> {{ $share->created_at->format('M d, Y \a\t g:i A') }}
                        </div>
                        @if($share->expires_at)
                            <div class="col-12 mb-2">
                                <strong>Expires:</strong> {{ $share->expires_at->format('M d, Y \a\t g:i A') }}
                            </div>
                        @endif
                        @if($share->max_downloads)
                            <div class="col-12">
                                <strong>Downloads:</strong> {{ $share->download_count }} / {{ $share->max_downloads }}
                            </div>
                        @endif
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

    <script>
        // Focus on password field
        document.getElementById('password').focus();
        
        // Add enter key support
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    </script>
</body>
</html> 