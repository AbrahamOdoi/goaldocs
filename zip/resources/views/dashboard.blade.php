@extends('layouts.app')

@section('title', 'Dashboard - GoalDocs')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="text-white mb-2">Welcome back, {{ $user->name }}! 👋</h2>
                            <p class="text-white-50 mb-0">Here's what's happening with your documents today</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-light btn-sm period-filter active" data-period="7d">7 Days</button>
                                <button type="button" class="btn btn-light btn-sm period-filter" data-period="30d">30 Days</button>
                                <button type="button" class="btn btn-light btn-sm period-filter" data-period="90d">90 Days</button>
                                <button type="button" class="btn btn-light btn-sm period-filter" data-period="1y">1 Year</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <!-- Total Files -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted text-uppercase mb-1 small fw-bold">Total Files</div>
                            <h3 class="mb-0">{{ number_format($overview['total_files']) }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="ti ti-arrow-up"></i> {{ $overview['files_this_week'] }} this week
                                </span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-label-primary">
                            <i class="ti ti-file ti-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage Used -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted text-uppercase mb-1 small fw-bold">Storage Used</div>
                            <h3 class="mb-0">{{ $overview['storage_formatted'] }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    <i class="ti ti-trending-up"></i> {{ $overview['growth_rate'] > 0 ? '+' : '' }}{{ $overview['growth_rate'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-label-info">
                            <i class="ti ti-database ti-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted text-uppercase mb-1 small fw-bold">Active Users</div>
                            <h3 class="mb-0">{{ number_format($overview['active_users']) }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    Last 7 days
                                </span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-label-success">
                            <i class="ti ti-users ti-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Folders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted text-uppercase mb-1 small fw-bold">Total Folders</div>
                            <h3 class="mb-0">{{ number_format($overview['total_folders']) }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-warning">
                                    <i class="ti ti-folders"></i> Organized
                                </span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-label-warning">
                            <i class="ti ti-folder ti-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Activity Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-primary">
                            <i class="ti ti-file-upload"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Files Today</p>
                            <h5 class="mb-0">{{ $quickStats['files_today'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-info">
                            <i class="ti ti-download"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Downloads Today</p>
                            <h5 class="mb-0">{{ $quickStats['downloads_today'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-success">
                            <i class="ti ti-share"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Active Shares</p>
                            <h5 class="mb-0">{{ $quickStats['shares_active'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-warning">
                            <i class="ti ti-clock"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Pending Tasks</p>
                            <h5 class="mb-0">{{ $quickStats['pending_tasks'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Activity Trend Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Activity Trend</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('files.analytics.dashboard') }}">View Details</a></li>
                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="activityTrendChart"></div>
                </div>
            </div>
        </div>

        <!-- Activity Distribution -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Activity Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="activityDistributionChart"></div>
                    <div class="mt-3">
                        @foreach($activity['by_type'] as $type => $count)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-dot bg-{{ ['upload' => 'primary', 'download' => 'info', 'view' => 'success', 'share' => 'warning'][$type] ?? 'secondary' }} me-2"></span>
                                <span class="text-capitalize">{{ $type }}</span>
                            </div>
                            <strong>{{ $count }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage and Documents Row -->
    <div class="row mb-4">
        <!-- Storage Distribution -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Storage by File Type</h5>
                </div>
                <div class="card-body">
                    <div id="storageDistributionChart"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activities</h5>
                    <a href="{{ route('files.index') }}" class="btn btn-sm btn-text-secondary">View All</a>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        @forelse($recentActivities as $activity)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="avatar avatar-sm me-3 bg-label-{{ ['upload' => 'primary', 'download' => 'info', 'view' => 'success', 'share' => 'warning', 'delete' => 'danger'][$activity->activity_type] ?? 'secondary' }}">
                                <i class="ti ti-{{ ['upload' => 'file-upload', 'download' => 'download', 'view' => 'eye', 'share' => 'share', 'delete' => 'trash'][$activity->activity_type] ?? 'activity' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $activity->user->name ?? 'Unknown User' }}</h6>
                                        <p class="mb-0 text-muted small">
                                            {{ ucfirst($activity->activity_type) }} 
                                            @if($activity->file)
                                            <strong>{{ Str::limit($activity->file->name, 30) }}</strong>
                                            @else
                                            a file
                                            @endif
                                        </p>
                                    </div>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="ti ti-mood-empty text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No recent activities</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow and Team Statistics -->
    <div class="row mb-4">
        <!-- Workflow Status -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Workflow Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Workflows</span>
                            <strong>{{ $workflows['total_workflows'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Active</span>
                            <span class="badge bg-primary">{{ $workflows['active_workflows'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Completed</span>
                            <span class="badge bg-success">{{ $workflows['completed_workflows'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Pending Approval</span>
                            <span class="badge bg-warning">{{ $workflows['pending_approvals'] }}</span>
                        </div>
                    </div>
                    <a href="{{ route('workflows.index') }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-arrow-right me-1"></i> View Workflows
                    </a>
                </div>
            </div>
        </div>

        <!-- Most Popular Files -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Most Popular Files</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($documents['most_popular'] as $file)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3 bg-label-primary">
                                    <i class="ti ti-file"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 text-truncate" style="max-width: 200px;">{{ $file->name }}</h6>
                                    <small class="text-muted">{{ $file->activity_count ?? 0 }} activities</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ $file->created_at->format('M d') }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted py-3">No data available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($user->is_admin)
        <!-- Top Contributors -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Contributors</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($team['top_contributors'] as $contributor)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    @if($contributor->avatar)
                                    <img src="{{ asset('storage/' . $contributor->avatar) }}" alt="{{ $contributor->name }}" class="rounded-circle">
                                    @else
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ strtoupper(substr($contributor->name, 0, 1)) }}
                                    </span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $contributor->name }}</h6>
                                    <small class="text-muted">{{ $contributor->file_count }} files</small>
                                </div>
                                <div>
                                    <span class="badge bg-label-primary">
                                        <i class="ti ti-trophy"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted py-3">No contributors yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($user->is_admin)
    <!-- System Health Monitor -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-label-{{ $systemHealth['storage_status'] === 'healthy' ? 'success' : 'warning' }}">
                                    <i class="ti ti-database"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Storage</h6>
                                    <span class="badge bg-{{ $systemHealth['storage_status'] === 'healthy' ? 'success' : 'warning' }}">
                                        {{ ucfirst($systemHealth['storage_status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-label-{{ $systemHealth['api_status'] === 'healthy' ? 'success' : 'warning' }}">
                                    <i class="ti ti-api"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">API</h6>
                                    <span class="badge bg-{{ $systemHealth['api_status'] === 'healthy' ? 'success' : 'warning' }}">
                                        {{ ucfirst($systemHealth['api_status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-label-{{ $systemHealth['database_status'] === 'healthy' ? 'success' : 'warning' }}">
                                    <i class="ti ti-database-import"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Database</h6>
                                    <span class="badge bg-{{ $systemHealth['database_status'] === 'healthy' ? 'success' : 'warning' }}">
                                        {{ ucfirst($systemHealth['database_status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-label-{{ $systemHealth['queue_status'] === 'healthy' ? 'success' : 'warning' }}">
                                    <i class="ti ti-list-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Queue</h6>
                                    <span class="badge bg-{{ $systemHealth['queue_status'] === 'healthy' ? 'success' : 'warning' }}">
                                        {{ ucfirst($systemHealth['queue_status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Overview -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Security Overview</h5>
                    <a href="{{ route('security-monitoring.dashboard') }}" class="btn btn-sm btn-outline-primary">
                        View Full Report
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <i class="ti ti-shield-check text-success" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0">{{ $security['security_events'] }}</h4>
                                <p class="text-muted mb-0">Security Events</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <i class="ti ti-lock-off text-warning" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0">{{ $security['failed_logins'] }}</h4>
                                <p class="text-muted mb-0">Failed Logins</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <i class="ti ti-alert-triangle text-danger" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0">{{ $security['suspicious_activities'] }}</h4>
                                <p class="text-muted mb-0">Suspicious Activities</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="ti ti-file-text text-info" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0">{{ $security['audit_logs'] }}</h4>
                                <p class="text-muted mb-0">Audit Logs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('files.index') }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-file-upload me-2"></i> Upload File
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('files.index') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-folder-plus me-2"></i> New Folder
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('search.index') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-search me-2"></i> Search Files
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-file-analytics me-2"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activity Trend Chart
    const activityTrendData = @json($activity['daily_trend']);
    const activityDates = Object.keys(activityTrendData);
    const activityCounts = Object.values(activityTrendData);

    const activityTrendOptions = {
        series: [{
            name: 'Activities',
            data: activityCounts
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: activityDates,
            labels: {
                rotate: -45
            }
        },
        colors: ['#696cff'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.1,
            }
        },
        tooltip: {
            x: {
                format: 'dd MMM'
            }
        }
    };

    const activityTrendChart = new ApexCharts(
        document.querySelector("#activityTrendChart"),
        activityTrendOptions
    );
    activityTrendChart.render();

    // Activity Distribution Chart
    const activityDistData = @json($activity['by_type']);
    const activityLabels = Object.keys(activityDistData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
    const activityValues = Object.values(activityDistData);

    const activityDistOptions = {
        series: activityValues,
        chart: {
            type: 'donut',
            height: 250
        },
        labels: activityLabels,
        colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d'],
        legend: {
            show: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        value: {
                            fontSize: '20px',
                            fontWeight: 600
                        },
                        total: {
                            show: true,
                            fontSize: '14px',
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        }
    };

    const activityDistChart = new ApexCharts(
        document.querySelector("#activityDistributionChart"),
        activityDistOptions
    );
    activityDistChart.render();

    // Storage Distribution Chart
    const storageData = @json($storage['by_type']);
    const storageLabels = storageData.map(item => {
        const type = item.mime_type || 'Unknown';
        return type.split('/')[1] || type;
    });
    const storageValues = storageData.map(item => Math.round(item.total_size / 1048576)); // Convert to MB

    const storageDistOptions = {
        series: [{
            name: 'Storage (MB)',
            data: storageValues
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: storageLabels
        },
        colors: ['#696cff']
    };

    const storageDistChart = new ApexCharts(
        document.querySelector("#storageDistributionChart"),
        storageDistOptions
    );
    storageDistChart.render();

    // Period Filter
    document.querySelectorAll('.period-filter').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.period-filter').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.getAttribute('data-period');
            window.location.href = `{{ route('dashboard') }}?period=${period}`;
        });
    });
});
</script>
@endpush
