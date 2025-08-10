@extends('layouts.app')

@section('title', 'Batch Processing')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Batch Processing
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Batch Processing</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Jobs</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $stats['total_jobs'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-settings ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Completed</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $stats['completed_jobs'] }}</h4>
                                <small class="text-success fw-semibold">+{{ $stats['completed_with_errors'] }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-check ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Processing</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $stats['processing_jobs'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-loader ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Success Rate</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ round($stats['success_rate'], 1) }}%</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-chart-line ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Status -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Queue Status</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshStats()">
                        <i class="ti ti-refresh ti-xs"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="ti ti-clock ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $queueStatus['pending_jobs'] }}</h6>
                                    <small class="text-muted">Pending Jobs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti ti-loader ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $queueStatus['processing_jobs'] }}</h6>
                                    <small class="text-muted">Processing Jobs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-check ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $queueStatus['completed_today'] }}</h6>
                                    <small class="text-muted">Completed Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="ti ti-x ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $queueStatus['failed_today'] }}</h6>
                                    <small class="text-muted">Failed Today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Batch Processing</h5>
                        <a href="{{ route('batch-processing.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus ti-xs me-1"></i>Create New Batch Job
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Jobs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Batch Jobs</h5>
                </div>
                <div class="card-body">
                    @if($batchJobs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Operation</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Files</th>
                                        <th>Created</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchJobs as $batchJob)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-label-{{ $batchJob->operation_type_color }} rounded-pill me-2">
                                                    <i class="{{ $batchJob->operation_type_icon }} ti-xs"></i>
                                                </span>
                                                <span>{{ $batchJob->operation_type_description }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $batchJob->status_color }}">
                                                {{ ucfirst(str_replace('_', ' ', $batchJob->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 100px; height: 6px;">
                                                    <div class="progress-bar bg-{{ $batchJob->status_color }}" 
                                                         style="width: {{ $batchJob->progress }}%"></div>
                                                </div>
                                                <small>{{ $batchJob->formatted_progress }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $batchJob->processed_files }}/{{ $batchJob->total_files }}
                                                @if($batchJob->successful_files > 0)
                                                    <span class="text-success">({{ $batchJob->successful_files }} ✓)</span>
                                                @endif
                                                @if($batchJob->failed_files > 0)
                                                    <span class="text-danger">({{ $batchJob->failed_files }} ✗)</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{ $batchJob->created_at->format('M j, Y g:i A') }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $batchJob->duration }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('batch-processing.show', $batchJob) }}">
                                                            <i class="ti ti-eye ti-xs me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    @if($batchJob->isPending())
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="startBatchJob({{ $batchJob->id }})">
                                                            <i class="ti ti-player-play ti-xs me-2"></i>Start Processing
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if($batchJob->isProcessing())
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="cancelBatchJob({{ $batchJob->id }})">
                                                            <i class="ti ti-player-stop ti-xs me-2"></i>Cancel
                                                        </a>
                                                    </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteBatchJob({{ $batchJob->id }})">
                                                            <i class="ti ti-trash ti-xs me-2"></i>Delete
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
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $batchJobs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-settings ti-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No batch jobs found</h5>
                            <p class="text-muted">Create your first batch job to start processing multiple files at once.</p>
                            <a href="{{ route('batch-processing.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus ti-xs me-1"></i>Create Batch Job
                            </a>
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

// Refresh statistics
function refreshStats() {
    fetch('/files/batch-processing/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats display (you can implement this based on your needs)
                location.reload();
            }
        })
        .catch(error => {
            console.error('Failed to refresh stats:', error);
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
</script>
@endpush
