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
    <title>{{ $file->name }} - Preview - GoalDocs</title>
    <meta name="description" content="Preview {{ $file->name }} in GoalDocs" />
    
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
        .preview-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .preview-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .preview-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .preview-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .preview-viewport {
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .preview-pdf {
            width: 100%;
            height: 80vh;
            border: none;
        }
        
        .preview-text {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-x: auto;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .preview-office {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .preview-office .office-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .metadata-section {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .metadata-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .metadata-item:last-child {
            border-bottom: none;
        }
        
        .metadata-label {
            font-weight: 600;
            color: #495057;
        }
        
        .metadata-value {
            color: #6c757d;
            text-align: right;
        }
        
        .related-files {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .related-file-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .related-file-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: inherit;
        }
        
        .related-file-icon {
            margin-right: 0.75rem;
            color: #6c757d;
        }
        
        .related-file-info {
            flex-grow: 1;
        }
        
        .related-file-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .related-file-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .zoom-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .zoom-btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        
        .zoom-level {
            font-size: 0.875rem;
            color: #6c757d;
            min-width: 60px;
            text-align: center;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .loading-spinner.show {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #7367f0;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            text-align: center;
            padding: 2rem;
            color: #dc3545;
        }
        
        .error-message i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .preview-toolbar {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .preview-actions {
                justify-content: center;
            }
            
            .zoom-controls {
                justify-content: center;
            }
            
            .preview-viewport {
                min-height: 300px;
                padding: 1rem;
            }
            
            .preview-image {
                max-height: 60vh;
            }
            
            .preview-pdf {
                height: 60vh;
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
                            <div class="col-lg-8">
                                <!-- Document Preview -->
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <i class="ti {{ $file->icon }} me-2"></i>
                                                    {{ $file->name }}
                                                </h5>
                                                <p class="card-subtitle text-muted">
                                                    {{ $file->human_size }} • {{ $file->mime_type }}
                                                </p>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('files.download', $file) }}" class="btn btn-primary">
                                                    <i class="ti ti-download me-1"></i>
                                                    Download
                                                </a>
                                                <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ti ti-arrow-left me-1"></i>
                                                    Back
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="preview-container">
                                            <div class="preview-content">
                                                <div class="preview-toolbar">
                                                    <div class="preview-actions">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="shareFile()">
                                                            <i class="ti ti-share me-1"></i>
                                                            Share
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="showMetadata()">
                                                            <i class="ti ti-info-circle me-1"></i>
                                                            Info
                                                        </button>
                                                        @if($preview['type'] === 'pdf')
                                                            <div class="zoom-controls">
                                                                <button class="zoom-btn" onclick="zoomOut()">
                                                                    <i class="ti ti-minus"></i>
                                                                </button>
                                                                <span class="zoom-level" id="zoomLevel">100%</span>
                                                                <button class="zoom-btn" onclick="zoomIn()">
                                                                    <i class="ti ti-plus"></i>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted small">
                                                        Preview generated on {{ now()->format('M j, Y g:i A') }}
                                                    </div>
                                                </div>
                                                
                                                <div class="preview-viewport" id="previewViewport">
                                                    <div class="loading-spinner" id="loadingSpinner">
                                                        <div class="spinner"></div>
                                                        <p>Loading preview...</p>
                                                    </div>
                                                    
                                                    <div id="previewContent" style="display: none;">
                                                        @if($preview['type'] === 'image')
                                                            <img src="{{ $preview['preview_url'] }}" 
                                                                 alt="{{ $file->name }}" 
                                                                 class="preview-image" 
                                                                 id="previewImage">
                                                        @elseif($preview['type'] === 'pdf')
                                                            <iframe src="{{ route('files.download', $file) }}" 
                                                                    class="preview-pdf" 
                                                                    id="previewPdf"></iframe>
                                                        @elseif($preview['type'] === 'text')
                                                            <div class="preview-text" id="previewText">
                                                                {{ $preview['content'] }}
                                                                @if($preview['has_more'])
                                                                    <div class="mt-3">
                                                                        <button class="btn btn-sm btn-outline-primary" onclick="loadFullText()">
                                                                            Load Full Content
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @elseif($preview['type'] === 'office')
                                                            <div class="preview-office">
                                                                <div class="office-icon">
                                                                    <i class="ti ti-file-text"></i>
                                                                </div>
                                                                <h5>{{ $preview['metadata']['document_type'] ?? 'Office Document' }}</h5>
                                                                <p class="text-muted">{{ $preview['content'] }}</p>
                                                                @if($preview['has_more'])
                                                                    <button class="btn btn-outline-primary" onclick="loadFullText()">
                                                                        Load Full Content
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @elseif($preview['type'] === 'error')
                                                            <div class="error-message">
                                                                <i class="ti ti-alert-circle"></i>
                                                                <h5>Preview Not Available</h5>
                                                                <p>{{ $preview['error'] ?? 'Unable to generate preview for this file type.' }}</p>
                                                                <a href="{{ route('files.download', $file) }}" class="btn btn-primary">
                                                                    Download File
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="preview-office">
                                                                <div class="office-icon">
                                                                    <i class="ti ti-file"></i>
                                                                </div>
                                                                <h5>Preview Not Available</h5>
                                                                <p class="text-muted">This file type does not support preview.</p>
                                                                <a href="{{ route('files.download', $file) }}" class="btn btn-primary">
                                                                    Download File
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <!-- File Metadata -->
                                <div class="metadata-section">
                                    <h6 class="mb-3">
                                        <i class="ti ti-info-circle me-2"></i>
                                        File Information
                                    </h6>
                                    @foreach($metadata['basic'] as $label => $value)
                                        <div class="metadata-item">
                                            <span class="metadata-label">{{ $label }}</span>
                                            <span class="metadata-value">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                    
                                    @if(isset($metadata['tags']) && count($metadata['tags']) > 0)
                                        <div class="metadata-item">
                                            <span class="metadata-label">Tags</span>
                                            <span class="metadata-value">
                                                @foreach($metadata['tags'] as $tag)
                                                    <span class="badge bg-primary me-1">{{ $tag }}</span>
                                                @endforeach
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Related Files -->
                                @if(count(array_filter($relatedFiles)) > 0)
                                    <div class="related-files">
                                        <h6 class="mb-3">
                                            <i class="ti ti-files me-2"></i>
                                            Related Files
                                        </h6>
                                        
                                        @if(isset($relatedFiles['same_folder']) && $relatedFiles['same_folder']->count() > 0)
                                            <h6 class="text-muted mb-2">Same Folder</h6>
                                            @foreach($relatedFiles['same_folder'] as $relatedFile)
                                                <a href="{{ route('files.preview', $relatedFile) }}" class="related-file-item">
                                                    <i class="ti {{ $relatedFile->icon }} related-file-icon"></i>
                                                    <div class="related-file-info">
                                                        <div class="related-file-name">{{ $relatedFile->name }}</div>
                                                        <div class="related-file-meta">{{ $relatedFile->human_size }}</div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endif
                                        
                                        @if(isset($relatedFiles['same_type']) && $relatedFiles['same_type']->count() > 0)
                                            <h6 class="text-muted mb-2 mt-3">Same Type</h6>
                                            @foreach($relatedFiles['same_type'] as $relatedFile)
                                                <a href="{{ route('files.preview', $relatedFile) }}" class="related-file-item">
                                                    <i class="ti {{ $relatedFile->icon }} related-file-icon"></i>
                                                    <div class="related-file-info">
                                                        <div class="related-file-name">{{ $relatedFile->name }}</div>
                                                        <div class="related-file-meta">{{ $relatedFile->human_size }}</div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
        let currentZoom = 100;
        let fullTextLoaded = false;
        
        $(document).ready(function() {
            // Show preview content after loading
            setTimeout(function() {
                $('#loadingSpinner').removeClass('show');
                $('#previewContent').show();
            }, 500);
            
            // Initialize zoom for images
            if ($('#previewImage').length > 0) {
                initializeImageZoom();
            }
        });
        
        function zoomIn() {
            if (currentZoom < 200) {
                currentZoom += 25;
                updateZoom();
            }
        }
        
        function zoomOut() {
            if (currentZoom > 50) {
                currentZoom -= 25;
                updateZoom();
            }
        }
        
        function updateZoom() {
            $('#zoomLevel').text(currentZoom + '%');
            $('#previewPdf').css('transform', `scale(${currentZoom / 100})`);
        }
        
        function initializeImageZoom() {
            const image = $('#previewImage');
            let isZoomed = false;
            
            image.on('click', function() {
                if (isZoomed) {
                    $(this).css({
                        'transform': 'scale(1)',
                        'cursor': 'zoom-in'
                    });
                    isZoomed = false;
                } else {
                    $(this).css({
                        'transform': 'scale(1.5)',
                        'cursor': 'zoom-out'
                    });
                    isZoomed = true;
                }
            });
            
            image.css('cursor', 'zoom-in');
        }
        
        function loadFullText() {
            if (fullTextLoaded) return;
            
            $('#loadingSpinner').addClass('show');
            
            $.ajax({
                url: '{{ route("files.preview.full-text", $file) }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        if ($('#previewText').length > 0) {
                            $('#previewText').html(response.content);
                        } else if ($('.preview-office').length > 0) {
                            $('.preview-office p').html(response.content);
                        }
                        fullTextLoaded = true;
                    }
                },
                error: function() {
                    alert('Error loading full text content');
                },
                complete: function() {
                    $('#loadingSpinner').removeClass('show');
                }
            });
        }
        
        function shareFile() {
            // Implementation for sharing
            alert('Share functionality will be implemented in the next version');
        }
        
        function showMetadata() {
            // Implementation for showing detailed metadata
            alert('Detailed metadata view will be implemented in the next version');
        }
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '=':
                    case '+':
                        e.preventDefault();
                        zoomIn();
                        break;
                    case '-':
                        e.preventDefault();
                        zoomOut();
                        break;
                    case '0':
                        e.preventDefault();
                        currentZoom = 100;
                        updateZoom();
                        break;
                }
            }
        });
    </script>
</body>
</html> 