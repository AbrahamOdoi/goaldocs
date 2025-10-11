@extends('layouts.app')

@section('title', 'Advanced Search')

@push('styles')
<style>
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .search-suggestion {
            cursor: pointer;
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .search-suggestion:hover {
            background-color: #f8f9fa;
        }
        
        .search-suggestion:last-child {
            border-bottom: none;
        }
        
        .filter-tag {
            display: inline-block;
            background: #7367f0;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 2px;
        }
        
        .filter-tag .remove {
            margin-left: 4px;
            cursor: pointer;
        }
        
        .search-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .result-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .relevance-score {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .content-preview {
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .ai-suggestion {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border-left: 4px solid #ff6b6b;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 8px 8px 0;
        }
        
        .search-filters {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-section {
            margin-bottom: 1rem;
        }
        
        .filter-section:last-child {
            margin-bottom: 0;
        }
        
        .filter-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .search-input-wrapper {
            position: relative;
        }
        
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .search-loading {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #6c757d;
        }
        
        .search-loading.show {
            display: block;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #7367f0;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .search-filters {
                margin-bottom: 0.5rem;
                padding: 0.75rem;
            }
            
            .filter-section {
                margin-bottom: 0.75rem;
            }
            
            .search-stats {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .result-card {
                margin-bottom: 0.75rem;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            <i class="ti ti-search me-2"></i>
                                            Advanced Search
                                        </h4>
                                        <p class="card-subtitle text-muted">
                                            Search across file names, descriptions, and document contents with AI-powered features
                                        </p>
                                    </div>
                                    <div class="card-body">
                                        <!-- Search Form -->
                                        <form id="advancedSearchForm" method="GET" action="{{ route('files.advanced-search') }}">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <div class="search-input-wrapper">
                                                        <input type="text" 
                                                               class="form-control form-control-lg" 
                                                               id="searchQuery" 
                                                               name="q" 
                                                               value="{{ $query }}"
                                                               placeholder="Search files, folders, and document contents..."
                                                               autocomplete="off">
                                                        <div class="search-suggestions" id="searchSuggestions" style="display: none;"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                                        <i class="ti ti-search me-2"></i>
                                                        Search
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Advanced Filters -->
                                            <div class="search-filters mt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <div class="filter-section">
                                                            <label class="filter-label">File Type</label>
                                                            <select class="form-select" name="type">
                                                                <option value="">All Types</option>
                                                                <option value="images" {{ request('type') == 'images' ? 'selected' : '' }}>Images</option>
                                                                <option value="documents" {{ request('type') == 'documents' ? 'selected' : '' }}>Documents</option>
                                                                <option value="videos" {{ request('type') == 'videos' ? 'selected' : '' }}>Videos</option>
                                                                <option value="audio" {{ request('type') == 'audio' ? 'selected' : '' }}>Audio</option>
                                                                <option value="archives" {{ request('type') == 'archives' ? 'selected' : '' }}>Archives</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <div class="filter-section">
                                                            <label class="filter-label">Date Range</label>
                                                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <div class="filter-section">
                                                            <label class="filter-label">&nbsp;</label>
                                                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <div class="filter-section">
                                                            <label class="filter-label">Size Range (MB)</label>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <input type="number" class="form-control" name="size_min" value="{{ request('size_min') }}" placeholder="Min">
                                                                </div>
                                                                <div class="col-6">
                                                                    <input type="number" class="form-control" name="size_max" value="{{ request('size_max') }}" placeholder="Max">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-6">
                                                        <div class="filter-section">
                                                            <label class="filter-label">Search Options</label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="search_in_content" id="searchInContent" value="1" {{ request('search_in_content') ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="searchInContent">
                                                                    Search within document contents (slower but more thorough)
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="filter-section">
                                                            <label class="filter-label">Tags</label>
                                                            <select class="form-select" name="tags" id="tagsSelect">
                                                                <option value="">All Tags</option>
                                                                @foreach($popularTags as $tag)
                                                                    <option value="{{ $tag->id }}" {{ request('tags') == $tag->id ? 'selected' : '' }}>
                                                                        {{ $tag->tag_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <!-- Search Results -->
                                        @if($query || !empty(array_filter($filters)))
                                            <div class="search-stats mt-4">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="mb-1">{{ $results->count() }}</h5>
                                                            <small>Total Results</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="mb-1">{{ $searchStats['total_files'] ?? 0 }}</h5>
                                                            <small>Files Found</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="mb-1">{{ $searchStats['total_folders'] ?? 0 }}</h5>
                                                            <small>Folders Found</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="mb-1">{{ count($suggestions) }}</h5>
                                                            <small>Suggestions</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- AI Suggestions -->
                                            @if(count($suggestions) > 0)
                                                <div class="ai-suggestion">
                                                    <h6 class="mb-2">
                                                        <i class="ti ti-brain me-2"></i>
                                                        AI Suggestions
                                                    </h6>
                                                    <p class="mb-2">Try these related searches:</p>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($suggestions->take(5) as $suggestion)
                                                            <a href="{{ route('files.advanced-search', ['q' => $suggestion]) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                {{ $suggestion }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Results List -->
                                            <div class="search-loading" id="searchLoading">
                                                <div class="spinner"></div>
                                                Searching...
                                            </div>
                                            
                                            <div id="searchResults">
                                                @if($results->count() > 0)
                                                    @foreach($results as $result)
                                                        <div class="result-card card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <i class="ti {{ $result->resource_type == 'file' ? $result->icon : 'ti-folder' }} me-2 text-primary"></i>
                                                                            <h6 class="card-title mb-0">
                                                                                <a href="{{ $result->resource_type == 'file' ? route('files.download', $result->id) : route('files.index', ['folder' => $result->id]) }}" 
                                                                                   class="text-decoration-none">
                                                                                    {{ $result->name }}
                                                                                </a>
                                                                            </h6>
                                                                            @if(isset($result->search_relevance_score))
                                                                                <span class="relevance-score ms-2">
                                                                                    {{ number_format($result->search_relevance_score, 1) }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                        
                                                                        @if($result->description)
                                                                            <p class="text-muted mb-2">{{ $result->description }}</p>
                                                                        @endif
                                                                        
                                                                        @if($result->resource_type == 'file' && $result->extracted_text)
                                                                            <div class="content-preview">
                                                                                {{ Str::limit($result->extracted_text, 150) }}
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        <div class="d-flex align-items-center mt-2">
                                                                            <small class="text-muted me-3">
                                                                                <i class="ti ti-calendar me-1"></i>
                                                                                {{ $result->created_at->format('M j, Y') }}
                                                                            </small>
                                                                            @if($result->resource_type == 'file')
                                                                                <small class="text-muted me-3">
                                                                                    <i class="ti ti-file me-1"></i>
                                                                                    {{ $result->human_size }}
                                                                                </small>
                                                                            @endif
                                                                            @if($result->uploader)
                                                                                <small class="text-muted">
                                                                                    <i class="ti ti-user me-1"></i>
                                                                                    {{ $result->uploader->name }}
                                                                                </small>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                                                            <i class="ti ti-dots-vertical"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li>
                                                                                <a class="dropdown-item" href="{{ $result->resource_type == 'file' ? route('files.download', $result->id) : route('files.index', ['folder' => $result->id]) }}">
                                                                                    <i class="ti ti-download me-2"></i>
                                                                                    {{ $result->resource_type == 'file' ? 'Download' : 'Open' }}
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a class="dropdown-item" href="#" onclick="shareResource('{{ $result->resource_type }}', {{ $result->id }}, '{{ $result->name }}')">
                                                                                    <i class="ti ti-share me-2"></i>
                                                                                    Share
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-center py-5">
                                                        <i class="ti ti-search-off display-1 text-muted mb-3"></i>
                                                        <h4 class="text-muted">No results found</h4>
                                                        <p class="text-muted">Try adjusting your search terms or filters</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Search Tips -->
                                            <div class="text-center py-5">
                                                <i class="ti ti-search display-1 text-primary mb-3"></i>
                                                <h4>Advanced Search Tips</h4>
                                                <div class="row mt-4">
                                                    <div class="col-md-4">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <i class="ti ti-file-text text-primary mb-2" style="font-size: 2rem;"></i>
                                                                <h6>Full-Text Search</h6>
                                                                <p class="text-muted small">Search within document contents, not just file names</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <i class="ti ti-filter text-primary mb-2" style="font-size: 2rem;"></i>
                                                                <h6>Advanced Filters</h6>
                                                                <p class="text-muted small">Filter by type, date, size, and more</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <i class="ti ti-brain text-primary mb-2" style="font-size: 2rem;"></i>
                                                                <h6>AI Suggestions</h6>
                                                                <p class="text-muted small">Get intelligent search suggestions</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
        $(document).ready(function() {
            let searchTimeout;
            const searchInput = $('#searchQuery');
            const suggestionsContainer = $('#searchSuggestions');
            const searchLoading = $('#searchLoading');
            
            // Real-time search suggestions
            searchInput.on('input', function() {
                const query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    suggestionsContainer.hide();
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route("files.ajax-search") }}',
                        method: 'GET',
                        data: { q: query },
                        beforeSend: function() {
                            searchLoading.addClass('show');
                        },
                        success: function(response) {
                            searchLoading.removeClass('show');
                            
                            if (response.suggestions.length > 0) {
                                let suggestionsHtml = '';
                                response.suggestions.forEach(function(suggestion) {
                                    suggestionsHtml += `
                                        <div class="search-suggestion" onclick="selectSuggestion('${suggestion}')">
                                            <i class="ti ti-search me-2"></i>
                                            ${suggestion}
                                        </div>
                                    `;
                                });
                                suggestionsContainer.html(suggestionsHtml).show();
                            } else {
                                suggestionsContainer.hide();
                            }
                        },
                        error: function() {
                            searchLoading.removeClass('show');
                            suggestionsContainer.hide();
                        }
                    });
                }, 300);
            });
            
            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-input-wrapper').length) {
                    suggestionsContainer.hide();
                }
            });
            
            // Select suggestion
            window.selectSuggestion = function(suggestion) {
                searchInput.val(suggestion);
                suggestionsContainer.hide();
                $('#advancedSearchForm').submit();
            };
            
            // Highlight search terms in results
            function highlightSearchTerms() {
                const query = '{{ $query }}';
                if (query) {
                    const regex = new RegExp(`(${query})`, 'gi');
                    $('.card-title, .content-preview').each(function() {
                        const text = $(this).text();
                        const highlighted = text.replace(regex, '<span class="search-highlight">$1</span>');
                        $(this).html(highlighted);
                    });
                }
            }
            
            highlightSearchTerms();
            
            // Initialize flatpickr for date inputs
            flatpickr('input[type="date"]', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });
            
            // Initialize select2 for tags
            $('#tagsSelect').select2({
                placeholder: 'Select tags...',
                allowClear: true
            });
        });
        
        // Share resource function
        function shareResource(type, id, name) {
            // Implementation for sharing
            alert(`Sharing ${type}: ${name}`);
        }
    </script>
@endpush