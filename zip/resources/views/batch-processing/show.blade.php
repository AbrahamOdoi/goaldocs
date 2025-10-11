@extends('layouts.app')

@section('title', 'Batch Job Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Batch Job Details
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('batch-processing.index') }}">Batch Processing</a>
                    </li>
                    <li class="breadcrumb-item active">Job #{{ $batchJob->id }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Job Overview -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Status</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">
                                    <span class="badge bg-{{ $batchJob->status_color }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $batchJob->status)) }}
                                    </span>
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-label-{{ $batchJob->status_color }} rounded p-2">
                            <i class="ti ti-{{ $batchJob->status === 'processing' ? 'loader' : ($batchJob->status === 'completed' ? 'check' : ($batchJob->status === 'failed' ? 'x' : 'clock')) }} ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Progress</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $batchJob->formatted_progress }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-chart-line ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Files Processed</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $batchJob->processed_files }}/{{ $batchJob->total_files }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-files ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Duration</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $batchJob->duration }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-clock ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Progress Bar -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Progress</h5>
                    @if($batchJob->isProcessing())
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshStatus()">
                            <i class="ti ti-refresh ti-xs"></i> Refresh
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Processing Progress</span>
                            <span>{{ $batchJob->formatted_progress }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-{{ $batchJob->status_color }}" 
                                 style="width: {{ $batchJob->progress }}%"></div>
                        </div>
                    </div>
                    
                    @if($batchJob->isProcessing())
                        <div class="d-flex align-items-center text-muted">
                            <i class="ti ti-clock ti-xs me-1"></i>
                            <small>Estimated time remaining: {{ $batchJob->estimated_time_remaining }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Logs -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Progress Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Time</th>
                                    <th>Progress</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <small>{{ $log['processed_at'] }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $log['progress'] }}%</small>
                                    </td>
                                    <td>
                                        <small>{{ $log['message'] }}</small>
                                    </td>
                                    <td>
                                        @if($log['success'])
                                            <span class="badge bg-success">✓</span>
                                        @else
                                            <span class="badge bg-danger">✗</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No progress logs available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Job Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Job Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Operation Type</label>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-{{ $batchJob->operation_type_color }} rounded-pill me-2">
                                <i class="{{ $batchJob->operation_type_icon }} ti-xs"></i>
                            </span>
                            <span>{{ $batchJob->operation_type_description }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Created</label>
                        <p class="mb-0">{{ $batchJob->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    
                    @if($batchJob->started_at)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Started</label>
                        <p class="mb-0">{{ $batchJob->started_at->format('M j, Y g:i A') }}</p>
                    </div>
                    @endif
                    
                    @if($batchJob->completed_at)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Completed</label>
                        <p class="mb-0">{{ $batchJob->completed_at->format('M j, Y g:i A') }}</p>
                    </div>
                    @endif
                    
                    @if($batchJob->error_message)
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger">Error Message</label>
                        <p class="mb-0 text-danger">{{ $batchJob->error_message }}</p>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Options</label>
                        <p class="mb-0">{{ $batchJob->formatted_options }}</p>
                    </div>
                </div>
            </div>

            <!-- File Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">File Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $batchJob->total_files }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $batchJob->successful_files }}</h4>
                                <small class="text-muted">Success</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-danger">{{ $batchJob->failed_files }}</h4>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                    
                    @if($batchJob->total_files > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Success Rate</small>
                            <small>{{ $batchJob->success_rate }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $batchJob->success_rate }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($batchJob->isPending())
                            <button type="button" class="btn btn-primary" onclick="startBatchJob({{ $batchJob->id }})">
                                <i class="ti ti-player-play ti-xs me-1"></i>Start Processing
                            </button>
                        @endif
                        
                        @if($batchJob->isProcessing())
                            <button type="button" class="btn btn-warning" onclick="cancelBatchJob({{ $batchJob->id }})">
                                <i class="ti ti-player-stop ti-xs me-1"></i>Cancel Job
                            </button>
                        @endif
                        
                        <a href="{{ route('batch-processing.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left ti-xs me-1"></i>Back to List
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBatchJob({{ $batchJob->id }})">
                            <i class="ti ti-trash ti-xs me-1"></i>Delete Job
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Files List -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Files in Batch Job</h5>
                </div>
                <div class="card-body">
                    @if($files->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($files as $file)
                                    <tr>
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
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('files.download', $file) }}">
                                                            <i class="ti ti-download ti-xs me-2"></i>Download
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('files.preview', $file) }}" target="_blank">
                                                            <i class="ti ti-eye ti-xs me-2"></i>Preview
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let statusRefreshInterval;

// Start batch job
function startBatchJob(batchJobId) {
    if (confirm('Are you sure you want to start this batch job?')) {
        fetch(`/files/batch-processing/${batchJobId}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.error);
            }
        })
        .catch(error => {
            showAlert('error', 'Failed to start batch job');
        });
    }
}

// Cancel batch job
function cancelBatchJob(batchJobId) {
    if (confirm('Are you sure you want to cancel this batch job?')) {
        fetch(`/files/batch-processing/${batchJobId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.error);
            }
        })
        .catch(error => {
            showAlert('error', 'Failed to cancel batch job');
        });
    }
}

// Delete batch job
function deleteBatchJob(batchJobId) {
    showConfirmModal(
        'Are you sure you want to delete this batch job? This action cannot be undone.',
        () => {
            fetch(`/files/batch-processing/${batchJobId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    if (data.redirect_url) {
                        setTimeout(() => window.location.href = data.redirect_url, 1500);
                    } else {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showAlert('error', data.error);
                }
            })
            .catch(error => {
                showAlert('error', 'Failed to delete batch job');
            });
        }
    );
}

// Refresh status
function refreshStatus() {
    fetch(`/files/batch-processing/{{ $batchJob->id }}/status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update progress bar
                const progressBar = document.querySelector('.progress-bar');
                const progressText = document.querySelector('.progress span:last-child');
                
                if (progressBar && progressText) {
                    progressBar.style.width = data.status.progress + '%';
                    progressText.textContent = data.status.progress + '%';
                }
                
                // Update status badge
                const statusBadge = document.querySelector('.badge.bg-{{ $batchJob->status_color }}');
                if (statusBadge) {
                    statusBadge.className = `badge bg-${data.status.status === 'processing' ? 'info' : (data.status.status === 'completed' ? 'success' : (data.status.status === 'failed' ? 'danger' : 'warning'))}`;
                    statusBadge.textContent = data.status.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                }
                
                // If job is completed or failed, stop auto-refresh
                if (data.is_completed || data.is_failed || data.is_cancelled) {
                    clearInterval(statusRefreshInterval);
                    setTimeout(() => location.reload(), 2000);
                }
            }
        })
        .catch(error => {
            console.error('Failed to refresh status:', error);
        });
}

// Show confirmation modal
function showConfirmModal(message, onConfirm) {
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmButton').onclick = () => {
        onConfirm();
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    };
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
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

// Auto-refresh status if job is processing
document.addEventListener('DOMContentLoaded', function() {
    @if($batchJob->isProcessing())
        statusRefreshInterval = setInterval(refreshStatus, 5000); // Refresh every 5 seconds
    @endif
});
</script>
@endpush
