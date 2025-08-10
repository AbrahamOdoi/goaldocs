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
    <title>File Manager - GoalDocs</title>
    <meta name="description" content="Manage your files and folders" />
    
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
    
    <!-- Load jQuery first to avoid conflicts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Essential head scripts only -->
    <script>
        // Minimal config to avoid conflicts
        window.templateCustomizer = window.templateCustomizer || {};
        window.config = window.config || { assets: { path: '{{ asset("assets") }}/' } };
    </script>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Cache-busting meta tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Debug info -->
    <script>
        console.log('Page loaded at:', new Date().toISOString());
        console.log('Folders count:', {{ $folders->count() }});
        console.log('Files count:', {{ $files->count() }});
        
        // Function to clear service worker cache
        function clearServiceWorkerCache() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then((registration) => {
                    registration.active.postMessage({ type: 'CLEAR_CACHE' });
                    console.log('Service Worker: Cache clear message sent');
                });
            }
        }
    </script>
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
    <!-- File Manager Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="ti ti-files me-2"></i>
                            File Manager
                        </h5>
                        <small class="text-muted">
                            @if(auth()->user()->type === 'individual')
                                Manage your personal files and folders
                            @else
                                Manage your {{ strtolower(auth()->user()->type) }} files and folders
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="ti ti-folder-plus me-1"></i>
                            New Folder
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="ti ti-upload me-1"></i>
                            Upload Files
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    @foreach($breadcrumbs as $breadcrumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search files and folders...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload Dropzone -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="dropzone" class="card border-dashed border-primary d-none">
                <div class="card-body text-center py-5">
                    <i class="ti ti-cloud-upload display-1 text-primary mb-3"></i>
                    <h4 class="text-primary">Drop files here to upload</h4>
                    <p class="text-muted">Or click the upload button above</p>
                </div>
            </div>
        </div>
    </div>

    <!-- File Browser -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($folders->isEmpty() && $files->isEmpty())
                        <div class="text-center py-5">
                            <i class="ti ti-folder-off display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">This folder is empty</h4>
                            <p class="text-muted">Create a new folder or upload files to get started</p>
                        </div>
                    @else
                        <!-- Folders -->
                        @if($folders->isNotEmpty())
                            <h6 class="text-uppercase text-muted mb-3">
                                <i class="ti ti-folders me-1"></i>
                                Folders ({{ $folders->count() }})
                            </h6>
                            <div class="row mb-4">
                                @foreach($folders as $folder)
                                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                                        <div class="card folder-card h-100" data-folder-id="{{ $folder->id }}">
                                            <div class="card-body text-center p-2 p-sm-3">
                                                <div class="dropdown position-absolute top-0 end-0 mt-1 me-1 mt-sm-2 me-sm-2">
                                                    <button class="btn btn-sm btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editFolder({{ $folder->id }}, '{{ $folder->name }}', '{{ $folder->description ?? '' }}')">
                                                            <i class="ti ti-edit me-2"></i>Edit
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="managePermissions('folder', {{ $folder->id }}, '{{ $folder->name }}')">
                                                            <i class="ti ti-lock me-2"></i>Permissions
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="shareResource('folder', {{ $folder->id }}, '{{ $folder->name }}')">
                                                            <i class="ti ti-share me-2"></i>Share
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#commentsModal" data-resource-type="folder" data-resource-id="{{ $folder->id }}" data-resource-name="{{ $folder->name }}">
                                                            <i class="ti ti-message-circle me-2"></i>Comments
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteFolder({{ $folder->id }})">
                                                            <i class="ti ti-trash me-2"></i>Delete
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <a href="{{ route('files.index', ['folder' => $folder->id]) }}" class="text-decoration-none">
                                                    <i class="ti ti-folder text-warning display-1 mb-3"></i>
                                                    <h6 class="card-title text-truncate">{{ $folder->name }}</h6>
                                                    @if($folder->description)
                                                        <p class="text-muted small mb-1" style="font-size: 0.75rem; line-height: 1.2; max-height: 2.4rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $folder->description }}</p>
                                                    @endif
                                                    <small class="text-muted">{{ $folder->activeFiles->count() + $folder->activeChildren->count() }} items</small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Files -->
                        @if($files->isNotEmpty())
                            <h6 class="text-uppercase text-muted mb-3">
                                <i class="ti ti-files me-1"></i>
                                Files ({{ $files->count() }})
                            </h6>
                            <div class="row">
                                @foreach($files as $file)
                                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                                        <div class="card file-card h-100" data-file-id="{{ $file->id }}">
                                            <div class="card-body text-center p-2 p-sm-3">
                                                <div class="dropdown position-absolute top-0 end-0 mt-1 me-1 mt-sm-2 me-sm-2">
                                                    <button class="btn btn-sm btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="openFilePreview({{ $file->id }}, '{{ addslashes($file->original_name) }}', '{{ $file->preview_url }}', '{{ $file->mime_type }}', {{ $file->file_size }})">
                                                            <i class="ti ti-eye me-2"></i>Preview
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ $file->download_url }}">
                                                            <i class="ti ti-download me-2"></i>Download
                                                        </a></li>
                                                        @if($file->is_image || $file->is_document)
                                                            <li><a class="dropdown-item" href="{{ $file->preview_url }}" target="_blank">
                                                                <i class="ti ti-external-link me-2"></i>Open in New Tab
                                                            </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFavorite({{ $file->id }}, null)">
                                                            <i class="ti ti-star me-2"></i>Favorite
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="showTagModal({{ $file->id }})">
                                                            <i class="ti ti-tag me-2"></i>Add Tag
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="editFile({{ $file->id }}, '{{ $file->name }}', '{{ $file->description ?? '' }}')">
                                                            <i class="ti ti-edit me-2"></i>Edit
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="moveFile({{ $file->id }})">
                                                            <i class="ti ti-folder-symlink me-2"></i>Move
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="managePermissions('file', {{ $file->id }}, '{{ $file->name }}')">
                                                            <i class="ti ti-lock me-2"></i>Permissions
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="shareResource('file', {{ $file->id }}, '{{ $file->name }}')">
                                                            <i class="ti ti-share me-2"></i>Share
                                                        </a></li>
                                                        @if($file->supportsPreview())
                                                        <li><a class="dropdown-item" href="{{ route('files.preview', $file) }}">
                                                            <i class="ti ti-eye me-2"></i>Preview
                                                        </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="{{ route('files.version.history', $file) }}">
                                                            <i class="ti ti-history me-2"></i>Version History
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('files.collaboration.show', $file) }}">
                                                            <i class="ti ti-users me-2"></i>Collaboration
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#workflowModal" data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}">
                                                            <i class="ti ti-git-branch me-2"></i>Start Workflow
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#securityModal" data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}">
                                                            <i class="ti ti-shield-lock me-2"></i>Security
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#commentsModal" data-resource-type="file" data-resource-id="{{ $file->id }}" data-resource-name="{{ $file->name }}">
                                                            <i class="ti ti-message-circle me-2"></i>Comments
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile({{ $file->id }})">
                                                            <i class="ti ti-trash me-2"></i>Delete
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                
                                                @if($file->is_image)
                                                    <img src="{{ $file->preview_url }}" alt="{{ $file->name }}" class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                                @else
                                                    <i class="{{ $file->icon }} text-primary display-1 mb-3"></i>
                                                @endif
                                                
                                                <h6 class="card-title text-truncate" title="{{ $file->name }}">{{ $file->name }}</h6>
                                                @if($file->description)
                                                    <p class="text-muted small mb-1" style="font-size: 0.75rem; line-height: 1.2; max-height: 2.4rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $file->description }}</p>
                                                @endif
                                                <small class="text-muted d-block">{{ $file->human_size }}</small>
                                                <small class="text-muted">{{ $file->updated_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-message-circle me-2"></i>
                    Comments & Collaboration
                </h3>
            </div>
            <div class="card-body">
                <div id="commentsContainer">
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-message-circle-off fs-1"></i>
                        <p class="mt-2">No comments yet. Start a discussion!</p>
                    </div>
                </div>
                
                <!-- Add Comment Form -->
                <div class="mt-4">
                    <form id="commentForm" onsubmit="event.preventDefault(); addComment();">
                        <input type="hidden" id="commentResourceType" name="resource_type">
                        <input type="hidden" id="commentResourceId" name="resource_id">
                        <div class="row">
                            <div class="col-md-8">
                                <textarea class="form-control" id="commentContent" name="content" rows="3" placeholder="Add a comment..." maxlength="2000"></textarea>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column gap-2">
                                    <select class="form-select" id="commentType" name="type">
                                        <option value="comment">Comment</option>
                                        <option value="annotation">Annotation</option>
                                        <option value="suggestion">Suggestion</option>
                                    </select>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isPrivateComment" name="is_private">
                                        <label class="form-check-label" for="isPrivateComment">
                                            Private Comment
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="addComment()">
                                        <i class="ti ti-send me-1"></i>
                                        Add Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden File Input -->
<input type="file" id="fileInput" multiple style="display: none;" accept="*/*">

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFolderForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" id="folderName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="folderDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="folderDescription" name="description" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="parent_folder_id" value="{{ $currentFolder->id ?? '' }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Files Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-upload me-2"></i>
                    Upload Files
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="uploadFiles" class="form-label">Select Files</label>
                        <input type="file" class="form-control" id="uploadFiles" name="files[]" multiple required>
                        <div class="form-text">You can select multiple files. Maximum file size: 50MB per file.</div>
                    </div>
                    <div class="mb-3">
                        <label for="uploadDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="uploadDescription" name="description" rows="3" placeholder="Add a description for the uploaded files..."></textarea>
                        <div class="form-text">This description will be applied to all uploaded files.</div>
                    </div>
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
                    
                    <!-- File Preview -->
                    <div id="filePreview" class="mt-3" style="display: none;">
                        <h6 class="text-uppercase text-muted mb-2">Selected Files</h6>
                        <div id="fileList" class="list-group"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                        <i class="ti ti-upload me-1"></i>
                        Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-edit me-2"></i>
                    Edit File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFileForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editFileName" class="form-label">File Name</label>
                        <input type="text" class="form-control" id="editFileName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editFileDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="editFileDescription" name="description" rows="3" placeholder="Add or update the file description..."></textarea>
                    </div>
                    <input type="hidden" id="editFileId" name="file_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Folder Modal -->
<div class="modal fade" id="editFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-edit me-2"></i>
                    Edit Folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFolderForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editFolderName" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" id="editFolderName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editFolderDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="editFolderDescription" name="description" rows="3" placeholder="Add or update the folder description..."></textarea>
                    </div>
                    <input type="hidden" id="editFolderId" name="folder_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permission Management Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-lock me-2"></i>
                    Manage Permissions: <span id="permissionResourceName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Current Permissions -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted mb-3">Current Permissions</h6>
                    <div id="currentPermissions" class="mb-3">
                        <div class="text-center text-muted py-3">
                            <i class="ti ti-users-off display-1 mb-2"></i>
                            <p>No permissions assigned yet</p>
                        </div>
                    </div>
                </div>

                <!-- Add Permission Form -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Add New Permission</h6>
                    </div>
                    <div class="card-body">
                        <form id="addPermissionForm">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Assign To</label>
                                    <select class="form-select" id="assignableType" name="assignable_type" required>
                                        <option value="">Select type...</option>
                                        <option value="user">User</option>
                                        <option value="position">Position</option>
                                        @if(Auth::user()->type !== 'individual')
                                            @switch(Auth::user()->type)
                                                @case('family')
                                                    <option value="role">Role</option>
                                                    @break
                                                @case('government')
                                                    <option value="agency">Agency</option>
                                                    @break
                                                @case('social_group')
                                                    <option value="group">Group</option>
                                                    @break
                                                @case('professional_group')
                                                    <option value="division">Division</option>
                                                    @break
                                                @case('educational_institution')
                                                    <option value="department">Department</option>
                                                    @break
                                                @case('non_profit')
                                                    <option value="department">Department</option>
                                                    @break
                                                @case('organisation')
                                                    <option value="department">Department</option>
                                                    @break
                                                @default
                                                    <option value="department">Department</option>
                                            @endswitch
                                        @else
                                            {{-- Individual users can assign to categories if they have any --}}
                                            <option value="category">Category</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select Entity</label>
                                    <select class="form-select" id="assignableId" name="assignable_id" required disabled>
                                        <option value="">Select entity...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Permission Level</label>
                                    <select class="form-select" id="permissionPreset" name="preset">
                                        <option value="">Custom permissions...</option>
                                        <option value="view_only">View Only</option>
                                        <option value="read_download">Read & Download</option>
                                        <option value="contributor">Contributor</option>
                                        <option value="editor">Editor</option>
                                        <option value="full_access">Full Access</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Permissions -->
                            <div id="customPermissions" class="mb-3">
                                <label class="form-label">Custom Permissions</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permView" name="permissions[view]">
                                            <label class="form-check-label" for="permView">
                                                <i class="ti ti-eye me-1"></i>View
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permDownload" name="permissions[download]">
                                            <label class="form-check-label" for="permDownload">
                                                <i class="ti ti-download me-1"></i>Download
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permEdit" name="permissions[edit]">
                                            <label class="form-check-label" for="permEdit">
                                                <i class="ti ti-edit me-1"></i>Edit
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permUpload" name="permissions[upload]">
                                            <label class="form-check-label" for="permUpload">
                                                <i class="ti ti-upload me-1"></i>Upload
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permDelete" name="permissions[delete]">
                                            <label class="form-check-label" for="permDelete">
                                                <i class="ti ti-trash me-1"></i>Delete
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permReshare" name="permissions[reshare]">
                                            <label class="form-check-label" for="permReshare">
                                                <i class="ti ti-share me-1"></i>Reshare
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="permManage" name="permissions[manage]">
                                            <label class="form-check-label" for="permManage">
                                                <i class="ti ti-settings me-1"></i>Manage
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="permissionNotes" name="notes" rows="2" placeholder="Add any notes about this permission assignment..."></textarea>
                            </div>

                            <input type="hidden" id="resourceType" name="resource_type">
                            <input type="hidden" id="resourceId" name="resource_id">
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addPermission()">Add Permission</button>
            </div>
        </div>
    </div>
</div>

<!-- Shares Management Modal -->
<div class="modal fade" id="sharesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-share me-2"></i>
                    My Shares
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="sharesContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading your shares...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-share me-2"></i>
                    Share: <span id="shareResourceName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shareForm">
                    <!-- Share Type -->
                    <div class="mb-3">
                        <label class="form-label">Share Type</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="share_type" id="shareTypeLink" value="link" checked>
                                    <label class="form-check-label" for="shareTypeLink">
                                        <i class="ti ti-link me-1"></i>Public Link
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="share_type" id="shareTypeEmail" value="email">
                                    <label class="form-check-label" for="shareTypeEmail">
                                        <i class="ti ti-mail me-1"></i>Email
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="share_type" id="shareTypeWhatsapp" value="whatsapp">
                                    <label class="form-check-label" for="shareTypeWhatsapp">
                                        <i class="ti ti-brand-whatsapp me-1"></i>WhatsApp
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Information (for email/whatsapp) -->
                    <div id="recipientInfo" class="mb-3" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" name="recipient_name" placeholder="Enter recipient name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Recipient Email</label>
                                <input type="email" class="form-control" name="recipient_email" placeholder="Enter recipient email">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Recipient Phone</label>
                                <input type="text" class="form-control" name="recipient_phone" placeholder="Enter recipient phone">
                            </div>
                        </div>
                    </div>

                    <!-- Access Controls -->
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted mb-3">Access Controls</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="passwordProtected" name="password_protected">
                                    <label class="form-check-label" for="passwordProtected">
                                        Password Protected
                                    </label>
                                </div>
                                <div id="passwordField" class="mt-2" style="display: none;">
                                    <input type="password" class="form-control" name="password" placeholder="Enter password" minlength="4">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expires At</label>
                                <input type="datetime-local" class="form-control" name="expires_at">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Max Downloads</label>
                                <input type="number" class="form-control" name="max_downloads" placeholder="Leave empty for unlimited" min="1">
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted mb-3">Permissions</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermView" name="permissions[view]" checked>
                                    <label class="form-check-label" for="sharePermView">
                                        <i class="ti ti-eye me-1"></i>View
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermDownload" name="permissions[download]" checked>
                                    <label class="form-check-label" for="sharePermDownload">
                                        <i class="ti ti-download me-1"></i>Download
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermEdit" name="permissions[edit]">
                                    <label class="form-check-label" for="sharePermEdit">
                                        <i class="ti ti-edit me-1"></i>Edit
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermUpload" name="permissions[upload]">
                                    <label class="form-check-label" for="sharePermUpload">
                                        <i class="ti ti-upload me-1"></i>Upload
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermDelete" name="permissions[delete]">
                                    <label class="form-check-label" for="sharePermDelete">
                                        <i class="ti ti-trash me-1"></i>Delete
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sharePermReshare" name="permissions[reshare]">
                                    <label class="form-check-label" for="sharePermReshare">
                                        <i class="ti ti-share me-1"></i>Reshare
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="shareResourceType" name="resource_type">
                    <input type="hidden" id="shareResourceId" name="resource_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createShare()">Create Share</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Processing...</h5>
        </div>
    </div>
</div>

                    <!-- / Content -->
                    
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

    <!-- Bootstrap JS only (jQuery already loaded in head) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Menu JS for sidebar functionality -->
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input handler
    const fileInput = document.getElementById('fileInput');
    const dropzone = document.getElementById('dropzone');
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            uploadFiles(e.target.files);
        }
    });

    // Drag and drop functionality
    let dragCounter = 0;

    document.addEventListener('dragenter', function(e) {
        e.preventDefault();
        dragCounter++;
        dropzone.classList.remove('d-none');
    });

    document.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dragCounter--;
        if (dragCounter === 0) {
            dropzone.classList.add('d-none');
        }
    });

    document.addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    document.addEventListener('drop', function(e) {
        e.preventDefault();
        dragCounter = 0;
        dropzone.classList.add('d-none');
        
        if (e.dataTransfer.files.length > 0) {
            uploadFiles(e.dataTransfer.files);
        }
    });

    // Create folder form
    document.getElementById('createFolderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createFolder();
    });

    // Upload form
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        uploadFilesFromModal();
    });

    // Edit file form
    document.getElementById('editFileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateFile();
    });

    // Edit folder form
    document.getElementById('editFolderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateFolder();
    });

    // File preview in upload modal
    document.getElementById('uploadFiles').addEventListener('change', function(e) {
        showFilePreview(e.target.files);
    });

    // Search functionality
    document.getElementById('searchButton').addEventListener('click', function() {
        performSearch();
    });

    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
});

function uploadFiles(files) {
    const formData = new FormData();
    const currentFolderId = '{{ $currentFolder->id ?? "" }}';
    
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    if (currentFolderId) {
        formData.append('folder_id', currentFolderId);
    }
    
    showLoading();
    
    fetch('{{ route("files.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', data.message);
            // Clear service worker cache and force refresh
            clearServiceWorkerCache();
            window.location.href = window.location.href + '?t=' + Date.now();
        } else {
            showAlert('error', data.error || 'Upload failed');
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(error => {
                    showAlert('warning', error);
                });
            }
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Upload failed: ' + error.message);
    });
}

function uploadFilesFromModal() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('uploadSubmitBtn');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ti ti-loader ti-spin me-1"></i>Uploading...';
    
    showLoading();
    
    fetch('{{ route("files.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', data.message);
            // Close modal, clear cache, and force refresh
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            modal.hide();
            clearServiceWorkerCache();
            window.location.href = window.location.href + '?t=' + Date.now();
        } else {
            showAlert('error', data.error || 'Upload failed');
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(error => {
                    showAlert('warning', error);
                });
            }
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Upload failed: ' + error.message);
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ti ti-upload me-1"></i>Upload Files';
    });
}

function showFilePreview(files) {
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    
    if (files.length === 0) {
        filePreview.style.display = 'none';
        return;
    }
    
    fileList.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        
        const fileItem = document.createElement('div');
        fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
        fileItem.innerHTML = `
            <div>
                <i class="ti ti-file me-2"></i>
                <strong>${file.name}</strong>
                <small class="text-muted d-block">${fileSize} MB</small>
            </div>
        `;
        
        fileList.appendChild(fileItem);
    }
    
    filePreview.style.display = 'block';
}

function createFolder() {
    const form = document.getElementById('createFolderForm');
    const formData = new FormData(form);
    
    fetch('{{ route("files.folders.create") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Clear service worker cache and force refresh
            clearServiceWorkerCache();
            window.location.href = window.location.href + '?t=' + Date.now();
        } else {
            showAlert('error', data.error);
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to create folder');
    });
}

function renameFile(fileId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        fetch(`/files/${fileId}/rename`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: newName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.error);
            }
        });
    }
}

function renameFolder(folderId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        fetch(`/files/folders/${folderId}/rename`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: newName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.error);
            }
        });
    }
}

function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        fetch(`/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.error);
            }
        });
    }
}

function deleteFolder(folderId) {
    if (confirm('Are you sure you want to delete this folder? This action cannot be undone.')) {
        fetch(`/files/folders/${folderId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.error);
            }
        });
    }
}

function performSearch() {
    const query = document.getElementById('searchInput').value.trim();
    if (query.length === 0) {
        return;
    }
    
    fetch(`{{ route('files.search') }}?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data);
    })
    .catch(error => {
        showAlert('error', 'Search failed');
    });
}

function displaySearchResults(results) {
    // This would update the UI with search results
    // For now, we'll just show an alert with the count
    const totalResults = results.files.length + results.folders.length;
    showAlert('info', `Found ${totalResults} results`);
}

function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('d-none');
}

function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Permission Management Functions
let currentPermissionData = {};

function managePermissions(resourceType, resourceId, resourceName) {
    currentPermissionData = {
        type: resourceType,
        id: resourceId,
        name: resourceName
    };
    
    document.getElementById('permissionResourceName').textContent = resourceName;
    document.getElementById('resourceType').value = resourceType;
    document.getElementById('resourceId').value = resourceId;
    
    // Load current permissions
    loadPermissions(resourceType, resourceId);
    
    // Show modal
    new bootstrap.Modal(document.getElementById('permissionModal')).show();
}

function loadPermissions(resourceType, resourceId) {
    showLoading();
    
    fetch(`{{ route('files.permissions.get') }}?resource_type=${resourceType}&resource_id=${resourceId}`)
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.error) {
            showAlert('error', data.error);
            return;
        }
        
        displayCurrentPermissions(data.permissions);
        populateAssignableEntities(data.assignable_entities);
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Failed to load permissions');
    });
}

function displayCurrentPermissions(permissions) {
    const container = document.getElementById('currentPermissions');
    
    if (permissions.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="ti ti-users-off display-1 mb-2"></i>
                <p>No permissions assigned yet</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    permissions.forEach(permission => {
        const assignableName = permission.assignable.name || permission.assignable.type_name || 'Unknown';
        const assignableType = permission.assignable_type.split('\\').pop();
        const permissionLevel = permission.permission_level || 'Custom';
        
        html += `
            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                <div>
                    <h6 class="mb-1">
                        <i class="ti ti-${getAssignableIcon(assignableType)} me-2"></i>
                        ${assignableName}
                    </h6>
                    <small class="text-muted">${assignableType} • ${permissionLevel}</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-label-primary">${getPermissionSummary(permission.permissions)}</span>
                    <button class="btn btn-sm btn-outline-danger" onclick="removePermission('${permission.assignable_type}', ${permission.assignable_id})">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function getAssignableIcon(type) {
    switch(type.toLowerCase()) {
        case 'user': return 'user';
        case 'position': return 'briefcase';
        case 'department': return 'building';
        default: return 'users';
    }
}

function getPermissionSummary(permissions) {
    // Handle case where permissions might be a JSON string
    let permObj = permissions;
    if (typeof permissions === 'string') {
        try {
            permObj = JSON.parse(permissions);
        } catch (e) {
            console.error('Failed to parse permissions:', e);
            return '0 permissions';
        }
    }
    
    const activePerms = Object.keys(permObj).filter(key => permObj[key]);
    return activePerms.length + ' permissions';
}

function populateAssignableEntities(entities) {
    currentPermissionData.entities = entities;
    
    // Remove existing event listeners to prevent conflicts
    const assignableTypeSelect = document.getElementById('assignableType');
    if (assignableTypeSelect) {
        // Clone the element to remove all event listeners
        const newElement = assignableTypeSelect.cloneNode(true);
        assignableTypeSelect.parentNode.replaceChild(newElement, assignableTypeSelect);
        
        // Add fresh event listener
        document.getElementById('assignableType').addEventListener('change', function() {
            const type = this.value;
            const entitySelect = document.getElementById('assignableId');
            
            entitySelect.innerHTML = '<option value="">Select entity...</option>';
            entitySelect.disabled = !type;
            
            if (type) {
                // Map the dropdown value to the correct entity array key
                let entityKey;
                if (type === 'user') {
                    entityKey = 'users';
                } else if (type === 'position') {
                    entityKey = 'positions';
                } else {
                    // For departments/roles/agencies/etc., find the correct key
                    entityKey = Object.keys(entities).find(key => 
                        key !== 'users' && key !== 'positions'
                    );
                }
                
                if (entityKey && entities[entityKey]) {
                    entities[entityKey].forEach(entity => {
                        const name = entity.name || entity.type_name || `${entity.first_name} ${entity.last_name}`;
                        entitySelect.innerHTML += `<option value="${entity.id}">${name}</option>`;
                    });
                    entitySelect.disabled = false;
                }
            }
        });
    }
}

function addPermission() {
    const form = document.getElementById('addPermissionForm');
    const formData = new FormData(form);
    
    // Convert assignable_type to correct model class
    const assignableType = formData.get('assignable_type');
    const assignableTypeMapping = {
        'user': 'App\\Models\\User',
        'position': 'App\\Models\\Position',
        'department': 'App\\Models\\Department',
        'role': 'App\\Models\\Department',           // Family
        'agency': 'App\\Models\\Department',         // Government
        'group': 'App\\Models\\Department',          // Social Group
        'division': 'App\\Models\\Department',       // Professional Group
        'category': 'App\\Models\\Department'        // Individual (though rarely used)
    };
    
    // If using preset, don't send custom permissions
    const preset = formData.get('preset');
    let requestData;
    
    if (preset) {
        requestData = {
            resource_type: formData.get('resource_type'),
            resource_id: formData.get('resource_id'),
            assignable_type: assignableTypeMapping[assignableType] || assignableType,
            assignable_id: formData.get('assignable_id'),
            preset: preset
        };
        
        // Use preset endpoint
        fetch('{{ route("files.permissions.preset") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(handlePermissionResponse);
    } else {
        // Custom permissions
        const permissions = {};
        ['view', 'download', 'edit', 'upload', 'delete', 'reshare', 'manage'].forEach(perm => {
            permissions[perm] = document.getElementById('perm' + perm.charAt(0).toUpperCase() + perm.slice(1)).checked;
        });
        
        requestData = {
            resource_type: formData.get('resource_type'),
            resource_id: formData.get('resource_id'),
            assignable_type: assignableTypeMapping[assignableType] || assignableType,
            assignable_id: formData.get('assignable_id'),
            permissions: permissions,
            notes: formData.get('notes')
        };
        
        fetch('{{ route("files.permissions.assign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(handlePermissionResponse);
    }
}

function handlePermissionResponse(data) {
    if (data.success) {
        showAlert('success', data.message);
        // Reload permissions
        loadPermissions(currentPermissionData.type, currentPermissionData.id);
        // Reset form
        document.getElementById('addPermissionForm').reset();
        document.getElementById('assignableId').disabled = true;
    } else {
        showAlert('error', data.error || 'Failed to assign permission');
    }
}

function removePermission(assignableType, assignableId) {
    if (!confirm('Are you sure you want to remove this permission?')) {
        return;
    }
    
    // Convert full model class name to simple type name
    let simpleType;
    if (assignableType.includes('User')) {
        simpleType = 'user';
    } else if (assignableType.includes('Position')) {
        simpleType = 'position';
    } else if (assignableType.includes('Department')) {
        simpleType = 'department';
    } else {
        simpleType = assignableType.split('\\').pop().toLowerCase();
    }
    
    const requestData = {
        resource_type: currentPermissionData.type,
        resource_id: currentPermissionData.id,
        assignable_type: simpleType,
        assignable_id: assignableId
    };
    
    fetch('{{ route("files.permissions.remove") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadPermissions(currentPermissionData.type, currentPermissionData.id);
        } else {
            showAlert('error', data.error || 'Failed to remove permission');
        }
    });
}

// File Manager specific dropdown handler (isolated namespace)
$(document).ready(function() {
    console.log('File manager dropdowns initializing...');
         console.log('Found file cards:', $('.file-card').length);
     console.log('Found folder cards:', $('.folder-card').length);
     console.log('Found dropdown buttons:', $('.file-card .dropdown button, .folder-card .dropdown button').length);
     
     // Manual sidebar menu toggle functionality
     $(document).on('click', '.menu-toggle', function(e) {
         e.preventDefault();
         e.stopPropagation();
         console.log('Menu toggle clicked');
         
         const $this = $(this);
         const $menuItem = $this.closest('.menu-item');
         const $submenu = $menuItem.find('> .menu-sub'); // Only direct child submenu
         
         console.log('Submenu found:', $submenu.length);
         console.log('Menu item text:', $this.text().trim());
         
         if ($submenu.length > 0) {
             // Toggle the submenu
             if ($submenu.is(':visible')) {
                 $submenu.slideUp(300);
                 $menuItem.removeClass('open');
                 console.log('Closing submenu');
             } else {
                 // Only close sibling submenus at the same level
                 $menuItem.siblings('.menu-item').find('> .menu-sub').slideUp(300);
                 $menuItem.siblings('.menu-item').removeClass('open');
                 
                 // Open this submenu
                 $submenu.slideDown(300);
                 $menuItem.addClass('open');
                 console.log('Opening submenu');
             }
         }
     });
    
    // Use a more specific selector to avoid conflicts with navbar dropdowns
    $(document).on('click', '.file-card .dropdown [data-bs-toggle="dropdown"], .folder-card .dropdown [data-bs-toggle="dropdown"]', function(e) {
        console.log('File dropdown clicked');
        e.preventDefault();
        e.stopPropagation();
        
        // Close all file/folder dropdowns specifically
        $('.file-card .dropdown-menu, .folder-card .dropdown-menu').removeClass('show');
        
        // Toggle this specific dropdown
        const menu = $(this).siblings('.dropdown-menu');
        menu.addClass('show');
        
        console.log('Dropdown should be visible now');
    });
    
    // Close file/folder dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.file-card .dropdown, .folder-card .dropdown').length) {
            $('.file-card .dropdown-menu, .folder-card .dropdown-menu').removeClass('show');
        }
    });
    
    // Prevent file/folder dropdown from closing when clicking inside
    $(document).on('click', '.file-card .dropdown-menu, .folder-card .dropdown-menu', function(e) {
        e.stopPropagation();
    });
    
    // Permission preset handler
    $(document).on('change', '#permissionPreset', function() {
        const preset = $(this).val();
        const customPermissions = $('#customPermissions');
        
        if (preset) {
            customPermissions.hide();
        } else {
            customPermissions.show();
        }
    });
    
         // Simple click handler for all dropdown buttons
     $(document).on('click', '.folder-card button[data-bs-toggle="dropdown"], .file-card button[data-bs-toggle="dropdown"]', function(e) {
         console.log('Simple click handler triggered');
         e.preventDefault();
         e.stopPropagation();
         
         // Hide all other dropdowns
         $('.dropdown-menu').removeClass('show');
         
         // Show this dropdown
         const dropdown = $(this).next('.dropdown-menu');
         dropdown.addClass('show');
         console.log('Dropdown should now be visible');
     });
     
     // Alternative: Direct click handlers (backup method)
     $('.file-card .dropdown button, .folder-card .dropdown button').each(function() {
         $(this).off('click.filemanager').on('click.filemanager', function(e) {
             console.log('Direct button click');
             e.preventDefault();
             e.stopPropagation();
             
             // Close all dropdowns
             $('.file-card .dropdown-menu, .folder-card .dropdown-menu').removeClass('show');
             
             // Find and show this dropdown menu
             const menu = $(this).siblings('.dropdown-menu');
             console.log('Found menu:', menu.length);
             console.log('Menu HTML:', menu.html());
             
             if (menu.length > 0) {
                 menu.addClass('show');
                 console.log('Added show class, menu should be visible');
                 console.log('Menu display style:', menu.css('display'));
                 console.log('Menu has show class:', menu.hasClass('show'));
             } else {
                 console.log('No menu found - checking parent structure');
                 console.log('Button parent:', $(this).parent().html());
             }
         });
     });
});

