@extends('layouts.app')

@section('title', $report->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $report->name }}
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('reports.index') }}">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $report->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Report Overview -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Report Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Report Type</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $report->type)) }}</span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <p class="mb-0">
                                    @if($report->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Visibility</label>
                                <p class="mb-0">
                                    @if($report->is_public)
                                        <span class="badge bg-label-success">Public</span>
                                    @else
                                        <span class="badge bg-label-warning">Private</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Created By</label>
                                <p class="mb-0">{{ $report->user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Created</label>
                                <p class="mb-0">{{ $report->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Last Generated</label>
                                <p class="mb-0">
                                    @if($report->last_generated_at)
                                        {{ $report->last_generated_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($report->description)
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Description</label>
                        <p class="mb-0">{{ $report->description }}</p>
                    </div>
                    @endif

                    @if($report->config)
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Configuration</label>
                        <div class="row">
                            @if(isset($report->config['period']))
                            <div class="col-md-6">
                                <small class="text-muted">Time Period:</small>
                                <p class="mb-1">{{ $report->config['period'] }}</p>
                            </div>
                            @endif
                            @if(isset($report->config['user_scope']))
                            <div class="col-md-6">
                                <small class="text-muted">User Scope:</small>
                                <p class="mb-1">{{ ucfirst($report->config['user_scope']) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($report->isScheduled())
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Schedule</label>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Type:</small>
                                <p class="mb-1">{{ ucfirst($report->schedule['type']) }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Time:</small>
                                <p class="mb-1">{{ $report->schedule['time'] }}</p>
                            </div>
                            @if($report->schedule['type'] === 'weekly' && isset($report->schedule['day']))
                            <div class="col-md-6">
                                <small class="text-muted">Day:</small>
                                <p class="mb-1">{{ ucfirst($report->schedule['day']) }}</p>
                            </div>
                            @endif
                            @if($report->schedule['type'] === 'monthly' && isset($report->schedule['month_day']))
                            <div class="col-md-6">
                                <small class="text-muted">Day of Month:</small>
                                <p class="mb-1">{{ $report->schedule['month_day'] }}</p>
                            </div>
                            @endif
                            @if($report->next_generation_at)
                            <div class="col-md-6">
                                <small class="text-muted">Next Generation:</small>
                                <p class="mb-1">{{ $report->next_generation_at->format('M d, Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="generateReport('pdf')">
                            <i class="ti ti-file-text ti-xs me-1"></i>Generate PDF
                        </button>
                        <button type="button" class="btn btn-success" onclick="generateReport('excel')">
                            <i class="ti ti-file-spreadsheet ti-xs me-1"></i>Generate Excel
                        </button>
                        <button type="button" class="btn btn-info" onclick="generateReport('csv')">
                            <i class="ti ti-file-text ti-xs me-1"></i>Generate CSV
                        </button>
                        <hr>
                        <a href="{{ route('reports.edit', $report) }}" class="btn btn-outline-primary">
                            <i class="ti ti-edit ti-xs me-1"></i>Edit Report
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteReport()">
                            <i class="ti ti-trash ti-xs me-1"></i>Delete Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Generation Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Generation Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="mb-0 text-primary">{{ $generations->total() }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-success">{{ $generations->where('status', 'completed')->count() }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generation History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Generation History</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshGenerations()">
                        <i class="ti ti-refresh ti-xs me-1"></i>Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Generated</th>
                                    <th>Format</th>
                                    <th>Status</th>
                                    <th>File Size</th>
                                    <th>Generated By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generations as $generation)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">{{ $generation->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $generation->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ strtoupper($generation->format) }}</span>
                                    </td>
                                    <td>
                                        @if($generation->status === 'completed')
                                            <span class="badge bg-label-success">Completed</span>
                                        @elseif($generation->status === 'processing')
                                            <span class="badge bg-label-warning">Processing</span>
                                        @elseif($generation->status === 'failed')
                                            <span class="badge bg-label-danger">Failed</span>
                                        @else
                                            <span class="badge bg-label-secondary">{{ ucfirst($generation->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($generation->file_size)
                                            {{ formatBytes($generation->file_size) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ strtoupper(substr($generation->user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span>{{ $generation->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($generation->isCompleted() && $generation->file_path)
                                                <li><a class="dropdown-item" href="{{ route('reports.download', $generation) }}">
                                                    <i class="ti ti-download ti-xs me-2"></i>Download
                                                </a></li>
                                                @endif
                                                @if($generation->status === 'failed')
                                                <li><a class="dropdown-item" href="#" onclick="retryGeneration({{ $generation->id }})">
                                                    <i class="ti ti-refresh ti-xs me-2"></i>Retry
                                                </a></li>
                                                @endif
                                                <li><a class="dropdown-item" href="#" onclick="viewGenerationDetails({{ $generation->id }})">
                                                    <i class="ti ti-eye ti-xs me-2"></i>View Details
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-report ti-2x mb-2"></i>
                                            <h6>No generations yet</h6>
                                            <p class="mb-3">Generate your first report to see it here</p>
                                            <button type="button" class="btn btn-primary" onclick="generateReport('pdf')">
                                                <i class="ti ti-file-text ti-xs me-1"></i>Generate Report
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($generations->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $generations->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generation Progress Modal -->
<div class="modal fade" id="generationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generating Report</h5>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p id="generationMessage">Initializing report generation...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generation Details Modal -->
<div class="modal fade" id="generationDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="generationDetailsContent">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let generationCheckInterval;

// Generate report
function generateReport(format) {
    const modal = new bootstrap.Modal(document.getElementById('generationModal'));
    modal.show();
    
    document.getElementById('generationMessage').textContent = 'Starting report generation...';
    
    fetch('{{ route("reports.generate", $report) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ format: format })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generationMessage').textContent = 'Report generation in progress...';
            checkGenerationStatus(data.generation.id);
        } else {
            modal.hide();
            showAlert('error', data.message || 'Failed to start report generation');
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Generation failed:', error);
        showAlert('error', 'Failed to start report generation');
    });
}

// Check generation status
function checkGenerationStatus(generationId) {
    generationCheckInterval = setInterval(() => {
        fetch(`{{ url('reports/status') }}/${generationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const generation = data.generation;
                    
                    if (generation.status === 'completed') {
                        clearInterval(generationCheckInterval);
                        bootstrap.Modal.getInstance(document.getElementById('generationModal')).hide();
                        showAlert('success', 'Report generated successfully!');
                        
                        // Download the file
                        window.open(`{{ url('reports/download') }}/${generation.id}`, '_blank');
                        
                        // Refresh the page to show new generation
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else if (generation.status === 'failed') {
                        clearInterval(generationCheckInterval);
                        bootstrap.Modal.getInstance(document.getElementById('generationModal')).hide();
                        showAlert('error', 'Report generation failed: ' + (generation.error_message || 'Unknown error'));
                    }
                }
            })
            .catch(error => {
                clearInterval(generationCheckInterval);
                bootstrap.Modal.getInstance(document.getElementById('generationModal')).hide();
                console.error('Status check failed:', error);
                showAlert('error', 'Failed to check generation status');
            });
    }, 2000);
}

// Delete report
function deleteReport() {
    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        fetch('{{ route("reports.destroy", $report) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Report deleted successfully');
                setTimeout(() => {
                    window.location.href = '{{ route("reports.index") }}';
                }, 1500);
            } else {
                showAlert('error', data.message || 'Failed to delete report');
            }
        })
        .catch(error => {
            console.error('Delete failed:', error);
            showAlert('error', 'Failed to delete report');
        });
    }
}

// View generation details
function viewGenerationDetails(generationId) {
    const modal = new bootstrap.Modal(document.getElementById('generationDetailsModal'));
    modal.show();
    
    // Load generation details
    fetch(`{{ url('reports/status') }}/${generationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const generation = data.generation;
                const content = document.getElementById('generationDetailsContent');
                
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <p class="mb-3">
                                ${generation.status === 'completed' ? '<span class="badge bg-label-success">Completed</span>' : 
                                  generation.status === 'processing' ? '<span class="badge bg-label-warning">Processing</span>' : 
                                  generation.status === 'failed' ? '<span class="badge bg-label-danger">Failed</span>' : 
                                  '<span class="badge bg-label-secondary">' + generation.status.charAt(0).toUpperCase() + generation.status.slice(1) + '</span>'}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Format</label>
                            <p class="mb-3">
                                <span class="badge bg-label-info">${generation.format.toUpperCase()}</span>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Created</label>
                            <p class="mb-3">${new Date(generation.created_at).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Generated By</label>
                            <p class="mb-3">${generation.user.name}</p>
                        </div>
                    </div>
                    ${generation.file_size ? `
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">File Size</label>
                            <p class="mb-3">${formatBytes(generation.file_size)}</p>
                        </div>
                    </div>
                    ` : ''}
                    ${generation.error_message ? `
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Error Message</label>
                            <div class="alert alert-danger">
                                ${generation.error_message}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;
            }
        })
        .catch(error => {
            console.error('Failed to load generation details:', error);
            document.getElementById('generationDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load generation details
                </div>
            `;
        });
}

// Retry generation
function retryGeneration(generationId) {
    if (confirm('Are you sure you want to retry this generation?')) {
        // This would need to be implemented in the controller
        showAlert('info', 'Retry functionality will be implemented soon');
    }
}

// Refresh generations
function refreshGenerations() {
    location.reload();
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

// Format bytes helper function
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush
