<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>File Manager - GoalDocs</title>
    <meta name="description" content="Manage your files and folders" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

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
                        <button type="button" class="btn btn-success" onclick="document.getElementById('fileInput').click()">
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
                                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                                        <div class="card folder-card h-100" data-folder-id="{{ $folder->id }}">
                                            <div class="card-body text-center p-3">
                                                <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                                    <button class="btn btn-sm btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="renameFolder({{ $folder->id }}, '{{ $folder->name }}')">
                                                            <i class="ti ti-edit me-2"></i>Rename
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="managePermissions('folder', {{ $folder->id }}, '{{ $folder->name }}')">
                                                            <i class="ti ti-lock me-2"></i>Permissions
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="shareResource('folder', {{ $folder->id }}, '{{ $folder->name }}')">
                                                            <i class="ti ti-share me-2"></i>Share
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
                                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                                        <div class="card file-card h-100" data-file-id="{{ $file->id }}">
                                            <div class="card-body text-center p-3">
                                                <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                                    <button class="btn btn-sm btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ $file->download_url }}">
                                                            <i class="ti ti-download me-2"></i>Download
                                                        </a></li>
                                                        @if($file->is_image || $file->is_document)
                                                            <li><a class="dropdown-item" href="{{ $file->preview_url }}" target="_blank">
                                                                <i class="ti ti-eye me-2"></i>Preview
                                                            </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFavorite({{ $file->id }}, null)">
                                                            <i class="ti ti-star me-2"></i>Favorite
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="showTagModal({{ $file->id }})">
                                                            <i class="ti ti-tag me-2"></i>Add Tag
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="renameFile({{ $file->id }}, '{{ $file->name }}')">
                                                            <i class="ti ti-edit me-2"></i>Rename
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
            location.reload();
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
            location.reload();
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

<style>
.folder-card:hover, .file-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
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
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 99999;
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

</body>
</html> 