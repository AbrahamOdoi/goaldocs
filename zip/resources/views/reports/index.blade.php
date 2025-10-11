@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Reports Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Reports</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Reports</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="totalReports">{{ $stats['total_reports'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-file-report ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Active Reports</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="activeReports">{{ $stats['active_reports'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-check-circle ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Scheduled Reports</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="scheduledReports">{{ $stats['scheduled_reports'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-clock ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Total Generations</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="totalGenerations">{{ $stats['total_generations'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-download ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('reports.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus ti-xs me-1"></i>Create New Report
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshStats()">
                                <i class="ti ti-refresh ti-xs me-1"></i>Refresh Stats
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Reports -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">My Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Schedule</th>
                                    <th>Last Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-file-report ti-xs"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $report->name }}</h6>
                                                @if($report->description)
                                                <small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $report->type)) }}</span>
                                    </td>
                                    <td>
                                        @if($report->is_active)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->isScheduled())
                                            <span class="badge bg-label-warning">{{ ucfirst($report->schedule['type']) }}</span>
                                        @else
                                            <span class="text-muted">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->last_generated_at)
                                            <small class="text-muted">{{ $report->last_generated_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('reports.show', $report) }}">
                                                    <i class="ti ti-eye ti-xs me-2"></i>View
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('reports.edit', $report) }}">
                                                    <i class="ti ti-edit ti-xs me-2"></i>Edit
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'pdf')">
                                                    <i class="ti ti-file-text ti-xs me-2"></i>Generate PDF
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'excel')">
                                                    <i class="ti ti-file-spreadsheet ti-xs me-2"></i>Generate Excel
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'csv')">
                                                    <i class="ti ti-file-text ti-xs me-2"></i>Generate CSV
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteReport({{ $report->id }})">
                                                    <i class="ti ti-trash ti-xs me-2"></i>Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-report ti-3x mb-3 text-muted"></i>
                                            <h6>No reports created yet</h6>
                                            <p class="mb-3">Create your first report to get started</p>
                                            <a href="{{ route('reports.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus ti-xs me-1"></i>Create Report
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Reports -->
    @if($publicReports->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Public Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Created By</th>
                                    <th>Last Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($publicReports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="ti ti-users ti-xs"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $report->name }}</h6>
                                                @if($report->description)
                                                <small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $report->type)) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ strtoupper(substr($report->user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span>{{ $report->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($report->last_generated_at)
                                            <small class="text-muted">{{ $report->last_generated_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('reports.show', $report) }}">
                                                    <i class="ti ti-eye ti-xs me-2"></i>View
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'pdf')">
                                                    <i class="ti ti-file-text ti-xs me-2"></i>Generate PDF
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'excel')">
                                                    <i class="ti ti-file-spreadsheet ti-xs me-2"></i>Generate Excel
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateReport({{ $report->id }}, 'csv')">
                                                    <i class="ti ti-file-text ti-xs me-2"></i>Generate CSV
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
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
@endsection

@push('scripts')
<script>
let generationCheckInterval;

// Generate report
function generateReport(reportId, format) {
    const modal = new bootstrap.Modal(document.getElementById('generationModal'));
    modal.show();
    
    document.getElementById('generationMessage').textContent = 'Starting report generation...';
    
    fetch(`{{ url('reports') }}/${reportId}/generate`, {
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
                        
                        // Refresh stats
                        refreshStats();
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
function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        fetch(`{{ url('reports') }}/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Report deleted successfully');
                location.reload();
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

// Refresh stats
function refreshStats() {
    fetch('{{ route("reports.api.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalReports').textContent = data.stats.total_reports;
                document.getElementById('activeReports').textContent = data.stats.active_reports;
                document.getElementById('scheduledReports').textContent = data.stats.scheduled_reports;
                document.getElementById('totalGenerations').textContent = data.stats.total_generations;
            }
        })
        .catch(error => {
            console.error('Failed to refresh stats:', error);
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

// Load stats on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshStats();
});
</script>
@endpush
