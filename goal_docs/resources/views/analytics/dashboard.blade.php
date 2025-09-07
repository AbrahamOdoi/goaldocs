@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Analytics Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Analytics</li>
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
                            <span class="fw-semibold d-block mb-1">Files Uploaded Today</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="filesUploadedToday">{{ $analytics['quick_stats']['files_uploaded_today'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-upload ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Activities Today</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="activitiesToday">{{ $analytics['quick_stats']['activities_today'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
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
                            <span class="fw-semibold d-block mb-1">Batch Jobs Today</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="batchJobsToday">{{ $analytics['quick_stats']['batch_jobs_today'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
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
                            <span class="fw-semibold d-block mb-1">Storage Used Today</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="storageUsedToday">{{ number_format($analytics['quick_stats']['storage_used_today'] / 1024 / 1024, 1) }} MB</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-database ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">System Health</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshSystemHealth()">
                        <i class="ti ti-refresh ti-xs"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $analytics['system_health']['database_connections'] === 'Healthy' ? 'success' : 'danger' }}">
                                        <i class="ti ti-database ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Database</h6>
                                    <small class="text-muted">{{ $analytics['system_health']['database_connections'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $analytics['system_health']['cache_status'] === 'Healthy' ? 'success' : 'danger' }}">
                                        <i class="ti ti-cache ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Cache</h6>
                                    <small class="text-muted">{{ $analytics['system_health']['cache_status'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti ti-hard-drive ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Storage</h6>
                                    <small class="text-muted">{{ $analytics['system_health']['storage_available'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-users ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Active Users</h6>
                                    <small class="text-muted">{{ $analytics['system_health']['active_users'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="ti ti-loader ti-xs"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Processing Queue</h6>
                                    <small class="text-muted">{{ $analytics['system_health']['processing_queue'] }} jobs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Sections -->
    <div class="row g-4">
        <!-- Document Usage Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Document Usage</h5>
                    <a href="{{ route('files.analytics.document-usage') }}" class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['document_usage']['total_activities'] }}</h4>
                                <small class="text-muted">Total Activities</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['document_usage']['file_views'] }}</h4>
                                <small class="text-muted">Views</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-info">{{ $analytics['document_usage']['file_downloads'] }}</h4>
                                <small class="text-muted">Downloads</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <h4 class="mb-0 text-warning">{{ $analytics['document_usage']['file_uploads'] }}</h4>
                            <small class="text-muted">Uploads</small>
                        </div>
                    </div>
                    <div id="documentUsageChart" style="height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- Processing Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Processing Performance</h5>
                    <a href="{{ route('files.analytics.processing') }}" class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['processing']['ocr']['total_processed'] }}</h4>
                                <small class="text-muted">OCR Jobs</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['processing']['conversion']['total_conversions'] }}</h4>
                                <small class="text-muted">Conversions</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-info">{{ $analytics['processing']['batch_processing']['total_jobs'] }}</h4>
                                <small class="text-muted">Batch Jobs</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <h4 class="mb-0 text-warning">{{ $analytics['processing']['text_extraction']['total_extractions'] }}</h4>
                            <small class="text-muted">Extractions</small>
                        </div>
                    </div>
                    <div id="processingChart" style="height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- Storage Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Storage Usage</h5>
                    <a href="{{ route('files.analytics.storage') }}" class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['storage']['formatted_storage'] }}</h4>
                                <small class="text-muted">Total Storage</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['storage']['total_files'] }}</h4>
                                <small class="text-muted">Total Files</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ number_format($analytics['storage']['average_file_size'] / 1024 / 1024, 1) }} MB</h4>
                            <small class="text-muted">Avg File Size</small>
                        </div>
                    </div>
                    <div id="storageChart" style="height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- User Activity Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">User Activity</h5>
                    <a href="{{ route('files.analytics.user-activity') }}" class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['user_activity']['total_activities'] }}</h4>
                                <small class="text-muted">Total Activities</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['user_activity']['active_users'] }}</h4>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ count($analytics['user_activity']['activity_by_type']) }}</h4>
                            <small class="text-muted">Activity Types</small>
                        </div>
                    </div>
                    <div id="userActivityChart" style="height: 200px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>File</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['recent_activity'] as $activity)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ strtoupper(substr($activity['user_name'], 0, 1)) }}
                                                </span>
                                            </div>
                                            <span>{{ $activity['user_name'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $activity['activity_type'] === 'upload' ? 'success' : ($activity['activity_type'] === 'download' ? 'info' : 'primary') }}">
                                            {{ ucfirst($activity['activity_type']) }}
                                        </span>
                                    </td>
                                    <td>{{ $activity['file_name'] }}</td>
                                    <td>
                                        <small class="text-muted">{{ $activity['created_at'] }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No recent activity
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
});