// Share functionality
function shareResource(type, id, name) {
    document.getElementById('shareResourceType').value = type;
    document.getElementById('shareResourceId').value = id;
    document.getElementById('shareResourceName').textContent = name;
    
    // Reset form
    document.getElementById('shareForm').reset();
    document.getElementById('recipientInfo').style.display = 'none';
    document.getElementById('passwordField').style.display = 'none';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('shareModal')).show();
}

// Handle share type changes
document.addEventListener('DOMContentLoaded', function() {
    const shareTypeInputs = document.querySelectorAll('input[name="share_type"]');
    const recipientInfo = document.getElementById('recipientInfo');
    
    shareTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'link') {
                recipientInfo.style.display = 'none';
            } else {
                recipientInfo.style.display = 'block';
            }
        });
    });
    
    // Handle password protection toggle
    const passwordProtected = document.getElementById('passwordProtected');
    const passwordField = document.getElementById('passwordField');
    
    passwordProtected.addEventListener('change', function() {
        if (this.checked) {
            passwordField.style.display = 'block';
        } else {
            passwordField.style.display = 'none';
        }
    });
});

function showMyShares() {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('sharesModal'));
    modal.show();
    
    // Load shares
    loadMyShares();
}

function loadMyShares() {
    fetch('{{ route("shares.my") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayShares(data.shares);
        } else {
            showAlert('error', 'Failed to load shares');
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to load shares: ' + error.message);
    });
}

