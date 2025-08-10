@extends('layouts.app')

@section('title', 'Workflows Dashboard')

@push('styles')
<!-- Force reload with timestamp -->
<meta name="cache-control" content="no-cache, no-store, must-revalidate">
<meta name="pragma" content="no-cache">
<meta name="expires" content="0">
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Workflow Management</h5>
                            <p class="mb-4">Create, manage, and monitor document approval workflows</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/workflow-light.png') }}?v=1.0.2" height="140" alt="Workflow Management">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Overview -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active Workflows</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['active_workflows'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="ti ti-git-branch ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Pending Actions</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['pending_actions'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ti ti-clock ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Completed</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['completed_workflows'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ti ti-check ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Success Rate</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['success_rate'] ?? 0 }}%</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="ti ti-percentage ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Charts -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Workflow Completion Trends</h5>
                </div>
                <div class="card-body">
                    <div id="workflowTrendsChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Workflow Status</h5>
                </div>
                <div class="card-body">
                    <div id="workflowStatusChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Workflows -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Active Workflow Instances</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('workflows.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-list ti-xs me-1"></i>All Workflows
                        </a>
                        <a href="{{ route('workflows.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus ti-xs me-1"></i>Create Workflow
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Workflow</th>
                                    <th>Document</th>
                                    <th>Current Step</th>
                                    <th>Assigned To</th>
                                    <th>Started</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="activeWorkflowsTable">
                                @forelse(($activeWorkflows ?? []) as $instance)
                                <tr data-instance-id="{{ $instance->id }}">
                                    <td>{{ $instance->workflow->name ?? 'Unknown Workflow' }}</td>
                                    <td>{{ $instance->file->name ?? 'N/A' }}</td>
                                    <td>Step {{ $instance->current_step ?? 1 }}</td>
                                    <td>{{ $instance->initiator->name ?? 'Unassigned' }}</td>
                                    <td>{{ $instance->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @php
                                            $status = $instance->status ?? 'in_progress';
                                            $badgeClass = [
                                                'pending' => 'bg-label-warning',
                                                'in_progress' => 'bg-label-primary',
                                                'completed' => 'bg-label-success',
                                                'rejected' => 'bg-label-danger'
                                            ][$status] ?? 'bg-label-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    </td>
                                    <td>
                                        @if($instance->file)
                                        <button class="btn btn-sm btn-outline-info" onclick="openDocumentPreview({{ $instance->file->id }}, '{{ addslashes($instance->file->original_name) }}', '{{ route('files.preview', $instance->file) }}', '{{ $instance->file->mime_type }}', {{ $instance->file->file_size }})" title="Preview Document">
                                            <i class="ti ti-file-text ti-xs"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewWorkflowInstance({{ $instance->id }})" title="View Details">
                                            <i class="ti ti-eye ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="approveWorkflowStep({{ $instance->id }})" title="Approve">
                                            <i class="ti ti-check ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="rejectWorkflowStep({{ $instance->id }})" title="Reject">
                                            <i class="ti ti-x ti-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-x ti-lg text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No active workflows</p>
                                            <small class="text-muted">Create a workflow to get started</small>
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

    <!-- Pending Actions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Pending Actions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Workflow</th>
                                    <th>Document</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($pendingActions ?? []) as $action)
                                <tr>
                                    <td>{{ $action['action_type'] ?? 'Review' }}</td>
                                    <td>{{ $action['workflow_name'] ?? 'Unknown' }}</td>
                                    <td>{{ $action['document_name'] ?? 'N/A' }}</td>
                                    <td>{{ $action['due_date'] ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $priority = $action['priority'] ?? 'medium';
                                            $badgeClass = [
                                                'low' => 'bg-label-success',
                                                'medium' => 'bg-label-warning',
                                                'high' => 'bg-label-danger'
                                            ][$priority] ?? 'bg-label-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($priority) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success">
                                            <i class="ti ti-check ti-xs me-1"></i>Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="ti ti-x ti-xs me-1"></i>Reject
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-inbox ti-lg text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No pending actions</p>
                                            <small class="text-muted">All workflows are up to date</small>
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendsEl = document.querySelector('#workflowTrendsChart');
    const statusEl = document.querySelector('#workflowStatusChart');

    // Initialize empty charts
    const trendsChart = new ApexCharts(trendsEl, {
        series: [],
        chart: { type: 'area', height: 300 },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: { categories: [] },
        colors: ['#28a745', '#ffc107']
    });
    const statusChart = new ApexCharts(statusEl, {
        series: [],
        chart: { type: 'donut', height: 300 },
        labels: [],
        colors: ['#007bff', '#ffc107', '#28a745'],
        responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
    });

    trendsChart.render();
    statusChart.render();

    // Fetch live data
    fetch("{{ route('workflows.stats') }}")
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) return;
            const { trends, status } = resp;
            
            // Handle empty trends data
            if (!trends.categories || trends.categories.length === 0) {
                document.querySelector('#workflowTrendsChart').innerHTML = 
                    '<div class="d-flex flex-column align-items-center justify-content-center h-100">' +
                    '<i class="ti ti-chart-line ti-lg text-muted mb-2"></i>' +
                    '<p class="text-muted mb-0">No workflow trend data available</p>' +
                    '<small class="text-muted">Data will appear when workflows are completed</small>' +
                    '</div>';
            } else {
                trendsChart.updateOptions({ xaxis: { categories: trends.categories } });
                trendsChart.updateSeries([
                    { name: 'Completed', data: trends.completed_series || [] },
                    { name: 'Pending', data: trends.pending_series || [] }
                ]);
            }
            
            // Handle empty status data
            if (!status.series || status.series.every(val => val === 0)) {
                document.querySelector('#workflowStatusChart').innerHTML = 
                    '<div class="d-flex flex-column align-items-center justify-content-center h-100">' +
                    '<i class="ti ti-chart-donut ti-lg text-muted mb-2"></i>' +
                    '<p class="text-muted mb-0">No workflow status data</p>' +
                    '<small class="text-muted">Data will appear when workflows are active</small>' +
                    '</div>';
            } else {
                statusChart.updateOptions({ labels: status.labels });
                statusChart.updateSeries(status.series);
            }
        })
        .catch(() => {/* no-op */});
});

