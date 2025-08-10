@extends('layouts.app')

@section('title', 'Document Usage Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Document Usage Analytics
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('analytics.dashboard') }}">Analytics</a>
                    </li>
                    <li class="breadcrumb-item active">Document Usage</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Select Time Period</h5>
                        </div>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="period" id="period7d" value="7d" checked>
                            <label class="btn btn-outline-primary" for="period7d">7 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period30d" value="30d">
                            <label class="btn btn-outline-primary" for="period30d">30 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period90d" value="90d">
                            <label class="btn btn-outline-primary" for="period90d">90 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period1y" value="1y">
                            <label class="btn btn-outline-primary" for="period1y">1 Year</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Activities</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="totalActivities">{{ $analytics['total_activities'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-activity ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">File Views</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="fileViews">{{ $analytics['file_views'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-eye ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">File Downloads</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="fileDownloads">{{ $analytics['file_downloads'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-download ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">File Uploads</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="fileUploads">{{ $analytics['file_uploads'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-upload ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Daily Activity Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daily Activity Trends</h5>
                </div>
                <div class="card-body">
                    <div id="dailyActivityChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Activity Distribution -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="activityDistributionChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Files and User Engagement -->
    <div class="row g-4">
        <!-- Top Files -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Most Active Files</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Activity Count</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_files'] as $file)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-file-text ti-sm me-2 text-muted"></i>
                                            <span>{{ Str::limit($file['file_name'], 30) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $file['activity_count'] }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ formatBytes($file['file_size']) }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No file activity data available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Engagement -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Engagement</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity Count</th>
                                    <th>Engagement Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['user_engagement'] as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ strtoupper(substr($user['user_name'], 0, 1)) }}
                                                </span>
                                            </div>
                                            <span>{{ $user['user_name'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success">{{ $user['activity_count'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $level = $user['activity_count'] > 50 ? 'High' : ($user['activity_count'] > 20 ? 'Medium' : 'Low');
                                            $color = $user['activity_count'] > 50 ? 'success' : ($user['activity_count'] > 20 ? 'warning' : 'secondary');
                                        @endphp
                                        <span class="badge bg-label-{{ $color }}">{{ $level }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No user engagement data available
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

    <!-- Export Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Export Data</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="exportData('pdf')">
                            <i class="ti ti-file-text ti-xs me-1"></i>Export as PDF
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="exportData('excel')">
                            <i class="ti ti-file-spreadsheet ti-xs me-1"></i>Export as Excel
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="exportData('csv')">
                            <i class="ti ti-file-text ti-xs me-1"></i>Export as CSV
                        </button>
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
let currentPeriod = '7d';
let dailyActivityChart, activityDistributionChart;

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupPeriodSelector();
});

// Setup period selector
function setupPeriodSelector() {
    const periodInputs = document.querySelectorAll('input[name="period"]');
    periodInputs.forEach(input => {
        input.addEventListener('change', function() {
            currentPeriod = this.value;
            updateAnalytics();
        });
    });
}

// Initialize charts
function initializeCharts() {
    initializeDailyActivityChart();
    initializeActivityDistributionChart();
}

// Daily Activity Chart
function initializeDailyActivityChart() {
    const dailyData = @json($analytics['daily_activity']);
    const categories = Object.keys(dailyData);
    const data = Object.values(dailyData);
    
    const options = {
        series: [{
            name: 'Activities',
            data: data
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: true
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#7367F0'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: categories,
            labels: {
                rotate: -45,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Number of Activities'
            }
        },
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            }
        },
        tooltip: {
            x: {
                format: 'dd MMM yyyy'
            }
        }
    };

    dailyActivityChart = new ApexCharts(document.querySelector("#dailyActivityChart"), options);
    dailyActivityChart.render();
}

// Activity Distribution Chart
function initializeActivityDistributionChart() {
    const activityData = [
        { name: 'Views', value: @json($analytics['file_views']) },
        { name: 'Downloads', value: @json($analytics['file_downloads']) },
        { name: 'Uploads', value: @json($analytics['file_uploads']) },
        { name: 'Shares', value: @json($analytics['file_shares']) }
    ];
    
    const options = {
        series: activityData.map(item => item.value),
        chart: {
            type: 'donut',
            height: 350
        },
        labels: activityData.map(item => item.name),
        colors: ['#28C76F', '#00CFE8', '#FF9F43', '#EA5455'],
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return opts.w.globals.seriesTotals.reduce((a, b) => a + b, 0) > 0 
                    ? Math.round((val / opts.w.globals.seriesTotals.reduce((a, b) => a + b, 0)) * 100) + '%'
                    : '0%';
            }
        },
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%'
                }
            }
        }
    };

    activityDistributionChart = new ApexCharts(document.querySelector("#activityDistributionChart"), options);
    activityDistributionChart.render();
}

// Update analytics data
function updateAnalytics() {
    fetch(`{{ route('analytics.api.document-usage') }}?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStats(data.data);
                updateCharts(data.data);
            }
        })
        .catch(error => {
            console.error('Failed to update analytics:', error);
        });
}

// Update statistics
function updateStats(analytics) {
    document.getElementById('totalActivities').textContent = analytics.total_activities;
    document.getElementById('fileViews').textContent = analytics.file_views;
    document.getElementById('fileDownloads').textContent = analytics.file_downloads;
    document.getElementById('fileUploads').textContent = analytics.file_uploads;
}

// Update charts
function updateCharts(analytics) {
    // Update daily activity chart
    const dailyData = analytics.daily_activity;
    const categories = Object.keys(dailyData);
    const data = Object.values(dailyData);
    
    dailyActivityChart.updateSeries([{
        name: 'Activities',
        data: data
    }]);
    dailyActivityChart.updateOptions({
        xaxis: {
            categories: categories
        }
    });
    
    // Update activity distribution chart
    const activityData = [
        { name: 'Views', value: analytics.file_views },
        { name: 'Downloads', value: analytics.file_downloads },
        { name: 'Uploads', value: analytics.file_uploads },
        { name: 'Shares', value: analytics.file_shares }
    ];
    
    activityDistributionChart.updateSeries(activityData.map(item => item.value));
    activityDistributionChart.updateOptions({
        labels: activityData.map(item => item.name)
    });
}

// Export data
function exportData(format) {
    fetch('{{ route("analytics.api.export") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            section: 'document_usage',
            format: format,
            period: currentPeriod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Data exported successfully as ${format.toUpperCase()}`);
        } else {
            showAlert('error', data.error || 'Export failed');
        }
    })
    .catch(error => {
        console.error('Export failed:', error);
        showAlert('error', 'Export failed');
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

// Format bytes helper function
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}
</script>
@endpush