function displayShares(shares) {
    const container = document.getElementById('sharesContent');
    
    if (shares.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="ti ti-share-off display-1 text-muted mb-3"></i>
                <h5>No shares yet</h5>
                <p class="text-muted">You haven't created any shares yet. Use the "Share" button on files or folders to get started.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover">';
    html += `
        <thead>
            <tr>
                <th>Resource</th>
                <th>Type</th>
                <th>Recipient</th>
                <th>Status</th>
                <th>Views</th>
                <th>Downloads</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    shares.forEach(share => {
        const statusClass = share.is_valid ? 'success' : 'danger';
        const statusText = share.is_valid ? 'Active' : 'Inactive';
        
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="ti ti-${share.resource_type === 'file' ? 'file' : 'folder'} me-2"></i>
                        <div>
                            <strong>${share.resource_name}</strong>
                            ${share.password_protected ? '<i class="ti ti-lock text-warning ms-1"></i>' : ''}
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-label-${share.share_type === 'link' ? 'primary' : share.share_type === 'email' ? 'info' : 'success'}">
                        ${share.share_type.charAt(0).toUpperCase() + share.share_type.slice(1)}
                    </span>
                </td>
                <td>
                    ${share.recipient_name || share.recipient_email || share.recipient_phone || 'Public Link'}
                </td>
                <td>
                    <span class="badge bg-label-${statusClass}">${statusText}</span>
                </td>
                <td>${share.view_count}</td>
                <td>${share.download_count}</td>
                <td>${new Date(share.created_at).toLocaleDateString()}</td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="${share.share_url}" target="_blank">
                                <i class="ti ti-external-link me-2"></i>View
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="copyShareUrl('${share.share_url}')">
                                <i class="ti ti-copy me-2"></i>Copy URL
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="revokeShare(${share.id})">
                                <i class="ti ti-trash me-2"></i>Revoke
                            </a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function copyShareUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showAlert('success', 'Share URL copied to clipboard!');
    }).catch(() => {
        showAlert('error', 'Failed to copy URL');
    });
}

function revokeShare(shareId) {
    if (!confirm('Are you sure you want to revoke this share? This action cannot be undone.')) {
        return;
    }
    
    fetch(`{{ url('shares/revoke') }}/${shareId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Share revoked successfully');
            loadMyShares(); // Reload the list
        } else {
            showAlert('error', data.error || 'Failed to revoke share');
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to revoke share: ' + error.message);
    });
}

function createShare() {
    const form = document.getElementById('shareForm');
    const formData = new FormData(form);
    
    // Convert form data to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (key.includes('[')) {
            // Handle nested permissions
            const [parent, child] = key.replace(']', '').split('[');
            if (!data[parent]) data[parent] = {};
            data[parent][child] = value === 'on' || value === 'true';
        } else {
            data[key] = value;
        }
    });
    
    // Handle checkboxes
    data.password_protected = document.getElementById('passwordProtected').checked;
    data.permissions = {
        view: document.getElementById('sharePermView').checked,
        download: document.getElementById('sharePermDownload').checked,
        edit: document.getElementById('sharePermEdit').checked,
        upload: document.getElementById('sharePermUpload').checked,
        delete: document.getElementById('sharePermDelete').checked,
        reshare: document.getElementById('sharePermReshare').checked,
        manage: false // External shares should never have manage permission
    };
    
    // Show loading
    showLoading();
    
    fetch('{{ route("shares.create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', data.message);
            
            // If it's a link share, show the share URL
            if (data.share.share_type === 'link') {
                const shareUrl = data.share.share_url;
                showAlert('info', `Share URL: ${shareUrl}`);
                
                // Copy to clipboard
                navigator.clipboard.writeText(shareUrl).then(() => {
                    showAlert('success', 'Share URL copied to clipboard!');
                });
            }
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
        } else {
            showAlert('error', data.error || 'Failed to create share');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Failed to create share: ' + error.message);
    });
}

// Tag and Favorites functionality
function showTagModal(fileId) {
    currentFileId = fileId;
    document.getElementById('tagFileId').value = fileId;
    document.getElementById('tagName').value = '';
    document.getElementById('tagSuggestions').innerHTML = '';
    
    const modal = new bootstrap.Modal(document.getElementById('tagModal'));
    modal.show();
}

function addTag() {
    const formData = new FormData(document.getElementById('tagForm'));
    
    fetch('{{ route("search.tags.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            file_id: formData.get('file_id'),
            tag_name: formData.get('tag_name'),
            color: formData.get('color')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.error || 'Failed to add tag');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to add tag');
    });
}

function toggleFavorite(fileId, folderId) {
    fetch('{{ route("search.favorites.toggle") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            file_id: fileId,
            folder_id: folderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.error || 'Failed to toggle favorite');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to toggle favorite');
    });
}

// Tag suggestions
document.addEventListener('DOMContentLoaded', function() {
    const tagNameInput = document.getElementById('tagName');
    if (tagNameInput) {
        tagNameInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length < 2) {
                document.getElementById('tagSuggestions').innerHTML = '';
                return;
            }
            
            fetch(`{{ route('search.tag-suggestions') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(suggestions => {
                    const container = document.getElementById('tagSuggestions');
                    container.innerHTML = '';
                    
                    suggestions.forEach(suggestion => {
                        const div = document.createElement('div');
                        div.className = 'badge me-1 mb-1';
                        div.style.backgroundColor = suggestion.color;
                        div.style.color = 'white';
                        div.style.cursor = 'pointer';
                        div.textContent = suggestion.tag_name;
                        div.onclick = () => {
                            document.getElementById('tagName').value = suggestion.tag_name;
                            document.getElementById('tagColor').value = suggestion.color;
                            container.innerHTML = '';
                        };
                        container.appendChild(div);
                    });
                });
        });
    }
});

function editFile(fileId, currentName, currentDescription) {
    document.getElementById('editFileId').value = fileId;
    document.getElementById('editFileName').value = currentName;
    document.getElementById('editFileDescription').value = currentDescription;
    
    const modal = new bootstrap.Modal(document.getElementById('editFileModal'));
    modal.show();
}

function editFolder(folderId, currentName, currentDescription) {
    document.getElementById('editFolderId').value = folderId;
    document.getElementById('editFolderName').value = currentName;
    document.getElementById('editFolderDescription').value = currentDescription;
    
    const modal = new bootstrap.Modal(document.getElementById('editFolderModal'));
    modal.show();
}

function updateFile() {
    const form = document.getElementById('editFileForm');
    const formData = new FormData(form);
    const fileId = formData.get('file_id');
    
    const requestData = {
        name: formData.get('name'),
        description: formData.get('description')
    };
    
    fetch(`/files/${fileId}/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('editFileModal'));
            modal.hide();
            location.reload();
        } else {
            showAlert('error', data.error || 'Failed to update file');
        }
    })
    .catch(error => {
        showAlert('error', 'Update failed: ' + error.message);
    });
}

