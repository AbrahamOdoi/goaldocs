@extends('layouts.app')

@section('title', 'Create Batch Job')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Create Batch Job
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('batch-processing.index') }}">Batch Processing</a>
                    </li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">New Batch Job</h5>
                </div>
                <div class="card-body">
                    <form id="batchJobForm">
                        <!-- Operation Type Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Operation Type <span class="text-danger">*</span></label>
                                <div class="row">
                                    @foreach($supportedOperations as $key => $description)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check custom-option custom-option-icon">
                                            <input class="form-check-input" type="radio" name="operation_type" 
                                                   id="operation_{{ $key }}" value="{{ $key }}" required>
                                            <label class="form-check-label custom-option-content" for="operation_{{ $key }}">
                                                <span class="custom-option-body">
                                                    <i class="ti ti-{{ $key === 'ocr' ? 'scan' : ($key === 'conversion' ? 'refresh' : ($key === 'text_extraction' ? 'file-text' : ($key === 'thumbnail_generation' ? 'photo' : 'info-circle'))) }} ti-2x mb-2"></i>
                                                    <span class="custom-option-title">{{ $description }}</span>
                                                    <small class="custom-option-text">
                                                        @switch($key)
                                                            @case('ocr')
                                                                Extract text from images and scanned documents
                                                                @break
                                                            @case('conversion')
                                                                Convert files between different formats
                                                                @break
                                                            @case('text_extraction')
                                                                Extract and analyze text content
                                                                @break
                                                            @case('thumbnail_generation')
                                                                Generate thumbnails for files
                                                                @break
                                                            @case('metadata_extraction')
                                                                Extract file metadata and properties
                                                                @break
                                                        @endswitch
                                                    </small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- File Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Select Files <span class="text-danger">*</span></label>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllFiles()">
                                            <i class="ti ti-check ti-xs me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllFiles()">
                                            <i class="ti ti-x ti-xs me-1"></i>Deselect All
                                        </button>
                                    </div>
                                    <div>
                                        <span class="text-muted">Selected: <span id="selectedCount">0</span> files</span>
                                    </div>
                                </div>
                                
                                @if($files->count() > 0)
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width: 50px;">
                                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                                    </th>
                                                    <th>File Name</th>
                                                    <th>Type</th>
                                                    <th>Size</th>
                                                    <th>Uploaded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($files as $file)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input file-checkbox" 
                                                               name="file_ids[]" value="{{ $file->id }}" 
                                                               data-file-name="{{ $file->name }}">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ti ti-{{ $file->mime_type === 'application/pdf' ? 'file-text' : (str_starts_with($file->mime_type, 'image/') ? 'photo' : 'file') }} ti-sm me-2 text-muted"></i>
                                                            <span>{{ $file->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-label-secondary">{{ strtoupper($file->extension) }}</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            @if($file->file_size < 1024)
                                                                {{ $file->file_size }} B
                                                            @elseif($file->file_size < 1048576)
                                                                {{ round($file->file_size / 1024, 1) }} KB
                                                            @else
                                                                {{ round($file->file_size / 1048576, 1) }} MB
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $file->created_at->format('M j, Y') }}</small>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="ti ti-folder-off ti-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No files found</h5>
                                        <p class="text-muted">Upload some files first to create a batch job.</p>
                                        <a href="{{ route('files.index') }}" class="btn btn-primary">
                                            <i class="ti ti-upload ti-xs me-1"></i>Upload Files
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Operation-specific Options -->
                        <div id="operationOptions" style="display: none;">
                            <!-- OCR Options -->
                            <div id="ocrOptions" class="operation-option" style="display: none;">
                                <h6 class="mb-3">OCR Settings</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Language</label>
                                        <select class="form-select" name="options[language]">
                                            <option value="eng">English</option>
                                            <option value="fra">French</option>
                                            <option value="deu">German</option>
                                            <option value="spa">Spanish</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Page Segmentation Mode</label>
                                        <select class="form-select" name="options[psm]">
                                            <option value="3">Automatic (Default)</option>
                                            <option value="6">Uniform Block</option>
                                            <option value="8">Single Word</option>
                                            <option value="13">Raw Line</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Conversion Options -->
                            <div id="conversionOptions" class="operation-option" style="display: none;">
                                <h6 class="mb-3">Conversion Settings</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Target Format <span class="text-danger">*</span></label>
                                        <select class="form-select" name="options[target_format]" required>
                                            <option value="">Select format...</option>
                                            <option value="pdf">PDF</option>
                                            <option value="docx">DOCX (Word)</option>
                                            <option value="xlsx">XLSX (Excel)</option>
                                            <option value="pptx">PPTX (PowerPoint)</option>
                                            <option value="jpg">JPG</option>
                                            <option value="png">PNG</option>
                                            <option value="txt">TXT</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quality</label>
                                        <select class="form-select" name="options[quality]">
                                            <option value="high">High</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Text Extraction Options -->
                            <div id="textExtractionOptions" class="operation-option" style="display: none;">
                                <h6 class="mb-3">Text Extraction Settings</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Extraction Type</label>
                                        <select class="form-select" name="options[extraction_type]">
                                            <option value="full_text">Full Text</option>
                                            <option value="structured_text">Structured Text</option>
                                            <option value="tables">Tables Only</option>
                                            <option value="forms">Form Fields</option>
                                            <option value="headings">Headings</option>
                                            <option value="lists">Lists</option>
                                            <option value="metadata">Metadata</option>
                                            <option value="keywords">Keywords</option>
                                            <option value="entities">Named Entities</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Language Detection</label>
                                        <select class="form-select" name="options[language_detection]">
                                            <option value="auto">Auto-detect</option>
                                            <option value="en">English</option>
                                            <option value="fr">French</option>
                                            <option value="de">German</option>
                                            <option value="es">Spanish</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Thumbnail Options -->
                            <div id="thumbnailOptions" class="operation-option" style="display: none;">
                                <h6 class="mb-3">Thumbnail Settings</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Width (px)</label>
                                        <input type="number" class="form-control" name="options[width]" value="200" min="50" max="1000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Height (px)</label>
                                        <input type="number" class="form-control" name="options[height]" value="200" min="50" max="1000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Quality</label>
                                        <select class="form-select" name="options[quality]">
                                            <option value="high">High</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('batch-processing.index') }}" class="btn btn-secondary">
                                        <i class="ti ti-arrow-left ti-xs me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                        <i class="ti ti-plus ti-xs me-1"></i>Create Batch Job
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

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Creating Batch Job...</h5>
                <p class="text-muted">Please wait while we create your batch job.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// File selection handling
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    const submitBtn = document.getElementById('submitBtn');
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        fileCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
        updateSubmitButton();
    });
    
    // Individual checkbox handling
    fileCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updateSubmitButton();
            updateSelectAllState();
        });
    });
    
    // Operation type selection
    const operationRadios = document.querySelectorAll('input[name="operation_type"]');
    operationRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            showOperationOptions(this.value);
        });
    });
    
    // Form submission
    document.getElementById('batchJobForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createBatchJob();
    });
});