// Workflow action functions
function viewWorkflowInstance(instanceId) {
    window.location.href = "{{ route('workflows.instance.show', ':id') }}".replace(':id', instanceId);
}

function approveWorkflowStep(instanceId) {
    if (!confirm('Are you sure you want to approve this workflow step?')) return;
    
    const comment = prompt('Add a comment (optional):');
    
    fetch("{{ route('workflows.instance.approve', ':id') }}".replace(':id', instanceId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: comment || '' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Workflow step approved successfully!');
            refreshWorkflowData();
        } else {
            alert('Error: ' + (data.error || 'Failed to approve step'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error approving workflow step');
    });
}

function rejectWorkflowStep(instanceId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    fetch("{{ route('workflows.instance.reject', ':id') }}".replace(':id', instanceId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Workflow step rejected successfully!');
            refreshWorkflowData();
        } else {
            alert('Error: ' + (data.error || 'Failed to reject step'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rejecting workflow step');
    });
}

function refreshWorkflowData() {
    // Refresh the charts
    fetch("{{ route('workflows.stats') }}")
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) return;
            const { trends, status } = resp;
            trendsChart.updateOptions({ xaxis: { categories: trends.categories } });
            trendsChart.updateSeries(trends.series);
            statusChart.updateOptions({ labels: status.labels });
            statusChart.updateSeries(status.series);
        })
        .catch(() => {/* no-op */});
    
    // Refresh the page to update tables
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}
</script>
@endpush
@endsection 