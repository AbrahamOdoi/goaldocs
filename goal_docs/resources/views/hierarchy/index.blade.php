<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="../../../assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>{{ $displayName }} Management - GoalDocs</title>
    <meta name="description" content="Manage your {{ strtolower($displayName) }}s and positions" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../../assets/img/favicon/favicon.ico" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="../../../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../../../assets/vendor/fonts/tabler-icons.css"/>
    <link rel="stylesheet" href="../../../assets/vendor/fonts/flag-icons.css" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/typeahead-js/typeahead.css" />
    
    <!-- Custom Styles -->
    <style>
        .positions-container {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f8f9fa;
        }

        .positions-container::-webkit-scrollbar {
            width: 6px;
        }

        .positions-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }

        .positions-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .positions-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .card.h-100 {
            min-height: 400px;
        }

        .card-body.d-flex.flex-column {
            height: 100%;
        }

        .row {
            align-items: stretch;
            margin-bottom: 1.5rem;
        }
        
        .row:last-child {
            margin-bottom: 0;
        }
        
        .col-xl-4, .col-lg-6, .col-md-6 {
            margin-bottom: 1.5rem;
        }
    </style>
    
    <!-- Helpers -->
    <script src="../../../assets/vendor/js/helpers.js"></script>
    <script src="../../../assets/vendor/js/template-customizer.js"></script>
    <script src="../../../assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu -->
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
                            <span class="text-muted fw-light">{{ $displayName }} Management /</span> {{ $displayName }}s
                        </h4>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <!-- Header -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-1">Manage Your {{ $displayName }}s</h5>
                                                <p class="card-text">Create and organize {{ strtolower($displayName) }}s and positions for your team</p>
                                            </div>
                                            <a href="{{ route('hierarchy.departments.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>Add {{ $displayName }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ Header -->
                        
                        <!-- {{ $displayName }}s Grid -->
                        <div class="row">
                            @forelse($departments as $department)
                                <div class="col-xl-4 col-lg-6 col-md-6">
                                    <div class="card card-action mb-4 h-100">
                                        <div class="card-header align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3" style="background-color: {{ $department->color }};">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="ti ti-building ti-sm"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="card-action-title mb-0">{{ $department->name }}</h5>
                                                    <small class="text-muted">{{ $department->active_positions_count }} Positions</small>
                                                </div>
                                            </div>
                                            <div class="card-action-element">
                                                <div class="dropdown">
                                                    <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('hierarchy.positions.create', $department) }}">
                                                                <i class="ti ti-plus me-1"></i>Add Position
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('hierarchy.departments.edit', $department) }}">
                                                                <i class="ti ti-edit me-1"></i>Edit {{ $displayName }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            @if($department->description)
                                                <p class="card-text mb-3">{{ $department->description }}</p>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <small class="text-muted text-uppercase">Positions</small>
                                            </div>
                                            
                                            <div class="positions-container flex-grow-1" style="max-height: 200px; overflow-y: auto;">
                                                @forelse($department->positions as $position)
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0">{{ $position->name }}</h6>
                                                            <small class="text-muted">
                                                                {{ ucfirst($position->level) }} Level • {{ $position->active_users_count }} Users
                                                            </small>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                                <i class="ti ti-dots-vertical text-muted"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('hierarchy.positions.edit', $position) }}">
                                                                        <i class="ti ti-edit me-1"></i>Edit Position
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-3">
                                                        <i class="ti ti-users-off ti-lg text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">No positions yet</p>
                                                        <a href="{{ route('hierarchy.positions.create', $department) }}" class="btn btn-sm btn-outline-primary mt-2">
                                                            Add First Position
                                                        </a>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-center py-5">
                                            <i class="ti ti-building-off ti-3x text-muted mb-3"></i>
                                            <h5 class="mb-2">No {{ $displayName }}s Yet</h5>
                                            <p class="text-muted mb-4">Get started by creating your first {{ strtolower($displayName) }}</p>
                                            <a href="{{ route('hierarchy.departments.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>Create {{ $displayName }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        <!--/ {{ $displayName }}s Grid -->
                        
                    </div>
                    <!-- / Content -->
                    
                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                <div>
                                    © <script>document.write(new Date().getFullYear())</script>, made with ❤️ by <a href="https://pixinvent.com" target="_blank" class="fw-medium">Pixinvent</a>
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
    <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../assets/vendor/libs/hammer/hammer.js"></script>
    <script src="../../../assets/vendor/libs/i18n/i18n.js"></script>
    <script src="../../../assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="../../../assets/vendor/js/menu.js"></script>
    
    <!-- Main JS -->
    <script src="../../../assets/js/main.js"></script>
</body>
</html> 