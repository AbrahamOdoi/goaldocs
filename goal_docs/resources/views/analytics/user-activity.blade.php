@extends('layouts.app')

@section('title', 'User Activity Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    User Activity Analytics
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('analytics.dashboard') }}">Analytics</a>
                    </li>
                    <li class="breadcrumb-item active">User Activity</li>
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

    <!-- User Activity Overview Stats -->
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
                            <span class="fw-semibold d-block mb-1">Active Users</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="activeUsers">{{ $analytics['active_users'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-users ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Activity Types</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="activityTypes">{{ count($analytics['activity_by_type']) }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-category ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Peak Hour</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="peakHour">{{ array_search(max($analytics['peak_activity_hours']), $analytics['peak_activity_hours']) }}:00</h4>
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

    <!-- Activity Charts -->
    <div class="row g-4 mb-4">
        <!-- Activity Trends Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Trends Over Time</h5>
                </div>
                <div class="card-body">
                    <div id="activityTrendsChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Peak Activity Hours -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Peak Activity Hours</h5>
                </div>
                <div class="card-body">
                    <div id="peakActivityChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Distribution and User Productivity -->
    <div class="row g-4">
        <!-- Activity Distribution -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="activityDistributionChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- User Productivity -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Productivity Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ number_format($analytics['user_productivity']['uploads_per_user'], 1) }}</h4>
                                <small class="text-muted">Uploads/User</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ number_format($analytics['user_productivity']['downloads_per_user'], 1) }}</h4>
                                <small class="text-muted">Downloads/User</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ number_format($analytics['user_productivity']['views_per_user'], 1) }}</h4>
                            <small class="text-muted">Views/User</small>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h6>Productivity Insights</h6>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle ti-sm me-2"></i>
                            <small>Users are most active during peak hours. Consider scheduling important activities during these times.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collaboration Metrics -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Collaboration Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['collaboration_metrics']['total_collaborations'] }}</h4>
                                <small class="text-muted">Total Collaborations</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['collaboration_metrics']['comments'] }}</h4>
                                <small class="text-muted">Comments</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-info">{{ $analytics['collaboration_metrics']['annotations'] }}</h4>
                                <small class="text-muted">Annotations</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="mb-0 text-warning">{{ $analytics['collaboration_metrics']['shares'] }}</h4>
                            <small class="text-muted">Shares</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <div class="d-flex">
                                    <i class="ti ti-check-circle ti-sm me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">High Collaboration</h6>
                                        <p class="mb-0">Users are actively collaborating through comments and annotations, indicating good team engagement.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <i class="ti ti-lightbulb ti-sm me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Engagement Opportunities</h6>
                                        <p class="mb-0">Consider implementing more collaboration features to further increase user engagement.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Details Table -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Type Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity Type</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['activity_by_type'] as $type => $count)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-label-primary me-2">{{ ucfirst($type) }}</span>
                                            <span>{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success">{{ $count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $analytics['total_activities'] > 0 ? ($count / $analytics['total_activities']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                    </td>
                                    <td>
                                        @php
                                            $trend = $count > 10 ? 'up' : ($count > 5 ? 'stable' : 'down');
                                            $trendColor = $trend === 'up' ? 'success' : ($trend === 'stable' ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-label-{{ $trendColor }}">
                                            <i class="ti ti-trending-{{ $trend }} ti-xs"></i>
                                            {{ ucfirst($trend) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No activity data available
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
let activityTrendsChart, peakActivityChart, activityDistributionChart;

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
    initializeActivityTrendsChart();
    initializePeakActivityChart();
    initializeActivityDistributionChart();
}

// Activity Trends Chart
function initializeActivityTrendsChart() {
    const trendsData = @json($analytics['activity_trends']);
    const categories = Object.keys(trendsData);
    const data = Object.values(trendsData);
    
    const options = {
        series: [{
            name: 'Activities',
            data: data
        }],
        chart: {
            type: 'line',
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

    activityTrendsChart = new ApexCharts(document.querySelector("#activityTrendsChart"), options);
    activityTrendsChart.render();
}

// Peak Activity Hours Chart
function initializePeakActivityChart() {
    const peakData = @json($analytics['peak_activity_hours']);
    const hours = Array.from({length: 24}, (_, i) => i);
    const data = hours.map(hour => peakData[hour] || 0);
    
    const options = {
        series: [{
            name: 'Activities',
            data: data
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '70%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#28C76F'],
        xaxis: {
            categories: hours.map(hour => `${hour}:00`),
            labels: {
                rotate: -45,
                style: {
                    fontSize: '10px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Number of Activities'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " activities"
                }
            }
        }
    };

    peakActivityChart = new ApexCharts(document.querySelector("#peakActivityChart"), options);
    peakActivityChart.render();
}

// Activity Distribution Chart
function initializeActivityDistributionChart() {
    const activityData = @json($analytics['activity_by_type']);
    const labels = Object.keys(activityData);
    const data = Object.values(activityData);
    
    const options = {
        series: data,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: labels.map(label => label.replace('_', ' ').toUpperCase()),
        colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43', '#EA5455', '#9C27B0'],
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
        }
    };

    activityDistributionChart = new ApexCharts(document.querySelector("#activityDistributionChart"), options);
    activityDistributionChart.render();
}

// Update analytics data
function updateAnalytics() {
    fetch(`{{ route('analytics.api.user-activity') }}?period=${currentPeriod}`)
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
    document.getElementById('activeUsers').textContent = analytics.active_users;
    document.getElementById('activityTypes').textContent = Object.keys(analytics.activity_by_type).length;
    document.getElementById('peakHour').textContent = Object.keys(analytics.peak_activity_hours).find(key => 
        analytics.peak_activity_hours[key] === Math.max(...Object.values(analytics.peak_activity_hours))
    ) + ':00';
}

// Update charts
function updateCharts(analytics) {
    // Update activity trends chart
    const trendsData = analytics.activity_trends;
    const categories = Object.keys(trendsData);
    const data = Object.values(trendsData);
    
    activityTrendsChart.updateSeries([{
        name: 'Activities',
        data: data
    }]);
    activityTrendsChart.updateOptions({
        xaxis: {
            categories: categories
        }
    });
    
    // Update peak activity chart
    const peakData = analytics.peak_activity_hours;
    const hours = Array.from({length: 24}, (_, i) => i);
    const peakChartData = hours.map(hour => peakData[hour] || 0);
    
    peakActivityChart.updateSeries([{
        name: 'Activities',
        data: peakChartData
    }]);
    
    // Update activity distribution chart
    const activityData = analytics.activity_by_type;
    const labels = Object.keys(activityData);
    const chartData = Object.values(activityData);
    
    activityDistributionChart.updateSeries(chartData);
    activityDistributionChart.updateOptions({
        labels: labels.map(label => label.replace('_', ' ').toUpperCase())
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
            section: 'user_activity',
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
</script>
@endpush
