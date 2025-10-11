@extends('layouts.app')

@section('title', 'Storage Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Storage Analytics
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('analytics.dashboard') }}">Analytics</a>
                    </li>
                    <li class="breadcrumb-item active">Storage</li>
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

    <!-- Storage Overview Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Storage Used</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="totalStorage">{{ $analytics['formatted_storage'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-database ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Total Files</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="totalFiles">{{ $analytics['total_files'] }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-files ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Average File Size</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="avgFileSize">{{ number_format($analytics['average_file_size'] / 1024 / 1024, 1) }} MB</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-file-text ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Storage Growth</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="storageGrowth">+{{ count($analytics['storage_growth']) }} days</h4>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-trending-up ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Charts -->
    <div class="row g-4 mb-4">
        <!-- Storage Growth Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Storage Growth Over Time</h5>
                </div>
                <div class="card-body">
                    <div id="storageGrowthChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- File Type Distribution -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">File Type Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="fileTypeChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Details -->
    <div class="row g-4">
        <!-- Largest Files -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Largest Files</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['largest_files'] as $file)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-file-text ti-sm me-2 text-muted"></i>
                                            <span>{{ Str::limit($file['name'], 30) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $file['formatted_size'] }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $file['uploaded_at'] }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No files available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage by User Type -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Storage by User Type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User Type</th>
                                    <th>Files</th>
                                    <th>Storage Used</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['storage_by_user_type'] as $userType)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ strtoupper(substr($userType['user_type'], 0, 1)) }}
                                                </span>
                                            </div>
                                            <span>{{ ucfirst($userType['user_type']) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success">{{ $userType['file_count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $userType['formatted_size'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $analytics['total_storage_used'] > 0 ? ($userType['total_size'] / $analytics['total_storage_used']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No user type data available
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

    <!-- File Type Details -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Detailed File Type Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File Type</th>
                                    <th>Count</th>
                                    <th>Total Size</th>
                                    <th>Average Size</th>
                                    <th>Percentage of Storage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['file_type_distribution'] as $extension => $data)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-label-secondary me-2">{{ strtoupper($extension) }}</span>
                                            <span>{{ ucfirst($extension) }} Files</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $data['count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ formatBytes($data['total_size']) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ formatBytes($data['total_size'] / $data['count']) }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $analytics['total_storage_used'] > 0 ? ($data['total_size'] / $analytics['total_storage_used']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No file type data available
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

    <!-- Storage Optimization Suggestions -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Storage Optimization Suggestions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <i class="ti ti-info-circle ti-sm me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Large Files Detected</h6>
                                        <p class="mb-0">Consider compressing large files to save storage space. Files larger than 10MB can often be compressed significantly.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <div class="d-flex">
                                    <i class="ti ti-alert-triangle ti-sm me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Storage Growth</h6>
                                        <p class="mb-0">Monitor storage growth trends. Consider implementing automatic cleanup for old files if needed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
let storageGrowthChart, fileTypeChart;

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
    initializeStorageGrowthChart();
    initializeFileTypeChart();
}

// Storage Growth Chart
function initializeStorageGrowthChart() {
    const growthData = @json($analytics['storage_growth']);
    const categories = Object.keys(growthData);
    const data = Object.values(growthData);
    
    const options = {
        series: [{
            name: 'Storage Used (MB)',
            data: data.map(bytes => Math.round(bytes / 1024 / 1024))
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
                text: 'Storage Used (MB)'
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
            },
            y: {
                formatter: function (val) {
                    return val + ' MB';
                }
            }
        }
    };

    storageGrowthChart = new ApexCharts(document.querySelector("#storageGrowthChart"), options);
    storageGrowthChart.render();
}

// File Type Chart
function initializeFileTypeChart() {
    const fileTypeData = @json($analytics['file_type_distribution']);
    const labels = Object.keys(fileTypeData);
    const data = Object.values(fileTypeData).map(item => item.count);
    
    const options = {
        series: data,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: labels.map(label => label.toUpperCase()),
        colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43', '#EA5455', '#9C27B0', '#FF5722', '#795548'],
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

    fileTypeChart = new ApexCharts(document.querySelector("#fileTypeChart"), options);
    fileTypeChart.render();
}

// Update analytics data
function updateAnalytics() {
    fetch(`{{ route('analytics.api.storage') }}?period=${currentPeriod}`)
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
    document.getElementById('totalStorage').textContent = analytics.formatted_storage;
    document.getElementById('totalFiles').textContent = analytics.total_files;
    document.getElementById('avgFileSize').textContent = formatBytes(analytics.average_file_size);
    document.getElementById('storageGrowth').textContent = `+${Object.keys(analytics.storage_growth).length} days`;
}

// Update charts
function updateCharts(analytics) {
    // Update storage growth chart
    const growthData = analytics.storage_growth;
    const categories = Object.keys(growthData);
    const data = Object.values(growthData);
    
    storageGrowthChart.updateSeries([{
        name: 'Storage Used (MB)',
        data: data.map(bytes => Math.round(bytes / 1024 / 1024))
    }]);
    storageGrowthChart.updateOptions({
        xaxis: {
            categories: categories
        }
    });
    
    // Update file type chart
    const fileTypeData = analytics.file_type_distribution;
    const labels = Object.keys(fileTypeData);
    const chartData = Object.values(fileTypeData).map(item => item.count);
    
    fileTypeChart.updateSeries(chartData);
    fileTypeChart.updateOptions({
        labels: labels.map(label => label.toUpperCase())
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
            section: 'storage',
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
