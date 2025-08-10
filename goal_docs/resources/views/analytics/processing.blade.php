@extends('layouts.app')

@section('title', 'Processing Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Processing Analytics
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('analytics.dashboard') }}">Analytics</a>
                    </li>
                    <li class="breadcrumb-item active">Processing</li>
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

    <!-- Processing Overview Stats -->
    <div class="row g-4 mb-4">
        <!-- OCR Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">OCR Jobs</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="ocrTotal">{{ $analytics['ocr']['total_processed'] }}</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    {{ $analytics['ocr']['successful'] }} successful
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-scan ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Conversions</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="conversionTotal">{{ $analytics['conversion']['total_conversions'] }}</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    {{ $analytics['conversion']['successful'] }} successful
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-refresh ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Processing Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Batch Jobs</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="batchTotal">{{ $analytics['batch_processing']['total_jobs'] }}</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    {{ $analytics['batch_processing']['completed'] }} completed
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-settings ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Text Extraction Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Text Extractions</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="extractionTotal">{{ $analytics['text_extraction']['total_extractions'] }}</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    {{ $analytics['text_extraction']['successful'] }} successful
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-file-text ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Performance Charts -->
    <div class="row g-4 mb-4">
        <!-- Processing Overview Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Processing Performance Overview</h5>
                </div>
                <div class="card-body">
                    <div id="processingOverviewChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Success Rates Chart -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Success Rates</h5>
                </div>
                <div class="card-body">
                    <div id="successRatesChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Processing Analytics -->
    <div class="row g-4">
        <!-- OCR Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">OCR Processing Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['ocr']['successful'] }}</h4>
                                <small class="text-muted">Successful</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-danger">{{ $analytics['ocr']['failed'] }}</h4>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ number_format($analytics['ocr']['average_confidence'], 1) }}%</h4>
                            <small class="text-muted">Avg Confidence</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Language</th>
                                    <th>Count</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['ocr']['language_distribution'] as $language => $count)
                                <tr>
                                    <td>{{ strtoupper($language) }}</td>
                                    <td>{{ $count }}</td>
                                    <td>
                                        @php
                                            $successRate = $analytics['ocr']['total_processed'] > 0 ? ($count / $analytics['ocr']['total_processed']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($successRate, 1) }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No OCR data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Analytics -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Format Conversion Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['conversion']['successful'] }}</h4>
                                <small class="text-muted">Successful</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="mb-0 text-danger">{{ $analytics['conversion']['failed'] }}</h4>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ number_format($analytics['conversion']['average_processing_time'], 1) }}s</h4>
                            <small class="text-muted">Avg Time</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Target Format</th>
                                    <th>Count</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['conversion']['format_distribution'] as $format => $count)
                                <tr>
                                    <td>{{ strtoupper($format) }}</td>
                                    <td>{{ $count }}</td>
                                    <td>
                                        @php
                                            $successRate = $analytics['conversion']['total_conversions'] > 0 ? ($count / $analytics['conversion']['total_conversions']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($successRate, 1) }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No conversion data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Processing Details -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Batch Processing Details</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary">{{ $analytics['batch_processing']['total_files_processed'] }}</h4>
                                <small class="text-muted">Total Files Processed</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-success">{{ $analytics['batch_processing']['successful_files'] }}</h4>
                                <small class="text-muted">Successful Files</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="mb-0 text-danger">{{ $analytics['batch_processing']['failed_files'] }}</h4>
                                <small class="text-muted">Failed Files</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="mb-0 text-info">{{ $analytics['batch_processing']['processing'] }}</h4>
                            <small class="text-muted">Currently Processing</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Operation Type</th>
                                    <th>Jobs</th>
                                    <th>Success Rate</th>
                                    <th>Avg Processing Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['batch_processing']['operation_distribution'] as $operation => $count)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $operation)) }}</td>
                                    <td>{{ $count }}</td>
                                    <td>
                                        @php
                                            $successRate = $analytics['batch_processing']['total_jobs'] > 0 ? ($count / $analytics['batch_processing']['total_jobs']) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($successRate, 1) }}%</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">N/A</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No batch processing data available</td>
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
let processingOverviewChart, successRatesChart;

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
    initializeProcessingOverviewChart();
    initializeSuccessRatesChart();
}

