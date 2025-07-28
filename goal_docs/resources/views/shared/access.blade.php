<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Shared Resource - GoalDocs</title>
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
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="ti ti-share me-2"></i>
                                            Shared Resource
                                        </h5>
                                        <div class="d-flex gap-2">
                                            @if($share->hasPermission('download') && $resource instanceof \App\Models\File)
                                                <a href="{{ route('shared.download', $share->share_token) }}" class="btn btn-primary btn-sm">
                                                    <i class="ti ti-download me-1"></i>Download
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Share Info -->
                                        <div class="alert alert-info">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-info-circle me-2"></i>
                                                <div>
                                                    <strong>Shared by:</strong> {{ $share->creator->name ?? 'Unknown' }}<br>
                                                    <strong>Shared on:</strong> {{ $share->created_at->format('M d, Y \a\t g:i A') }}
                                                    @if($share->expires_at)
                                                        <br><strong>Expires:</strong> {{ $share->expires_at->format('M d, Y \a\t g:i A') }}
                                                    @endif
                                                    @if($share->max_downloads)
                                                        <br><strong>Downloads:</strong> {{ $share->download_count }} / {{ $share->max_downloads }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Resource Details -->
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4>{{ $resource->name }}</h4>
                                                @if($resource->description)
                                                    <p class="text-muted">{{ $resource->description }}</p>
                                                @endif
                                                
                                                @if($resource instanceof \App\Models\File)
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <strong>File Size:</strong> {{ $resource->human_size }}<br>
                                                            <strong>Type:</strong> {{ $resource->mime_type }}<br>
                                                            <strong>Uploaded:</strong> {{ $resource->created_at->format('M d, Y') }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Downloads:</strong> {{ $resource->download_count ?? 0 }}<br>
                                                            <strong>Last Accessed:</strong> {{ $resource->last_accessed_at ? $resource->last_accessed_at->format('M d, Y') : 'Never' }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- File Preview -->
                                                    @if($resource->is_image)
                                                        <div class="mt-3">
                                                            <img src="{{ $resource->preview_url }}" alt="{{ $resource->name }}" class="img-fluid rounded" style="max-width: 100%; max-height: 400px; object-fit: contain;">
                                                        </div>
                                                    @elseif($resource->is_document)
                                                        <div class="mt-3">
                                                            <div class="alert alert-warning">
                                                <i class="ti ti-file-text me-2"></i>
                                                Document preview not available. Please download to view.
                                            </div>
                                        </div>
                                    @endif
                                @elseif($resource instanceof \App\Models\Folder)
                                    <div class="mt-3">
                                        <div class="alert alert-info">
                                            <i class="ti ti-folder me-2"></i>
                                            This is a shared folder containing {{ $resource->activeFiles->count() + $resource->activeChildren->count() }} items.
                                        </div>
                                        <p class="text-muted">Folder contents are not displayed in external shares for security reasons.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <!-- Permissions -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-shield me-2"></i>Your Permissions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $permissions = $share->permissions;
                                        @endphp
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-eye {{ $permissions['view'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['view'] ? '' : 'text-muted' }}">View</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-download {{ $permissions['download'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['download'] ? '' : 'text-muted' }}">Download</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-edit {{ $permissions['edit'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['edit'] ? '' : 'text-muted' }}">Edit</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-upload {{ $permissions['upload'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['upload'] ? '' : 'text-muted' }}">Upload</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-trash {{ $permissions['delete'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['delete'] ? '' : 'text-muted' }}">Delete</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-share {{ $permissions['reshare'] ? 'text-success' : 'text-muted' }} me-2"></i>
                                                <span class="{{ $permissions['reshare'] ? '' : 'text-muted' }}">Reshare</span>
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
    // Track view
    fetch('{{ route("shared.access", $share->share_token) }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
</script>
</body>
</html> 