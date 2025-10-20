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
    <title>Search Files - GoalDocs</title>
    <meta name="description" content="Search and manage your files and folders" />
    
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
        console.log('Search Files page loaded at:', new Date().toISOString());
        console.log('Folders count:', {{ $folders->count() }});
        console.log('Files count:', {{ $files->count() }});
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
    <!-- Search Files Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="ti ti-search me-2"></i>
                            Search Files
                        </h5>
                        <small class="text-muted">
                            @if(auth()->user()->type === 'individual')
                                Search your personal files and folders
                            @else
                                Search your {{ strtolower(auth()->user()->type) }} files and folders
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="clearSearch()">
                            <i class="ti ti-refresh me-1"></i>
                            Clear Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchForm" method="GET" action="{{ route('search.files') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" class="form-control" name="q" value="{{ $query }}" placeholder="Search files and folders...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="image" {{ $filters['type'] ?? '' === 'image' ? 'selected' : '' }}>Images</option>
                                    <option value="video" {{ $filters['type'] ?? '' === 'video' ? 'selected' : '' }}>Videos</option>
                                    <option value="audio" {{ $filters['type'] ?? '' === 'audio' ? 'selected' : '' }}>Audio</option>
                                    <option value="application/pdf" {{ $filters['type'] ?? '' === 'application/pdf' ? 'selected' : '' }}>PDFs</option>
                                    <option value="text" {{ $filters['type'] ?? '' === 'text' ? 'selected' : '' }}>Documents</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-search me-1"></i>
                                    Search
                                </button>
                            </div>
                        </div>
                        
                        <!-- Advanced Filters -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}" placeholder="From Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}" placeholder="To Date">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="size_min" value="{{ $filters['size_min'] ?? '' }}" placeholder="Min Size (MB)">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="size_max" value="{{ $filters['size_max'] ?? '' }}" placeholder="Max Size (MB)">
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <input type="text" class="form-control" name="tags" value="{{ $filters['tags'] ?? '' }}" placeholder="Tags (comma separated)">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        @if($query || !empty(array_filter($filters)))
                            Search Results ({{ $folders->count() + $files->count() }})
                        @else
                            All Files and Folders ({{ $folders->count() + $files->count() }})
                        @endif
                    </h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleView('grid')">
                            <i class="ti ti-layout-grid"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleView('list')">
                            <i class="ti ti-list"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($folders->isEmpty() && $files->isEmpty())
                        <div class="text-center py-5">
                            @if($query || !empty(array_filter($filters)))
                                <i class="ti ti-search-off display-1 text-muted mb-3"></i>
                                <h4 class="text-muted">No results found</h4>
                                <p class="text-muted">Try adjusting your search criteria or filters.</p>
                            @else
                                <i class="ti ti-folder-off display-1 text-muted mb-3"></i>
                                <h4 class="text-muted">No files or folders</h4>
                                <p class="text-muted">Upload files or create folders to get started.</p>
                            @endif
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
                                                        <li><a class="dropdown-item" href="{{ route('files.index', ['folder' => $folder->id]) }}">
                                                            <i class="ti ti-folder-open me-2"></i>Open
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFavorite(null, {{ $folder->id }})">
                                                            <i class="ti ti-star me-2"></i>Favorite
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <div class="folder-icon mb-2">
                                                    <i class="ti ti-folder display-4 text-warning"></i>
                                                </div>
                                                <h6 class="folder-name mb-1 text-truncate" title="{{ $folder->name }}">
                                                    {{ Str::limit($folder->name, 20) }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $folder->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Files -->
                        @if($files->isNotEmpty())
                            <h6 class="text-uppercase text-muted mb-3">
                                <i class="ti ti-file me-1"></i>
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
                                                        <li><a class="dropdown-item" href="{{ route('files.preview', $file->id) }}">
                                                            <i class="ti ti-eye me-2"></i>Preview
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('files.download', $file->id) }}">
                                                            <i class="ti ti-download me-2"></i>Download
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFavorite({{ $file->id }}, null)">
                                                            <i class="ti ti-star me-2"></i>Favorite
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="showTagModal({{ $file->id }})">
                                                            <i class="ti ti-tag me-2"></i>Add Tag
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <div class="file-icon mb-2">
                                                    <i class="ti {{ $file->icon }} display-4 text-primary"></i>
                                                </div>
                                                <h6 class="file-name mb-1 text-truncate" title="{{ $file->name }}">
                                                    {{ Str::limit($file->name, 20) }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $file->human_size }} • {{ $file->created_at->diffForHumans() }}
                                                </small>
                                                
                                                @if($file->tags->count() > 0)
                                                <div class="mt-2">
                                                    @foreach($file->tags->take(2) as $tag)
                                                    <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                        {{ $tag->tag_name }}
                                                    </span>
                                                    @endforeach
                                                    @if($file->tags->count() > 2)
                                                    <small class="text-muted">+{{ $file->tags->count() - 2 }}</small>
                                                    @endif
                                                </div>
                                                @endif
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

    <!-- Recent Activities & Favorites -->
    <div class="row mt-4">
        <!-- Recent Activities -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-clock me-2"></i>
                        Recent Activities
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($recentActivities as $activity)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ti ti-circle fs-6 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $activity->user->name }}</strong>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">
                                {{ $activity->activity_type_label }}
                                @if($activity->file)
                                <strong>{{ $activity->file->name }}</strong>
                                @elseif($activity->folder)
                                <strong>{{ $activity->folder->name }}</strong>
                                @endif
                            </small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No recent activities</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Favorites -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-star me-2"></i>
                        Your Favorites
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($favorites as $favorite)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ti {{ $favorite->resource_type === 'file' ? $favorite->file->icon : 'ti-folder' }} me-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ Str::limit($favorite->resource->name, 25) }}</strong>
                                <small class="text-muted">{{ $favorite->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">{{ $favorite->resource_type }}</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No favorites yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

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

                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}?v={{ time() }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>

    <!-- Search Files Specific Scripts -->
    <script>
        let searchFilesCurrentFileId = null;

        function clearSearch() {
            window.location.href = '{{ route("search.files") }}';
        }

        function showTagModal(fileId) {
            searchFilesCurrentFileId = fileId;
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
                    alert(data.error || 'Failed to add tag');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add tag');
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
                    location.reload();
                } else {
                    alert(data.error || 'Failed to toggle favorite');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to toggle favorite');
            });
        }

        // Tag suggestions
        document.getElementById('tagName').addEventListener('input', function() {
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

        function toggleView(view) {
            // Implementation for switching between grid and list view
            console.log('Switching to', view, 'view');
        }
    </script>

    <!-- Include Document Preview Modal -->
    @include('files.preview-modal')
</body>
</html>