// Update selected count
function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.file-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selectedCount;
}

// Update submit button state
function updateSubmitButton() {
    const selectedFiles = document.querySelectorAll('.file-checkbox:checked').length;
    const selectedOperation = document.querySelector('input[name="operation_type"]:checked');
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = selectedFiles === 0 || !selectedOperation;
}

// Update select all state
function updateSelectAllState() {
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
    
    if (checkedCount === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCount === fileCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }
}

// Select all files
function selectAllFiles() {
    document.getElementById('selectAll').checked = true;
    document.querySelectorAll('.file-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
    updateSubmitButton();
}

// Deselect all files
function deselectAllFiles() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.file-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
    updateSubmitButton();
}

// Show operation-specific options
function showOperationOptions(operationType) {
    // Hide all option sections
    document.querySelectorAll('.operation-option').forEach(option => {
        option.style.display = 'none';
    });
    
    // Show the selected operation options
    const optionElement = document.getElementById(operationType + 'Options');
    if (optionElement) {
        optionElement.style.display = 'block';
    }
    
    // Show the options container
    document.getElementById('operationOptions').style.display = 'block';
    
    updateSubmitButton();
}

// Create batch job
function createBatchJob() {
    const form = document.getElementById('batchJobForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key.includes('[')) {
            // Handle nested options
            const matches = key.match(/^(\w+)\[(\w+)\]$/);
            if (matches) {
                if (!data[matches[1]]) {
                    data[matches[1]] = {};
                }
                data[matches[1]][matches[2]] = value;
            }
        } else {
            data[key] = value;
        }
    }
    
    // Show loading modal
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    // Submit the request
    fetch('{{ route("batch-processing.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1500);
        } else {
            showAlert('error', data.error);
        }
    })
    .catch(error => {
        loadingModal.hide();
        showAlert('error', 'Failed to create batch job');
        console.error('Error:', error);
    });
}

// Show alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-xxl');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush
