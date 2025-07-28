<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ $resource->name }} - Shared via GoalDocs</title>
    <meta name="description" content="Shared resource via GoalDocs" />
    
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
        .shared-resource-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .resource-preview {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .permission-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0.25rem;
        }
        .permission-badge.granted {
            background: #e8f5e8;
            color: #2d5a2d;
            border: 1px solid #c3e6c3;
        }
        .permission-badge.denied {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        .share-info-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .file-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .stat-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .download-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .preview-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="shared-resource-header">
        <div class="container-xxl">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="ti ti-share me-3"></i>
                        Shared Resource
                    </h1>
                    <p class="mb-0 opacity-75">Accessing content shared via GoalDocs</p>
                </div>
                <div class="col-md-4 text-md-end">
                    @if($share->hasPermission('download') && $resource instanceof \App\Models\File)
                        <a href="{{ route('shared.download', $share->share_token) }}{{ $share->password_protected ? '?password=' . request('password') : '' }}" class="download-btn">
                            <i class="ti ti-download"></i>
                            Download File
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-xxl">
        <!-- Share Information -->
        <div class="share-info-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="ti ti-user me-2"></i>
                        Shared by {{ $share->creator->name ?? 'Unknown' }}
                    </h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <small class="opacity-75">
                                <i class="ti ti-calendar me-1"></i>
                                {{ $share->created_at->format('M d, Y \a\t g:i A') }}
                            </small>
                        </div>
                        @if($share->expires_at)
                            <div class="col-sm-6">
                                <small class="opacity-75">
                                    <i class="ti ti-clock me-1"></i>
                                    Expires {{ $share->expires_at->format('M d, Y \a\t g:i A') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    @if($share->max_downloads)
                        <div class="d-flex align-items-center justify-content-md-end">
                            <i class="ti ti-download me-2"></i>
                            <span>{{ $share->download_count }} / {{ $share->max_downloads }} downloads</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Resource Details -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Resource Preview -->
                <div class="resource-preview">
                    <div class="mb-3">
                        <h3 class="mb-2">{{ $resource->name }}</h3>
                        @if($resource->description)
                            <p class="text-muted mb-0">{{ $resource->description }}</p>
                        @endif
                    </div>
                    
                    @if($resource instanceof \App\Models\File)
                        @if($resource->is_image)
                            <div class="mb-4">
                                <img src="{{ $resource->preview_url }}{{ $share->password_protected ? '?password=' . request('password') : '' }}" alt="{{ $resource->name }}" class="preview-image">
                            </div>
                        @else
                            <div class="mb-4">
                                <i class="ti ti-file display-1 text-muted"></i>
                                <h5 class="mt-3">{{ $resource->name }}</h5>
                                <p class="text-muted">File preview not available</p>
                            </div>
                        @endif
                        
                        <!-- File Statistics -->
                        <div class="file-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ $resource->human_size }}</div>
                                <div class="stat-label">File Size</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $resource->mime_type }}</div>
                                <div class="stat-label">File Type</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $resource->created_at->format('M d') }}</div>
                                <div class="stat-label">Uploaded</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $resource->download_count ?? 0 }}</div>
                                <div class="stat-label">Downloads</div>
                            </div>
                        </div>
                    @elseif($resource instanceof \App\Models\Folder)
                        <div class="mb-4">
                            <i class="ti ti-folder display-1 text-warning"></i>
                            <h5 class="mt-3">{{ $resource->name }}</h5>
                            <p class="text-muted">
                                This folder contains {{ $resource->activeFiles->count() + $resource->activeChildren->count() }} items
                            </p>
                        </div>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Folder contents are not displayed in external shares for security reasons.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Permissions Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti ti-shield me-2"></i>
                            Your Permissions
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $permissions = $share->permissions;
                        @endphp
                        <div class="d-flex flex-wrap">
                            <div class="permission-badge {{ $permissions['view'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-eye me-2"></i>
                                View
                            </div>
                            <div class="permission-badge {{ $permissions['download'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-download me-2"></i>
                                Download
                            </div>
                            <div class="permission-badge {{ $permissions['edit'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-edit me-2"></i>
                                Edit
                            </div>
                            <div class="permission-badge {{ $permissions['upload'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-upload me-2"></i>
                                Upload
                            </div>
                            <div class="permission-badge {{ $permissions['delete'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-trash me-2"></i>
                                Delete
                            </div>
                            <div class="permission-badge {{ $permissions['reshare'] ? 'granted' : 'denied' }}">
                                <i class="ti ti-share me-2"></i>
                                Reshare
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="ti ti-info-circle me-1"></i>
                                These permissions are specific to this shared resource
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                @if($share->hasPermission('download') && $resource instanceof \App\Models\File)
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <a href="{{ route('shared.download', $share->share_token) }}{{ $share->password_protected ? '?password=' . request('password') : '' }}" class="download-btn w-100">
                                <i class="ti ti-download"></i>
                                Download File
                            </a>
                        </div>
                    </div>
                @endif
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