// Processing Overview Chart
function initializeProcessingOverviewChart() {
    const options = {
        series: [{
            name: 'OCR Jobs',
            data: [@json($analytics['ocr']['total_processed'])]
        }, {
            name: 'Conversions',
            data: [@json($analytics['conversion']['total_conversions'])]
        }, {
            name: 'Batch Jobs',
            data: [@json($analytics['batch_processing']['total_jobs'])]
        }, {
            name: 'Text Extractions',
            data: [@json($analytics['text_extraction']['total_extractions'])]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true
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
            categories: ['Processing Operations'],
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Number of Operations'
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

    processingOverviewChart = new ApexCharts(document.querySelector("#processingOverviewChart"), options);
    processingOverviewChart.render();
}

// Success Rates Chart
function initializeSuccessRatesChart() {
    const successRates = [
        @php
            $ocrSuccessRate = $analytics['ocr']['total_processed'] > 0 ? ($analytics['ocr']['successful'] / $analytics['ocr']['total_processed']) * 100 : 0;
            $conversionSuccessRate = $analytics['conversion']['total_conversions'] > 0 ? ($analytics['conversion']['successful'] / $analytics['conversion']['total_conversions']) * 100 : 0;
            $batchSuccessRate = $analytics['batch_processing']['total_jobs'] > 0 ? ($analytics['batch_processing']['completed'] / $analytics['batch_processing']['total_jobs']) * 100 : 0;
            $extractionSuccessRate = $analytics['text_extraction']['total_extractions'] > 0 ? ($analytics['text_extraction']['successful'] / $analytics['text_extraction']['total_extractions']) * 100 : 0;
        @endphp
        { name: 'OCR', value: {{ $ocrSuccessRate }} },
        { name: 'Conversion', value: {{ $conversionSuccessRate }} },
        { name: 'Batch Processing', value: {{ $batchSuccessRate }} },
        { name: 'Text Extraction', value: {{ $extractionSuccessRate }} }
    ];
    
    const options = {
        series: successRates.map(item => item.value),
        chart: {
            type: 'radialBar',
            height: 350
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
                        show: true,
                        fontSize: '14px',
                        fontFamily: 'inherit',
                        color: '#666',
                        offsetY: -10
                    },
                    value: {
                        fontSize: '20px',
                        show: true,
                        formatter: function (val) {
                            return val.toFixed(1) + '%';
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
        labels: successRates.map(item => item.name)
    };

    successRatesChart = new ApexCharts(document.querySelector("#successRatesChart"), options);
    successRatesChart.render();
}

// Update analytics data
function updateAnalytics() {
    fetch(`{{ route('analytics.api.processing') }}?period=${currentPeriod}`)
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
    document.getElementById('ocrTotal').textContent = analytics.ocr.total_processed;
    document.getElementById('conversionTotal').textContent = analytics.conversion.total_conversions;
    document.getElementById('batchTotal').textContent = analytics.batch_processing.total_jobs;
    document.getElementById('extractionTotal').textContent = analytics.text_extraction.total_extractions;
}

// Update charts
function updateCharts(analytics) {
    // Update processing overview chart
    processingOverviewChart.updateSeries([
        { name: 'OCR Jobs', data: [analytics.ocr.total_processed] },
        { name: 'Conversions', data: [analytics.conversion.total_conversions] },
        { name: 'Batch Jobs', data: [analytics.batch_processing.total_jobs] },
        { name: 'Text Extractions', data: [analytics.text_extraction.total_extractions] }
    ]);
    
    // Update success rates chart
    const successRates = [
        { name: 'OCR', value: analytics.ocr.total_processed > 0 ? (analytics.ocr.successful / analytics.ocr.total_processed) * 100 : 0 },
        { name: 'Conversion', value: analytics.conversion.total_conversions > 0 ? (analytics.conversion.successful / analytics.conversion.total_conversions) * 100 : 0 },
        { name: 'Batch Processing', value: analytics.batch_processing.total_jobs > 0 ? (analytics.batch_processing.completed / analytics.batch_processing.total_jobs) * 100 : 0 },
        { name: 'Text Extraction', value: analytics.text_extraction.total_extractions > 0 ? (analytics.text_extraction.successful / analytics.text_extraction.total_extractions) * 100 : 0 }
    ];
    
    successRatesChart.updateSeries(successRates.map(item => item.value));
    successRatesChart.updateOptions({
        labels: successRates.map(item => item.name)
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
            section: 'processing',
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