function updateFolder() {
    const form = document.getElementById('editFolderForm');
    const formData = new FormData(form);
    const folderId = formData.get('folder_id');
    
    const requestData = {
        name: formData.get('name'),
        description: formData.get('description')
    };
    
    fetch(`/files/folders/${folderId}/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('editFolderModal'));
            modal.hide();
            location.reload();
        } else {
            showAlert('error', data.error || 'Failed to update folder');
        }
    })
    .catch(error => {
        showAlert('error', 'Update failed: ' + error.message);
    });
}

// Comment functionality
function showCommentsModal(resourceType, resourceId, resourceName) {
    const resourceTypeEl = document.getElementById('commentResourceType');
    const resourceIdEl = document.getElementById('commentResourceId');
    
    if (resourceTypeEl && resourceIdEl) {
        resourceTypeEl.value = resourceType;
        resourceIdEl.value = resourceId;
    }
    
    // Update modal title
    const modalTitle = document.querySelector('#commentsModal .modal-title');
    modalTitle.innerHTML = `<i class="ti ti-message-circle me-2"></i>Comments: ${resourceName}`;
    
    // Load comments
    loadComments(resourceType, resourceId);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('commentsModal'));
    modal.show();
}

function loadComments(resourceType, resourceId) {
    const params = new URLSearchParams({
        resource_type: resourceType,
        resource_id: resourceId
    });
    
    fetch(`/comments?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
            } else {
                showAlert('error', data.error || 'Failed to load comments');
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            showAlert('error', 'Failed to load comments: ' + error.message);
        });
}

function displayComments(comments) {
    const container = document.getElementById('commentsContainer');
    
    if (!comments || comments.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="ti ti-message-circle-off fs-1"></i>
                <p class="mt-2">No comments yet. Start a discussion!</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        html += createCommentHTML(comment);
    });
    
    container.innerHTML = html;
}

function createCommentHTML(comment) {
    const isPrivate = comment.is_private ? '<span class="badge bg-warning ms-2">Private</span>' : '';
    const isResolved = comment.status === 'resolved' ? '<span class="badge bg-success ms-2">Resolved</span>' : '';
    const typeBadge = `<span class="badge bg-info">${comment.type_display || comment.type || 'Comment'}</span>`;
    
    // Handle missing author data defensively
    const authorName = comment.author?.full_name || comment.author?.name || 'Unknown User';
    const authorInitial = comment.author?.first_name?.charAt(0) || comment.author?.name?.charAt(0) || 'U';
    
    return `
        <div class="comment-item mb-3 p-3 border rounded" data-comment-id="${comment.id}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-primary">${authorInitial}</span>
                    </div>
                    <div>
                        <strong>${authorName}</strong>
                        <small class="text-muted ms-2">${new Date(comment.created_at).toLocaleString()}</small>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    ${typeBadge}
                    ${isPrivate}
                    ${isResolved}
                </div>
            </div>
            <div class="comment-content">
                <p class="mb-2">${comment.content}</p>
            </div>
            <div class="comment-actions d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="replyToComment(${comment.id})">
                    <i class="ti ti-reply me-1"></i>Reply
                </button>
                ${comment.status === 'active' ? `
                    <button class="btn btn-sm btn-outline-success" onclick="resolveComment(${comment.id})">
                        <i class="ti ti-check me-1"></i>Resolve
                    </button>
                ` : `
                    <button class="btn btn-sm btn-outline-warning" onclick="reopenComment(${comment.id})">
                        <i class="ti ti-refresh me-1"></i>Reopen
                    </button>
                `}
                ${comment.author_id === {{ auth()->id() }} || {{ auth()->user()->is_admin ? 'true' : 'false' }} ? `
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteComment(${comment.id})">
                        <i class="ti ti-trash me-1"></i>Delete
                    </button>
                ` : ''}
            </div>
            ${comment.replies && comment.replies.length > 0 ? `
                <div class="replies mt-3 ms-4">
                    ${comment.replies.map(reply => createCommentHTML(reply)).join('')}
                </div>
            ` : ''}
        </div>
    `;
}

// Form submission is now handled directly in the form onsubmit attribute

function addComment() {
    // Get form values directly from elements
    const resourceTypeEl = document.getElementById('commentResourceType');
    const resourceIdEl = document.getElementById('commentResourceId');
    const contentEl = document.getElementById('commentContent');
    const typeEl = document.getElementById('commentType');
    const isPrivateEl = document.getElementById('isPrivateComment');
    
    const data = {
        resource_type: resourceTypeEl ? resourceTypeEl.value : '',
        resource_id: resourceIdEl ? parseInt(resourceIdEl.value) : 0,
        content: contentEl ? contentEl.value : '',
        type: typeEl ? typeEl.value : 'comment',
        is_private: isPrivateEl ? isPrivateEl.checked : false
    };
    
    if (!data.content.trim()) {
        showAlert('error', 'Please enter a comment');
        return;
    }
    
    if (!data.resource_type || !data.resource_id) {
        showAlert('error', 'Missing resource information. Please try refreshing the page.');
        return;
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showAlert('error', 'CSRF token not found. Please refresh the page.');
        return;
    }
    
    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Reload comments
            loadComments(document.getElementById('commentResourceType').value, document.getElementById('commentResourceId').value);
            // Clear form
            document.getElementById('commentContent').value = '';
            document.getElementById('isPrivateComment').checked = false;
        } else {
            showAlert('error', data.error || 'Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error adding comment:', error);
        showAlert('error', 'Failed to add comment: ' + error.message);
    });
}

function resolveComment(commentId) {
    const notes = prompt('Add resolution notes (optional):');
    
    fetch(`/comments/${commentId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ resolution_notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadComments(document.getElementById('commentResourceType').value, document.getElementById('commentResourceId').value);
        } else {
            showAlert('error', data.error || 'Failed to resolve comment');
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to resolve comment: ' + error.message);
    });
}

function reopenComment(commentId) {
    fetch(`/comments/${commentId}/reopen`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadComments(document.getElementById('commentResourceType').value, document.getElementById('commentResourceId').value);
        } else {
            showAlert('error', data.error || 'Failed to reopen comment');
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to reopen comment: ' + error.message);
    });
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    fetch(`/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadComments(document.getElementById('commentResourceType').value, document.getElementById('commentResourceId').value);
        } else {
            showAlert('error', data.error || 'Failed to delete comment');
        }
    })
    .catch(error => {
        showAlert('error', 'Failed to delete comment: ' + error.message);
    });
}

function replyToComment(commentId) {
    // For now, we'll just focus on the comment form
    // In a full implementation, you'd add a reply form or modify the main form
    document.getElementById('commentContent').focus();
    showAlert('info', 'Reply functionality will be implemented in the next version');
}

function testComment() {
    console.log('Test function called');
    
    // Check form elements
    const resourceType = document.getElementById('commentResourceType');
    const resourceId = document.getElementById('commentResourceId');
    const content = document.getElementById('commentContent');
    
    console.log('Resource Type Element:', resourceType);
    console.log('Resource ID Element:', resourceId);
    console.log('Content Element:', content);
    
    console.log('Resource Type Value:', resourceType ? resourceType.value : 'NOT FOUND');
    console.log('Resource ID Value:', resourceId ? resourceId.value : 'NOT FOUND');
    console.log('Content Value:', content ? content.value : 'NOT FOUND');
    
    // Check if elements exist in DOM
    console.log('All form elements in modal:');
    const modal = document.getElementById('commentsModal');
    if (modal) {
        const allInputs = modal.querySelectorAll('input, textarea, select');
        allInputs.forEach((input, index) => {
            console.log(`Input ${index}:`, input.id, input.name, input.value);
        });
    } else {
        console.log('Modal not found!');
    }
    
    // Test the addComment function
    addComment();
}

