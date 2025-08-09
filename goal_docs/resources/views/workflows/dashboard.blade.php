@extends('layouts.app')

@section('title', 'Workflows Dashboard')

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
                            <img src="{{ asset('assets/img/illustrations/workflow-light.png') }}" height="140" alt="Workflows">
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
                    <button class="btn btn-primary btn-sm">
                        <i class="ti ti-plus ti-xs me-1"></i>Create Workflow
                    </button>
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
                            <tbody>
                                @foreach(($activeWorkflows ?? []) as $workflow)
                                <tr>
                                    <td>{{ $workflow['name'] ?? 'Unknown Workflow' }}</td>
                                    <td>{{ $workflow['document'] ?? 'N/A' }}</td>
                                    <td>{{ $workflow['current_step'] ?? 'N/A' }}</td>
                                    <td>{{ $workflow['assigned_to'] ?? 'Unassigned' }}</td>
                                    <td>{{ $workflow['started_at'] ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $status = $workflow['status'] ?? 'pending';
                                            $badgeClass = [
                                                'pending' => 'bg-label-warning',
                                                'active' => 'bg-label-primary',
                                                'completed' => 'bg-label-success',
                                                'rejected' => 'bg-label-danger'
                                            ][$status] ?? 'bg-label-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="ti ti-check ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-x ti-xs"></i>
                                        </button>
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
                                @foreach(($pendingActions ?? []) as $action)
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
                                @endforeach
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
            trendsChart.updateOptions({ xaxis: { categories: trends.categories } });
            trendsChart.updateSeries(trends.series);
            statusChart.updateOptions({ labels: status.labels });
            statusChart.updateSeries(status.series);
        })
        .catch(() => {/* no-op */});
});
</script>
@endpush
@endsection 