// Initialize all charts
function initializeCharts() {
    initializeDocumentUsageChart();
    initializeProcessingChart();
    initializeStorageChart();
    initializeUserActivityChart();
}

// Document Usage Chart
function initializeDocumentUsageChart() {
    const options = {
        series: [{
            name: 'Activities',
            data: Object.values(@json($analytics['document_usage']['daily_activity']))
        }],
        chart: {
            type: 'area',
            height: 200,
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
            categories: Object.keys(@json($analytics['document_usage']['daily_activity'])),
            labels: {
                show: false
            }
        },
        yaxis: {
            labels: {
                show: false
            }
        },
        grid: {
            show: false
        },
        tooltip: {
            x: {
                format: 'dd MMM'
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#documentUsageChart"), options);
    chart.render();
}

// Processing Chart
function initializeProcessingChart() {
    const options = {
        series: [{
            name: 'OCR',
            data: [@json($analytics['processing']['ocr']['total_processed'])]
        }, {
            name: 'Conversions',
            data: [@json($analytics['processing']['conversion']['total_conversions'])]
        }, {
            name: 'Batch Jobs',
            data: [@json($analytics['processing']['batch_processing']['total_jobs'])]
        }, {
            name: 'Extractions',
            data: [@json($analytics['processing']['text_extraction']['total_extractions'])]
        }],
        chart: {
            type: 'bar',
            height: 200,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
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
        colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43'],
        xaxis: {
            categories: ['Processing'],
            labels: {
                show: false
            }
        },
        yaxis: {
            labels: {
                show: false
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " operations"
                }
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#processingChart"), options);
    chart.render();
}

// Storage Chart
function initializeStorageChart() {
    const options = {
        series: [@json($analytics['storage']['total_storage_used'])],
        chart: {
            type: 'radialBar',
            height: 200,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                hollow: {
                    margin: 15,
                    size: '70%',
                },
                track: {
                    background: '#e7e7e7',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        fontSize: '30px',
                        show: true,
                        formatter: function (val) {
                            return formatBytes(val);
                        },
                        color: '#111'
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#7367F0'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['Storage Used']
    };

    const chart = new ApexCharts(document.querySelector("#storageChart"), options);
    chart.render();
}

// User Activity Chart
function initializeUserActivityChart() {
    const activityTypes = @json(array_keys($analytics['user_activity']['activity_by_type']));
    const activityCounts = @json(array_values($analytics['user_activity']['activity_by_type']));
    
    const options = {
        series: activityCounts,
        chart: {
            type: 'donut',
            height: 200,
            toolbar: {
                show: false
            }
        },
        labels: activityTypes,
        colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43', '#EA5455'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%'
                }
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#userActivityChart"), options);
    chart.render();
}

// Real-time updates
function startRealTimeUpdates() {
    setInterval(function() {
        updateQuickStats();
    }, 30000); // Update every 30 seconds
}

// Update quick stats
function updateQuickStats() {
    fetch('{{ route("analytics.api.summary") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('filesUploadedToday').textContent = data.data.quick_stats.files_uploaded_today;
                document.getElementById('activitiesToday').textContent = data.data.quick_stats.activities_today;
                document.getElementById('batchJobsToday').textContent = data.data.quick_stats.batch_jobs_today;
                document.getElementById('storageUsedToday').textContent = formatBytes(data.data.quick_stats.storage_used_today);
            }
        })
        .catch(error => {
            console.error('Failed to update quick stats:', error);
        });
}

// Refresh system health
function refreshSystemHealth() {
    fetch('{{ route("analytics.api.realtime") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update system health indicators
                console.log('System health refreshed');
            }
        })
        .catch(error => {
            console.error('Failed to refresh system health:', error);
        });
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