// Ensure modal is properly initialized when opened
document.addEventListener('DOMContentLoaded', function() {
    const commentsModal = document.getElementById('commentsModal');
    if (commentsModal) {
        commentsModal.addEventListener('show.bs.modal', function(event) {
            // Check if we have the resource info from the button that was clicked
            const button = event.relatedTarget;
            if (button && button.getAttribute('data-resource-type') && button.getAttribute('data-resource-id')) {
                const resourceType = button.getAttribute('data-resource-type');
                const resourceId = button.getAttribute('data-resource-id');
                const resourceName = button.getAttribute('data-resource-name');
                
                const resourceTypeEl = document.getElementById('commentResourceType');
                const resourceIdEl = document.getElementById('commentResourceId');
                
                if (resourceTypeEl && resourceIdEl) {
                    resourceTypeEl.value = resourceType;
                    resourceIdEl.value = resourceId;
                    
                    // Update modal title
                    const modalTitle = document.querySelector('#commentsModal .modal-title');
                    modalTitle.innerHTML = `<i class="ti ti-message-circle me-2"></i>Comments: ${resourceName}`;
                    
                    // Load comments
                    loadComments(resourceType, resourceId);
                }
            }
        });
    }
});

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('SW registered: ', registration);
                
                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New version available
                            showAlert('info', 'New version available! Refresh to update.');
                        }
                    });
                });
            })
            .catch((registrationError) => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// PWA Install Prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt triggered');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show custom install button
    showPWAInstallPrompt();
});

function showPWAInstallPrompt() {
    // Create install prompt
    const installPrompt = document.createElement('div');
    installPrompt.id = 'pwa-install-prompt';
    installPrompt.className = 'position-fixed bottom-0 start-0 m-3 p-3 bg-primary text-white rounded shadow-lg';
    installPrompt.style.zIndex = '999999';
    installPrompt.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ti ti-download me-2"></i>
            <div class="me-3">
                <div class="fw-semibold">Install GoalDocs</div>
                <small>Add to your home screen for quick access</small>
            </div>
            <button class="btn btn-sm btn-light me-2" onclick="installPWA()">Install</button>
            <button class="btn btn-sm btn-outline-light" onclick="dismissPWAPrompt()">×</button>
        </div>
    `;
    
    document.body.appendChild(installPrompt);
    
    // Auto-hide after 10 seconds
    setTimeout(() => {
        dismissPWAPrompt();
    }, 10000);
}

function installPWA() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                showAlert('success', 'GoalDocs installed successfully!');
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
            dismissPWAPrompt();
        });
    }
}

function dismissPWAPrompt() {
    const prompt = document.getElementById('pwa-install-prompt');
    if (prompt) {
        prompt.remove();
    }
}

// Handle PWA display mode
if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
    console.log('Running as PWA');
    document.body.classList.add('pwa-mode');
    
    // Hide browser-specific elements when running as PWA
    const browserElements = document.querySelectorAll('.browser-only');
    browserElements.forEach(el => el.style.display = 'none');
}

// Mobile-specific enhancements
if (/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    document.body.classList.add('mobile-device');
    
    // Prevent zoom on input focus for iOS
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        if (input.style.fontSize < '16px') {
            input.style.fontSize = '16px';
        }
    });
    
    // Add touch-friendly classes
    document.body.classList.add('touch-device');
    
    // Touch gesture support for file cards
    initTouchGestures();
}

// Touch gesture initialization
function initTouchGestures() {
    let startX, startY, isSelecting = false;
    let selectedCards = new Set();
    
    // Add touch event listeners to file/folder cards
    document.addEventListener('touchstart', handleTouchStart, { passive: false });
    document.addEventListener('touchmove', handleTouchMove, { passive: false });
    document.addEventListener('touchend', handleTouchEnd, { passive: false });
    
    // Long press for selection
    let longPressTimer;
    let longPressTriggered = false;
    
    function handleTouchStart(e) {
        const card = e.target.closest('.file-card, .folder-card');
        if (!card) return;
        
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        longPressTriggered = false;
        
        // Start long press timer
        longPressTimer = setTimeout(() => {
            if (!longPressTriggered) {
                longPressTriggered = true;
                toggleCardSelection(card);
                navigator.vibrate && navigator.vibrate(50); // Haptic feedback
            }
        }, 500);
    }
    
    function handleTouchMove(e) {
        const deltaX = Math.abs(e.touches[0].clientX - startX);
        const deltaY = Math.abs(e.touches[0].clientY - startY);
        
        // Cancel long press if moved too much
        if (deltaX > 10 || deltaY > 10) {
            clearTimeout(longPressTimer);
        }
    }
    
    function handleTouchEnd(e) {
        clearTimeout(longPressTimer);
        
        if (!longPressTriggered) {
            // Regular tap - clear selection if no cards selected
            if (selectedCards.size === 0) {
                clearCardSelection();
            }
        }
    }
    
    function toggleCardSelection(card) {
        const cardId = card.dataset.fileId || card.dataset.folderId;
        
        if (selectedCards.has(cardId)) {
            selectedCards.delete(cardId);
            card.classList.remove('selected');
        } else {
            selectedCards.add(cardId);
            card.classList.add('selected');
        }
        
        updateSelectionToolbar();
    }
    
    function clearCardSelection() {
        selectedCards.clear();
        document.querySelectorAll('.file-card.selected, .folder-card.selected')
            .forEach(card => card.classList.remove('selected'));
        updateSelectionToolbar();
    }
    
    function updateSelectionToolbar() {
        let toolbar = document.getElementById('selection-toolbar');
        
        if (selectedCards.size > 0) {
            if (!toolbar) {
                toolbar = createSelectionToolbar();
                document.body.appendChild(toolbar);
            }
            toolbar.querySelector('.selection-count').textContent = selectedCards.size;
            toolbar.style.display = 'flex';
        } else if (toolbar) {
            toolbar.style.display = 'none';
        }
    }
    
    function createSelectionToolbar() {
        const toolbar = document.createElement('div');
        toolbar.id = 'selection-toolbar';
        toolbar.className = 'position-fixed bottom-0 start-0 end-0 bg-primary text-white p-3 d-flex align-items-center justify-content-between';
        toolbar.style.zIndex = '9999';
        toolbar.innerHTML = `
            <div class="d-flex align-items-center">
                <span class="selection-count me-2">0</span>
                <span>items selected</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-light" onclick="shareSelected()">
                    <i class="ti ti-share"></i>
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="moveSelected()">
                    <i class="ti ti-folder-symlink"></i>
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="deleteSelected()">
                    <i class="ti ti-trash"></i>
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="clearCardSelection()">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
        return toolbar;
    }
    
    // Expose functions globally
    window.clearCardSelection = clearCardSelection;
    window.shareSelected = function() {
        showAlert('info', `Sharing ${selectedCards.size} items...`);
        // Implementation for bulk sharing
    };
    
    window.moveSelected = function() {
        showAlert('info', `Moving ${selectedCards.size} items...`);
        // Implementation for bulk moving
    };
    
    window.deleteSelected = function() {
        if (confirm(`Delete ${selectedCards.size} selected items?`)) {
            showAlert('info', `Deleting ${selectedCards.size} items...`);
            // Implementation for bulk deletion
        }
    };
}

// Swipe to refresh functionality
let isRefreshing = false;
let startY = 0;
let currentY = 0;
let pullDistance = 0;

document.addEventListener('touchstart', (e) => {
    if (window.scrollY === 0) {
        startY = e.touches[0].clientY;
    }
}, { passive: true });

document.addEventListener('touchmove', (e) => {
    if (window.scrollY === 0 && !isRefreshing) {
        currentY = e.touches[0].clientY;
        pullDistance = currentY - startY;
        
        if (pullDistance > 100) {
            showPullToRefreshIndicator();
        }
    }
}, { passive: true });

document.addEventListener('touchend', () => {
    if (pullDistance > 150 && !isRefreshing) {
        triggerRefresh();
    }
    hidePullToRefreshIndicator();
    pullDistance = 0;
}, { passive: true });

