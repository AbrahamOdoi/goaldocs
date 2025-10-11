<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=0.5, maximum-scale=3.0" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="GoalDocs" />
    <meta name="theme-color" content="#7367f0" />
    <title>{{ $file->name }} - Version History - GoalDocs</title>
    <meta name="description" content="Version history for {{ $file->name }}" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="manifest" href="{{ asset('manifest.json') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />

    <!-- Page CSS -->
    <style>
        .version-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .version-timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .version-item {
            position: relative;
            margin-bottom: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .version-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .version-item::before {
            content: '';
            position: absolute;
            left: -2.5rem;
            top: 1.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #7367f0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e9ecef;
        }
        
        .version-item.current::before {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }
        
        .version-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .version-info {
            flex-grow: 1;
        }
        
        .version-number {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .version-meta {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .version-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .version-content {
            margin-bottom: 1rem;
        }
        
        .version-notes {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 6px;
            border-left: 4px solid #7367f0;
            margin-bottom: 1rem;
        }
        
        .version-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .comparison-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .comparison-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .comparison-content {
            padding: 1.5rem;
        }
        
        .diff-view {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .diff-line {
            padding: 2px 0;
            white-space: pre-wrap;
        }
        
        .diff-added {
            background: #d4edda;
            color: #155724;
        }
        
        .diff-removed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .diff-modified {
            background: #fff3cd;
            color: #856404;
        }
        
        .upload-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .upload-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .upload-content {
            padding: 1.5rem;
        }
        
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: #7367f0;
            background: #f8f9ff;
        }
        
        .upload-zone.dragover {
            border-color: #7367f0;
            background: #f0f2ff;
        }
        
        .rollback-modal .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .rollback-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .rollback-warning i {
            color: #f39c12;
            margin-right: 0.5rem;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .version-timeline {
                padding-left: 1rem;
            }
            
            .version-timeline::before {
                left: 0.5rem;
            }
            
            .version-item::before {
                left: -1.5rem;
            }
            
            .version-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .version-actions {
                align-self: stretch;
                justify-content: center;
            }
            
            .version-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('partials.sidebar')
            
            <!-- Layout page -->
            <div class="layout-page">
                @include('partials.navbar')
                
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-12">
                                <!-- File Header -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <i class="ti {{ $file->icon }} me-2"></i>
                                                    {{ $file->name }}
                                                </h5>
                                                <p class="card-subtitle text-muted">
                                                    Version History • {{ $file->human_size }} • {{ $file->mime_type }}
                                                </p>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('files.preview', $file) }}" class="btn btn-outline-primary">
                                                    <i class="ti ti-eye me-1"></i>
                                                    Preview
                                                </a>
                                                <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ti ti-arrow-left me-1"></i>
                                                    Back
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Version Statistics -->
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="ti ti-chart-bar me-2"></i>
                                            Version Statistics
                                        </h6>
                                        <div class="version-stats">
                                            <div class="stat-item">
                                                <div class="stat-value">{{ $versionStats['total_versions'] }}</div>
                                                <div class="stat-label">Total Versions</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value">{{ $versionStats['current_version'] }}</div>
                                                <div class="stat-label">Current Version</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value">{{ number_format($versionStats['total_size'] / 1024 / 1024, 2) }} MB</div>
                                                <div class="stat-label">Total Size</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value">{{ number_format($versionStats['average_size'] / 1024 / 1024, 2) }} MB</div>
                                                <div class="stat-label">Avg Size</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Upload New Version -->
                                <div class="upload-panel">
                                    <div class="upload-header">
                                        <h6 class="mb-0">
                                            <i class="ti ti-upload me-2"></i>
                                            Upload New Version
                                        </h6>
                                    </div>
                                    <div class="upload-content">
                                        <form id="uploadVersionForm" enctype="multipart/form-data">
                                            <div class="upload-zone" id="uploadZone">
                                                <i class="ti ti-cloud-upload display-4 text-muted mb-3"></i>
                                                <h5>Drop file here or click to upload</h5>
                                                <p class="text-muted">Upload a new version of this file</p>
                                                <input type="file" id="versionFile" name="file" style="display: none;" accept="{{ $file->mime_type }}">
                                            </div>
                                            
                                            <div class="mt-3">
                                                <label for="changeNotes" class="form-label">Change Notes (Optional)</label>
                                                <textarea class="form-control" id="changeNotes" name="change_notes" rows="3" placeholder="Describe what changed in this version..."></textarea>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                                    <i class="ti ti-upload me-1"></i>
                                                    Upload New Version
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Version Timeline -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-history me-2"></i>
                                            Version History
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="version-timeline">
                                            @foreach($versionHistory as $version)
                                                <div class="version-item {{ $version['is_current'] ? 'current' : '' }}">
                                                    <div class="version-header">
                                                        <div class="version-info">
                                                            <div class="version-number">
                                                                Version {{ $version['version_number'] }}
                                                                @if($version['is_current'])
                                                                    <span class="badge bg-success ms-2">Current</span>
                                                                @endif
                                                            </div>
                                                            <div class="version-meta">
                                                                <i class="ti ti-user me-1"></i>
                                                                {{ $version['uploaded_by'] }} • 
                                                                <i class="ti ti-calendar me-1"></i>
                                                                {{ $version['uploaded_at'] }} • 
                                                                <i class="ti ti-file me-1"></i>
                                                                {{ $version['human_size'] }}
                                                            </div>
                                                        </div>
                                                        <div class="version-actions">
                                                            <a href="{{ $version['download_url'] }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="ti ti-download"></i>
                                                            </a>
                                                            @if(!$version['is_current'])
                                                                <button class="btn btn-sm btn-outline-warning" onclick="compareVersion({{ $version['id'] }})">
                                                                    <i class="ti ti-git-compare"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" onclick="rollbackVersion({{ $version['id'] }}, {{ $version['version_number'] }})">
                                                                    <i class="ti ti-rotate"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @if($version['change_notes'])
                                                        <div class="version-notes">
                                                            <strong>Change Notes:</strong> {{ $version['change_notes'] }}
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="version-content">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <small class="text-muted">File Size:</small>
                                                                <div>{{ $version['human_size'] }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <small class="text-muted">Uploaded:</small>
                                                                <div>{{ $version['uploaded_at'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
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

    <!-- Rollback Confirmation Modal -->
    <div class="modal fade" id="rollbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>
                        Confirm Rollback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="rollback-warning">
                        <i class="ti ti-alert-triangle"></i>
                        <strong>Warning:</strong> Rolling back will replace the current version with the selected version. This action cannot be undone.
                    </div>
                    <p>Are you sure you want to rollback to <strong id="rollbackVersionNumber"></strong>?</p>
                    <p class="text-muted">The current version will be preserved in the version history.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmRollback">
                        <i class="ti ti-rotate me-1"></i>
                        Confirm Rollback
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script>
        let selectedVersionId = null;
        
        $(document).ready(function() {
            // File upload handling
            const uploadZone = $('#uploadZone');
            const fileInput = $('#versionFile');
            const uploadBtn = $('#uploadBtn');
            
            uploadZone.on('click', function() {
                fileInput.click();
            });
            
            uploadZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            uploadZone.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            uploadZone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    fileInput[0].files = files;
                    handleFileSelect();
                }
            });
            
            fileInput.on('change', handleFileSelect);
            
            function handleFileSelect() {
                const file = fileInput[0].files[0];
                if (file) {
                    uploadZone.html(`
                        <i class="ti ti-file-check text-success display-4 mb-3"></i>
                        <h5>${file.name}</h5>
                        <p class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    `);
                    uploadBtn.prop('disabled', false);
                }
            }
            
            // Form submission
            $('#uploadVersionForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append('file', fileInput[0].files[0]);
                formData.append('change_notes', $('#changeNotes').val());
                formData.append('_token', '{{ csrf_token() }}');
                
                uploadBtn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i>Uploading...');
                
                $.ajax({
                    url: '{{ route("files.version.upload", $file) }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'New version uploaded successfully!');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert('error', response.error || 'Upload failed');
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Upload failed';
                        showAlert('error', error);
                    },
                    complete: function() {
                        uploadBtn.prop('disabled', false).html('<i class="ti ti-upload me-1"></i>Upload New Version');
                    }
                });
            });
        });
        
        function compareVersion(versionId) {
            // Implementation for version comparison
            window.open(`{{ route('files.version.comparison', $file) }}?version1={{ $versionHistory[0]['id'] }}&version2=${versionId}`, '_blank');
        }
        
        function rollbackVersion(versionId, versionNumber) {
            selectedVersionId = versionId;
            $('#rollbackVersionNumber').text(`Version ${versionNumber}`);
            $('#rollbackModal').modal('show');
        }
        
        $('#confirmRollback').on('click', function() {
            if (!selectedVersionId) return;
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i>Rolling back...');
            
            $.ajax({
                url: '{{ route("files.version.rollback", $file) }}',
                method: 'POST',
                data: {
                    version_id: selectedVersionId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $('#rollbackModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('error', response.error || 'Rollback failed');
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Rollback failed';
                    showAlert('error', error);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="ti ti-rotate me-1"></i>Confirm Rollback');
                }
            });
        });
        
        function showAlert(type, message) {
            // Implementation for showing alerts
            alert(message);
        }
    </script>
</body>
</html> 