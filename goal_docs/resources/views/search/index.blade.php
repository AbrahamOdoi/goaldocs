@extends('layouts.app')

@section('title', 'Search & Organize')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Search Sidebar -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-search me-2"></i>
                        Search & Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form id="searchForm" method="GET" action="{{ route('search.index') }}">
                        <!-- Search Query -->
                        <div class="mb-3">
                            <label for="searchQuery" class="form-label">Search Query</label>
                            <input type="text" class="form-control" id="searchQuery" name="q" 
                                   value="{{ $query }}" placeholder="Search files and folders...">
                        </div>

                        <!-- File Type Filter -->
                        <div class="mb-3">
                            <label for="fileType" class="form-label">File Type</label>
                            <select class="form-select" id="fileType" name="type">
                                <option value="">All Types</option>
                                <option value="image" {{ $filters['type'] ?? '' === 'image' ? 'selected' : '' }}>Images</option>
                                <option value="video" {{ $filters['type'] ?? '' === 'video' ? 'selected' : '' }}>Videos</option>
                                <option value="audio" {{ $filters['type'] ?? '' === 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="application/pdf" {{ $filters['type'] ?? '' === 'application/pdf' ? 'selected' : '' }}>PDFs</option>
                                <option value="text" {{ $filters['type'] ?? '' === 'text' ? 'selected' : '' }}>Documents</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_from" 
                                           value="{{ $filters['date_from'] ?? '' }}" placeholder="From">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_to" 
                                           value="{{ $filters['date_to'] ?? '' }}" placeholder="To">
                                </div>
                            </div>
                        </div>

                        <!-- File Size Range -->
                        <div class="mb-3">
                            <label class="form-label">File Size (MB)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="size_min" 
                                           value="{{ $filters['size_min'] ?? '' }}" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="size_max" 
                                           value="{{ $filters['size_max'] ?? '' }}" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <!-- Tags Filter -->
                        <div class="mb-3">
                            <label for="tagsFilter" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tagsFilter" name="tags" 
                                   value="{{ $filters['tags'] ?? '' }}" placeholder="Enter tags (comma separated)">
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-2"></i>
                            Search
                        </button>

                        <!-- Clear Filters -->
                        @if($query || !empty(array_filter($filters)))
                        <a href="{{ route('search.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="ti ti-x me-2"></i>
                            Clear Filters
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Popular Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-tags me-2"></i>
                        Popular Tags
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($popularTags as $tag)
                        <span class="badge" style="background-color: {{ $tag->color }}; color: white; cursor: pointer;"
                              onclick="addTagToFilter('{{ $tag->tag_name }}')">
                            {{ $tag->tag_name }}
                            <small>({{ $tag->usage_count }})</small>
                        </span>
                        @empty
                        <p class="text-muted small mb-0">No tags yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar me-2"></i>
                        Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Results:</span>
                        <strong>{{ $results->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Files:</span>
                        <strong>{{ $results->where('resource_type', 'file')->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Folders:</span>
                        <strong>{{ $results->where('resource_type', 'folder')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Search Results -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-list me-2"></i>
                        Search Results
                        @if($query || !empty(array_filter($filters)))
                        <span class="badge bg-primary ms-2">{{ $results->count() }}</span>
                        @endif
                    </h5>
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
                    @if($query || !empty(array_filter($filters)))
                        @if($results->count() > 0)
                        <div id="searchResults" class="row g-3">
                            @foreach($results as $item)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ti {{ $item->resource_type === 'file' ? $item->icon : 'ti-folder' }} me-2 fs-4"></i>
                                                <div>
                                                    <h6 class="card-title mb-0">{{ Str::limit($item->name, 30) }}</h6>
                                                    <small class="text-muted">{{ $item->resource_type }}</small>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($item->resource_type === 'file')
                                                    <li><a class="dropdown-item" href="{{ route('files.preview', $item->id) }}">
                                                        <i class="ti ti-eye me-2"></i>Preview
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('files.download', $item->id) }}">
                                                        <i class="ti ti-download me-2"></i>Download
                                                    </a></li>
                                                    @else
                                                    <li><a class="dropdown-item" href="{{ route('files.index', ['folder' => $item->id]) }}">
                                                        <i class="ti ti-folder-open me-2"></i>Open
                                                    </a></li>
                                                    @endif
                                                    <li><a class="dropdown-item" href="#" onclick="toggleFavorite({{ $item->resource_type === 'file' ? $item->id : 'null' }}, {{ $item->resource_type === 'folder' ? $item->id : 'null' }})">
                                                        <i class="ti ti-star me-2"></i>Favorite
                                                    </a></li>
                                                    @if($item->resource_type === 'file')
                                                    <li><a class="dropdown-item" href="#" onclick="showTagModal({{ $item->id }})">
                                                        <i class="ti ti-tag me-2"></i>Add Tag
                                                    </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        @if($item->resource_type === 'file')
                                        <div class="mb-2">
                                            <small class="text-muted">{{ $item->human_size }}</small>
                                        </div>
                                        @endif

                                        @if($item->resource_type === 'file' && $item->tags->count() > 0)
                                        <div class="mb-2">
                                            @foreach($item->tags->take(3) as $tag)
                                            <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                {{ $tag->tag_name }}
                                            </span>
                                            @endforeach
                                            @if($item->tags->count() > 3)
                                            <small class="text-muted">+{{ $item->tags->count() - 3 }} more</small>
                                            @endif
                                        </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                            <small class="text-muted">{{ $item->resource_type === 'file' ? $item->uploader->name : $item->creator->name }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="ti ti-search-off fs-1 text-muted mb-3"></i>
                            <h5>No results found</h5>
                            <p class="text-muted">Try adjusting your search criteria or filters.</p>
                        </div>
                        @endif
                    @else
                    <div class="text-center py-5">
                        <i class="ti ti-search fs-1 text-muted mb-3"></i>
                        <h5>Start searching</h5>
                        <p class="text-muted">Enter a search query or use filters to find files and folders.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activities & Favorites -->
            <div class="row">
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

@endsection

@push('scripts')
<script>
let currentFileId = null;

function addTagToFilter(tagName) {
    const currentTags = document.getElementById('tagsFilter').value;
    const tags = currentTags ? currentTags.split(',').map(t => t.trim()) : [];
    
    if (!tags.includes(tagName)) {
        tags.push(tagName);
        document.getElementById('tagsFilter').value = tags.join(', ');
    }
}

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
@endpush 