function showPullToRefreshIndicator() {
    let indicator = document.getElementById('pull-refresh-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'pull-refresh-indicator';
        indicator.className = 'position-fixed top-0 start-0 end-0 text-center p-2 bg-primary text-white';
        indicator.style.transform = 'translateY(-100%)';
        indicator.style.transition = 'transform 0.3s ease';
        indicator.innerHTML = '<i class="ti ti-refresh me-2"></i>Pull to refresh';
        document.body.appendChild(indicator);
    }
    indicator.style.transform = 'translateY(0)';
}

function hidePullToRefreshIndicator() {
    const indicator = document.getElementById('pull-refresh-indicator');
    if (indicator) {
        indicator.style.transform = 'translateY(-100%)';
        setTimeout(() => indicator.remove(), 300);
    }
}

function triggerRefresh() {
    isRefreshing = true;
    showAlert('info', 'Refreshing...');
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>

<!-- Core JS -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<script>
/**
 * Open file preview modal
 */
function openFilePreview(fileId, fileName, fileUrl, mimeType, fileSize) {
    // Use the global function from preview-modal.blade.php
    if (typeof openDocumentPreview === 'function') {
        openDocumentPreview(fileId, fileName, fileUrl, mimeType, fileSize);
    } else {
        // Fallback to regular preview URL if modal is not available
        window.open(fileUrl, '_blank');
    }
}

/**
 * Check if file type is previewable
 */
function isFilePreviewable(mimeType) {
    const previewableTypes = [
        'application/pdf',
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'text/plain', 'text/html', 'text/css', 'text/javascript', 'application/javascript',
        'application/json', 'application/xml', 'text/xml', 'text/csv', 'text/markdown'
    ];
    
    return previewableTypes.includes(mimeType) || mimeType.startsWith('text/');
}
</script>

<style>
.folder-card:hover, .file-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Ensure cards don't interfere with dropdowns */
.card {
    position: relative;
    z-index: 1;
}

.card .dropdown {
    z-index: 999999;
}

.card .dropdown-menu {
    z-index: 999999 !important;
}

.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
}

#dropzone {
    transition: all 0.3s ease;
}

.card-title {
    font-size: 0.875rem;
}

.display-1 {
    font-size: 3rem !important;
}

/* Dropdown fixes */
.dropdown {
    position: relative;
    z-index: 999999; /* Ensure dropdown container has high z-index */
}

.dropdown-toggle {
    z-index: 999999;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 999999 !important; /* Increased z-index with !important */
    display: none !important;
    min-width: 160px;
    padding: 8px 0;
    margin: 2px 0 0;
    font-size: 14px;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #ffffff;
    background-clip: padding-box;
    border: 2px solid #333;
    border-radius: 6px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    transform: translateY(0);
}

.dropdown-menu.show {
    display: block !important;
    animation: dropdownSlide 0.3s ease-out;
    z-index: 999999 !important; /* Ensure shown dropdown has highest priority */
}

@keyframes dropdownSlide {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.375rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover {
    color: #1e2125;
    background-color: #e9ecef;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid rgba(0, 0, 0, 0.15);
}

/* Ensure cards and comments don't interfere with dropdowns */
.card {
    position: relative;
    z-index: 1;
}

.card:hover {
    z-index: 2;
}

#commentsContainer {
    z-index: 10; /* Lower than dropdown */
}

/* Mobile Optimizations */
@media (max-width: 768px) {
    /* Better touch targets */
    .btn-sm {
        min-height: 44px;
        min-width: 44px;
        padding: 8px 12px;
    }
    
    .dropdown-toggle {
        min-height: 44px;
        min-width: 44px;
    }
    
    /* Improved card spacing on mobile */
    .folder-card, .file-card {
        margin-bottom: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    /* Touch selection states */
    .folder-card.selected, .file-card.selected {
        transform: scale(0.95);
        box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.3);
        background-color: rgba(115, 103, 240, 0.1);
    }
    
    /* Touch feedback */
    .folder-card:active, .file-card:active {
        transform: scale(0.98);
    }
    
    /* Better card titles on mobile */
    .card-title {
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    /* Optimized icon sizes */
    .display-1 {
        font-size: 2.5rem !important;
    }
    
    /* Better modal sizing */
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-lg {
        max-width: calc(100% - 2rem);
    }
    
    /* Mobile-friendly form controls */
    .form-control, .form-select {
        font-size: 16px; /* Prevents zoom on iOS */
        min-height: 44px;
    }
    
    /* Better comment form on mobile */
    .row .col-md-8, .row .col-md-4 {
        margin-bottom: 1rem;
    }
    
    /* Improved navigation */
    .layout-menu {
        width: 280px;
    }
    
    /* Better spacing for mobile cards */
    .card-body {
        padding: 1rem 0.5rem;
    }
    
    /* Responsive text */
    .text-muted.small {
        font-size: 0.7rem;
    }
    
    /* Touch-friendly dropdowns */
    .dropdown-menu {
        min-width: 200px;
        font-size: 16px;
    }
    
    .dropdown-item {
        padding: 0.75rem 1rem;
        min-height: 44px;
        display: flex;
        align-items: center;
    }
    
    /* Mobile upload zone */
    #dropzone {
        min-height: 120px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    #dropzone.dragover {
        border-color: #7367f0;
        background-color: rgba(115, 103, 240, 0.05);
    }
    
    /* Selection toolbar styles */
    #selection-toolbar {
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    /* Pull to refresh indicator */
    #pull-refresh-indicator {
        z-index: 9999;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
}

@media (max-width: 576px) {
    /* Extra small screens */
    .card-body {
        padding: 0.75rem 0.25rem;
    }
    
    .btn {
        font-size: 0.875rem;
    }
    
    /* Stack comment form vertically */
    .row .col-md-8, .row .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Smaller margins */
    .mb-3 {
        margin-bottom: 0.5rem !important;
    }
    
    /* Better file icons on very small screens */
    .display-1 {
        font-size: 2rem !important;
    }
    
    /* Responsive grid - 2 columns only on very small screens */
    .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    /* Mobile-specific button sizes */
    .btn-sm {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
    
    /* Better navigation on small screens */
    .layout-menu {
        width: 260px;
    }
}

/* PWA specific styles */
.pwa-mode {
    /* Hide elements that don't make sense in PWA mode */
}

.touch-device .card {
    /* Enhanced touch feedback */
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .app-brand-logo svg {
        width: 40px;
        height: 28px;
    }
    
    .ti {
        /* Sharper icons on retina displays */
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
}
</style>
<!-- Tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tagForm">
                    <input type="hidden" id="tagFileId" name="file_id">
                    <div class="mb-3">
                        <label for="tagName" class="form-label">Tag Name</label>
                        <input type="text" class="form-control" id="tagName" name="tag_name" required>
                        <div id="tagSuggestions" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="tagColor" class="form-label">Tag Color</label>
                        <input type="color" class="form-control form-control-color" id="tagColor" name="color" value="#667eea">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addTag()">Add Tag</button>
            </div>
        </div>
    </div>
</div>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-message-circle me-2"></i>
                    Comments & Collaboration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="commentsContainer">
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-message-circle-off fs-1"></i>
                        <p class="mt-2">No comments yet. Start a discussion!</p>
                    </div>
                </div>
                
                <!-- Add Comment Form -->
                <div class="mt-4">
                    <form id="commentForm" onsubmit="event.preventDefault(); addComment();">
                        <input type="hidden" id="commentResourceType" name="resource_type">
                        <input type="hidden" id="commentResourceId" name="resource_id">
                        <div class="row">
                            <div class="col-md-8">
                                <textarea class="form-control" id="commentContent" name="content" rows="3" placeholder="Add a comment..." maxlength="2000"></textarea>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column gap-2">
                                    <select class="form-select" id="commentType" name="type">
                                        <option value="comment">Comment</option>
                                        <option value="annotation">Annotation</option>
                                        <option value="suggestion">Suggestion</option>
                                    </select>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isPrivateComment" name="is_private">
                                        <label class="form-check-label" for="isPrivateComment">
                                            Private Comment
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="addComment()">
                                        <i class="ti ti-send me-1"></i>
                                        Add Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Document Preview Modal -->
@include('files.preview-modal')

</body>
</html> 