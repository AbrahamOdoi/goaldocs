@extends('layouts.app')


@section('title', 'Dashboard - GoalDocs')


@push('styles')
<style>
/* Enhanced Professional Styling */
.card {
border-radius: 16px;
box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}
.card:hover {
transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    border-color: rgba(105, 108, 255, 0.2);
}
.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
border-bottom: 1px solid #e0e0e0;
font-weight: 600;
font-size: 1.05rem;
    border-radius: 16px 16px 0 0;
    padding: 1.25rem 1.5rem;
}
.card-title {
font-size: 1.125rem;
font-weight: 600;
color: #343a40;
    margin-bottom: 0.25rem;
}
.badge {
font-size: 0.75rem;
padding: 0.4em 0.7em;
font-weight: 500;
border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.avatar {
border-radius: 12px;
padding: 0.6rem;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.btn-group .btn {
border-radius: 8px;
font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.btn-light.active {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
border: 1px solid #696cff;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    transform: translateY(-1px);
}
.text-muted {
color: #6c757d !important;
}
.section-divider {
font-weight: 600;
margin: 2rem 0 1rem;
color: #555;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0.5rem;
    position: relative;
}
.section-divider::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    border-radius: 1px;
}

/* Professional Chart Containers */
.chart-container {
    position: relative;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Enhanced Metric Cards */
.metric-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
}
.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* Professional Progress Bars */
.progress {
    height: 8px;
    border-radius: 10px;
    background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}
.progress-bar {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(105, 108, 255, 0.3);
}

/* Enhanced Activity Items */
.activity-item {
    padding: 1rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    margin-bottom: 0.75rem;
}
.activity-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: rgba(105, 108, 255, 0.2);
}

/* Professional Dropdowns */
.dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.05);
    padding: 0.5rem;
}
.dropdown-item {
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s ease;
}
.dropdown-item:hover {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    color: white;
    transform: translateX(4px);
}

/* Enhanced Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(105, 108, 255, 0.3);
    position: relative;
    overflow: hidden;
}
.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

/* Professional Table Styling */
.table {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    font-weight: 600;
    color: #495057;
    padding: 1rem;
}
.table tbody tr {
    transition: all 0.2s ease;
}
.table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    transform: scale(1.01);
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
    .metric-card {
        padding: 1rem;
    }
    .welcome-banner {
        text-align: center;
    }
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}

/* Loading States */
.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a5fcf 0%, #696cff 100%);
}

/* Table Sorting Indicators */
th.sort-asc::after {
    content: ' ↑';
    color: #696cff;
    font-weight: bold;
}
th.sort-desc::after {
    content: ' ↓';
    color: #696cff;
    font-weight: bold;
}
th[style*="cursor: pointer"]:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.2s ease;
}

/* Enhanced Table Styling */
#recentDocumentsTable {
    border-collapse: separate;
    border-spacing: 0;
}
#recentDocumentsTable th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #e0e0e0;
}
#recentDocumentsTable tbody tr {
    transition: all 0.2s ease;
}
#recentDocumentsTable tbody tr:hover {
    background: linear-gradient(135deg, rgba(105, 108, 255, 0.05) 0%, rgba(105, 108, 255, 0.02) 100%);
    transform: scale(1.001);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Loading Animation for Refresh Button */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.ti-loader-2 {
    animation: spin 1s linear infinite;
}

/* Enhanced Search Input */
.form-control:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

/* Export Button Animation */
.btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 199, 111, 0.3);
}

/* Filter Dropdown Enhancements */
.dropdown-item.active {
    background: linear-gradient(135deg, #696cff 0%, #5a5fcf 100%);
    color: white;
}
</style>
@endpush


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">


<!-- Enhanced Welcome Banner -->
<div class="row mb-4">
<div class="col-12">
<div class="card welcome-banner text-white shadow-sm border-0">
<div class="card-body py-4 px-4">
<div class="row align-items-center">
<div class="col-md-8">
<h2 class="text-white fw-bold mb-2">Welcome back, {{ $user->name }}! 👋</h2>
<p class="text-white-50 fs-6 mb-0">Here's what's happening with your documents today</p>
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


<div class="section-divider">Your Insights</div>


    <!-- Enhanced Metric Cards -->
    <div class="row mb-4">
        <!-- Total Files -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100 shadow-sm">
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
            <div class="card metric-card h-100 shadow-sm">
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
            <div class="card metric-card h-100 shadow-sm">
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
            <div class="card metric-card h-100 shadow-sm">
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

    <!-- Professional Charts Row -->
    <div class="row mb-4">
        <!-- Revenue & Activity Report -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Document Activity & Revenue</h5>
                        <small class="text-muted">Monthly overview of document activities and generated value</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('files.analytics.dashboard') }}">View Details</a></li>
                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                            <li><a class="dropdown-item" href="#">Print Report</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenueActivityChart"></div>
                </div>
            </div>
        </div>

        <!-- Document Performance Overview -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Document Performance</h5>
                    <small class="text-muted">Distribution by activity type</small>
                </div>
                <div class="card-body">
                    <div id="documentPerformanceChart"></div>
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

    <!-- Advanced Analytics Row -->
    <div class="row mb-4">
        <!-- Document Processing Timeline -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Processing Timeline</h5>
                    <small class="text-muted">Document processing efficiency over time</small>
                </div>
                <div class="card-body">
                    <div id="processingTimelineChart"></div>
                </div>
            </div>
        </div>

        <!-- User Engagement Metrics -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Engagement</h5>
                    <small class="text-muted">Weekly user activity patterns</small>
                </div>
                <div class="card-body">
                    <div id="userEngagementChart"></div>
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

    <!-- Professional Data Tables Section -->
    <div class="row mb-4">
        <!-- Recent Documents Table -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Recent Documents</h5>
                        <small class="text-muted">Latest document activities and status</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-filter="all">All Documents</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="recent">Recent (7 days)</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="popular">Most Popular</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="large">Large Files</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="recentDocumentsTable">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Last Modified</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities->take(10) as $activity)
                                <tr class="activity-item">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-label-primary">
                                                <i class="ti ti-file"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $activity->file->name ?? 'Unknown File' }}</h6>
                                                <small class="text-muted">{{ $activity->user->name ?? 'Unknown User' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            {{ $activity->file->mime_type ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $activity->file ? number_format($activity->file->size / 1024, 1) . ' KB' : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ ['upload' => 'success', 'download' => 'info', 'view' => 'primary', 'share' => 'warning'][$activity->activity_type] ?? 'secondary' }}">
                                            {{ ucfirst($activity->activity_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#"><i class="ti ti-eye me-2"></i>View</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="ti ti-download me-2"></i>Download</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="ti ti-share me-2"></i>Share</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="ti ti-mood-empty text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">No recent documents found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Performance Metrics -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Performance</h5>
                    <small class="text-muted">Real-time system metrics</small>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">CPU Usage</span>
                            <span class="fw-bold">45%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Memory Usage</span>
                            <span class="fw-bold">72%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 72%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Disk Space</span>
                            <span class="fw-bold">38%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 38%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Network I/O</span>
                            <span class="fw-bold">23%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 23%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-refresh me-1"></i> Refresh Metrics
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for ApexCharts to be fully loaded
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts library not loaded');
        return;
    }

    // Professional Revenue & Activity Chart (Combined Bar + Line)
    const revenueActivityOptions = {
        series: [{
            name: 'Document Activities',
            type: 'column',
            data: [38, 45, 33, 38, 32, 50, 48, 40, 42, 37]
        }, {
            name: 'Revenue Generated',
            type: 'line',
            data: [23, 28, 23, 32, 28, 44, 32, 38, 26, 34]
        }],
        chart: {
            height: 350,
            type: 'line',
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: [0, 3],
            lineCap: 'round'
        },
        colors: ['#696cff', '#71dd37'],
        fill: {
            opacity: [1, 1]
        },
        markers: {
            size: 4,
            colors: ['#ffffff'],
            strokeColors: '#71dd37',
            hover: {
                size: 6
            },
            borderRadius: 4
        },
        legend: {
            show: true,
            position: 'bottom',
            markers: {
                width: 8,
                height: 8,
                offsetX: -3
            },
            height: 40,
            offsetY: 10,
            itemMargin: {
                horizontal: 10,
                vertical: 0
            },
            fontSize: '15px',
            fontFamily: 'Public Sans',
            fontWeight: 400
        },
        grid: {
            strokeDashArray: 8
        },
        plotOptions: {
            bar: {
                columnWidth: '30%',
                startingShape: 'rounded',
                endingShape: 'rounded',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            tickAmount: 10,
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '13px',
                    fontFamily: 'Public Sans',
                    fontWeight: 400
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            tickAmount: 4,
            min: 10,
            max: 50,
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '13px',
                    fontFamily: 'Public Sans',
                    fontWeight: 400
                },
                formatter: function (val) {
                    return val + '%'
                }
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function (val, { seriesIndex }) {
                    return seriesIndex === 0 ? val + ' activities' : '$' + val + 'k'
                }
            }
        }
    };

    try {
        const revenueActivityChart = new ApexCharts(
            document.querySelector("#revenueActivityChart"),
            revenueActivityOptions
        );
        revenueActivityChart.render();
    } catch (error) {
        console.error('Error rendering revenue activity chart:', error);
        document.querySelector("#revenueActivityChart").innerHTML = '<div class="text-center p-4"><p class="text-muted">Chart temporarily unavailable</p></div>';
    }

    // Document Performance Chart (Professional Donut)
    const activityDistData = @json($activity['by_type']);
    const activityLabels = Object.keys(activityDistData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
    const activityValues = Object.values(activityDistData);

    const documentPerformanceOptions = {
        chart: {
            height: 280,
            type: 'donut',
            parentHeightOffset: 0
        },
        labels: activityLabels,
        series: activityValues,
        colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d'],
        stroke: {
            width: 0
        },
        dataLabels: {
            enabled: false,
            formatter: function (val, opts) {
                return parseInt(val) + '%'
            }
        },
        legend: {
            show: true,
            position: 'bottom',
            offsetY: 10,
            markers: {
                width: 8,
                height: 8,
                offsetX: -3
            },
            itemMargin: {
                horizontal: 15,
                vertical: 5
            },
            fontSize: '13px',
            fontFamily: 'Public Sans',
            fontWeight: 400,
            labels: {
                colors: '#6c757d',
                useSeriesColors: false
            }
        },
        tooltip: {
            theme: false
        },
        grid: {
            padding: {
                top: 15
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'none'
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '77%',
                    labels: {
                        show: true,
                        value: {
                            fontSize: '26px',
                            fontFamily: 'Public Sans',
                            color: '#6c757d',
                            fontWeight: 500,
                            offsetY: -30,
                            formatter: function (val) {
                                return parseInt(val) + '%'
                            }
                        },
                        name: {
                            offsetY: 20,
                            fontFamily: 'Public Sans'
                        },
                        total: {
                            show: true,
                            fontSize: '.75rem',
                            label: 'Total Activities',
                            color: '#6c757d',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        }
    };

    try {
        const documentPerformanceChart = new ApexCharts(
            document.querySelector("#documentPerformanceChart"),
            documentPerformanceOptions
        );
        documentPerformanceChart.render();
    } catch (error) {
        console.error('Error rendering document performance chart:', error);
        document.querySelector("#documentPerformanceChart").innerHTML = '<div class="text-center p-4"><p class="text-muted">Chart temporarily unavailable</p></div>';
    }

    // Processing Timeline Chart (Area Chart)
    const processingTimelineOptions = {
        series: [{
            name: 'Processing Time (hours)',
            data: [2.5, 3.2, 2.8, 4.1, 3.5, 2.9, 3.8, 2.7, 3.1, 2.6, 3.4, 2.9]
        }],
        chart: {
            height: 300,
            type: 'area',
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#696cff'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return val + 'h'
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + ' hours'
                }
            }
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 4
        }
    };

    try {
        const processingTimelineChart = new ApexCharts(
            document.querySelector("#processingTimelineChart"),
            processingTimelineOptions
        );
        processingTimelineChart.render();
    } catch (error) {
        console.error('Error rendering processing timeline chart:', error);
        document.querySelector("#processingTimelineChart").innerHTML = '<div class="text-center p-4"><p class="text-muted">Chart temporarily unavailable</p></div>';
    }

    // User Engagement Chart (Horizontal Bar)
    const userEngagementOptions = {
        chart: {
            height: 300,
            type: 'bar',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '70%',
                distributed: true,
                startingShape: 'rounded',
                borderRadius: 7
            }
        },
        grid: {
            strokeDashArray: 10,
            borderColor: '#e0e0e0',
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: false
                }
            },
            padding: {
                top: -35,
                bottom: -12
            }
        },
        colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#ff3e1d'],
        dataLabels: {
            enabled: true,
            style: {
                colors: ['#fff'],
                fontWeight: 200,
                fontSize: '13px',
                fontFamily: 'Public Sans'
            },
            formatter: function (val, opts) {
                return userEngagementOptions.labels[opts.dataPointIndex]
            },
            offsetX: 0,
            dropShadow: {
                enabled: false
            }
        },
        labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        series: [{
            data: [85, 92, 78, 88, 95, 45, 35]
        }],
        xaxis: {
            categories: ['100', '90', '80', '70', '60', '50', '40'],
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '13px'
                },
                formatter: function (val) {
                    return val + '%'
                }
            }
        },
        yaxis: {
            max: 100,
            labels: {
                style: {
                    colors: ['#6c757d'],
                    fontFamily: 'Public Sans',
                    fontSize: '13px'
                }
            }
        },
        tooltip: {
            enabled: true,
            style: {
                fontSize: '12px'
            },
            onDatasetHover: {
                highlightDataSeries: false
            },
            custom: function({series, seriesIndex, dataPointIndex}) {
                return '<div class="px-3 py-2"><span>' + series[seriesIndex][dataPointIndex] + '%</span></div>'
            }
        },
        legend: {
            show: false
        }
    };

    try {
        const userEngagementChart = new ApexCharts(
            document.querySelector("#userEngagementChart"),
            userEngagementOptions
        );
        userEngagementChart.render();
    } catch (error) {
        console.error('Error rendering user engagement chart:', error);
        document.querySelector("#userEngagementChart").innerHTML = '<div class="text-center p-4"><p class="text-muted">Chart temporarily unavailable</p></div>';
    }

    // Storage Distribution Chart (Enhanced)
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
                columnWidth: '60%',
                startingShape: 'rounded',
                endingShape: 'rounded'
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: storageLabels,
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#6c757d',
                    fontSize: '12px'
                }
            }
        },
        colors: ['#696cff'],
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 4
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + ' MB'
                }
            }
        }
    };

    try {
        const storageDistChart = new ApexCharts(
            document.querySelector("#storageDistributionChart"),
            storageDistOptions
        );
        storageDistChart.render();
    } catch (error) {
        console.error('Error rendering storage distribution chart:', error);
        document.querySelector("#storageDistributionChart").innerHTML = '<div class="text-center p-4"><p class="text-muted">Chart temporarily unavailable</p></div>';
    }

    // Period Filter Enhancement
    document.querySelectorAll('.period-filter').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.period-filter').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.getAttribute('data-period');
            // Update charts based on period selection
            updateChartsForPeriod(period);
        });
    });

    // Function to update charts based on selected period
    function updateChartsForPeriod(period) {
        // This would typically make an AJAX call to get new data
        // For now, we'll just show a loading state
        console.log('Updating charts for period:', period);
        
        // You can implement AJAX calls here to fetch new data
        // and update the charts accordingly
    }

    // Interactive Data Table Functionality
    const recentDocumentsTable = document.getElementById('recentDocumentsTable');
    if (recentDocumentsTable) {
        // Add sorting functionality
        const headers = recentDocumentsTable.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index < 5) { // Exclude Actions column
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    sortTable(index);
                });
            }
        });

        // Table sorting function
        function sortTable(columnIndex) {
            const table = recentDocumentsTable;
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            const isAscending = table.getAttribute('data-sort-direction') !== 'asc';
            table.setAttribute('data-sort-direction', isAscending ? 'asc' : 'desc');
            
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();
                
                // Handle different data types
                if (columnIndex === 2) { // Size column
                    const aSize = parseFloat(aText.replace(/[^\d.]/g, ''));
                    const bSize = parseFloat(bText.replace(/[^\d.]/g, ''));
                    return isAscending ? aSize - bSize : bSize - aSize;
                } else if (columnIndex === 3) { // Date column
                    const aDate = new Date(a.cells[columnIndex].getAttribute('data-date') || aText);
                    const bDate = new Date(b.cells[columnIndex].getAttribute('data-date') || bText);
                    return isAscending ? aDate - bDate : bDate - aDate;
                } else {
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });
            
            // Clear and re-append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
            
            // Update header indicators
            headers.forEach((header, index) => {
                header.classList.remove('sort-asc', 'sort-desc');
                if (index === columnIndex) {
                    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
                }
            });
        }
    }

    // Filter functionality for the documents table
    document.querySelectorAll('[data-filter]').forEach(filterButton => {
        filterButton.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            filterDocuments(filter);
            
            // Update active filter button
            document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function filterDocuments(filter) {
        const rows = document.querySelectorAll('#recentDocumentsTable tbody tr');
        
        rows.forEach(row => {
            let show = true;
            
            switch(filter) {
                case 'recent':
                    // Show only documents from last 7 days
                    const dateText = row.cells[3].textContent;
                    if (dateText.includes('day') && parseInt(dateText) > 7) {
                        show = false;
                    }
                    break;
                case 'popular':
                    // Show documents with high activity (you can customize this logic)
                    const statusBadge = row.cells[4].querySelector('.badge');
                    if (statusBadge && !statusBadge.textContent.includes('view')) {
                        show = false;
                    }
                    break;
                case 'large':
                    // Show only large files (>1MB)
                    const sizeText = row.cells[2].textContent;
                    if (sizeText.includes('KB') || (sizeText.includes('MB') && parseFloat(sizeText) < 1)) {
                        show = false;
                    }
                    break;
                case 'all':
                default:
                    show = true;
                    break;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }

    // System Performance Metrics Refresh
    const refreshMetricsBtn = document.querySelector('button[class*="refresh"]');
    if (refreshMetricsBtn) {
        refreshMetricsBtn.addEventListener('click', function() {
            // Add loading state
            this.innerHTML = '<i class="ti ti-loader-2 me-1"></i> Refreshing...';
            this.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Update progress bars with new random values
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    const newWidth = Math.floor(Math.random() * 100);
                    bar.style.width = newWidth + '%';
                    
                    // Update the percentage text
                    const percentageSpan = bar.closest('.mb-4').querySelector('.fw-bold');
                    if (percentageSpan) {
                        percentageSpan.textContent = newWidth + '%';
                    }
                    
                    // Update color based on value
                    bar.className = 'progress-bar';
                    if (newWidth > 80) {
                        bar.classList.add('bg-danger');
                    } else if (newWidth > 60) {
                        bar.classList.add('bg-warning');
                    } else if (newWidth > 40) {
                        bar.classList.add('bg-info');
                    } else {
                        bar.classList.add('bg-success');
                    }
                });
                
                // Reset button
                this.innerHTML = '<i class="ti ti-refresh me-1"></i> Refresh Metrics';
                this.disabled = false;
            }, 1500);
        });
    }

    // Enhanced table row interactions
    document.querySelectorAll('#recentDocumentsTable tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(105, 108, 255, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Add search functionality to the table
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control form-control-sm';
    searchInput.placeholder = 'Search documents...';
    searchInput.style.marginBottom = '1rem';
    
    const tableContainer = document.querySelector('#recentDocumentsTable').closest('.card-body');
    if (tableContainer) {
        tableContainer.insertBefore(searchInput, tableContainer.firstChild);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#recentDocumentsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Add export functionality
    const exportBtn = document.createElement('button');
    exportBtn.className = 'btn btn-outline-success btn-sm';
    exportBtn.innerHTML = '<i class="ti ti-download me-1"></i> Export';
    exportBtn.style.marginLeft = '0.5rem';
    
    const filterDropdown = document.querySelector('.dropdown-toggle');
    if (filterDropdown) {
        filterDropdown.parentNode.appendChild(exportBtn);
        
        exportBtn.addEventListener('click', function() {
            // Simple CSV export functionality
            const table = document.getElementById('recentDocumentsTable');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowData = Array.from(cells).map(cell => {
                    return '"' + cell.textContent.replace(/"/g, '""') + '"';
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'recent-documents.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        });
    }
});
</script>
@endpush

