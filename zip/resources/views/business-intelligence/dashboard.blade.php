@extends('layouts.app')

@section('title', 'Business Intelligence Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Business Intelligence Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Business Intelligence</li>
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
                            <h5 class="card-title mb-0">Analysis Period</h5>
                        </div>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="period" id="period7d" value="7d" {{ $period === '7d' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="period7d">7 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period30d" value="30d" {{ $period === '30d' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="period30d">30 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period90d" value="90d" {{ $period === '90d' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="period90d">90 Days</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period1y" value="1y" {{ $period === '1y' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="period1y">1 Year</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Document Engagement</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="engagementRate">{{ number_format($data['kpis']['document_usage']['engagement_rate'] ?? 0, 1) }}%</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    +12%
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
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
                            <span class="fw-semibold d-block mb-1">Processing Success</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="processingSuccess">{{ number_format($data['kpis']['processing']['overall_processing_success'] ?? 0, 1) }}%</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    +5%
                                </small>
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
                            <span class="fw-semibold d-block mb-1">Storage Efficiency</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="storageEfficiency">{{ number_format($data['kpis']['storage']['storage_efficiency'] ?? 0, 1) }}%</h4>
                                <small class="text-warning">
                                    <i class="ti ti-arrow-down ti-xs"></i>
                                    -2%
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
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
                            <span class="fw-semibold d-block mb-1">User Efficiency</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2" id="userEfficiency">{{ number_format($data['kpis']['efficiency']['user_efficiency'] ?? 0, 1) }}%</h4>
                                <small class="text-success">
                                    <i class="ti ti-arrow-up ti-xs"></i>
                                    +8%
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-users ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Insights -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Business Insights</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">Strengths</h6>
                        <ul class="list-unstyled">
                            @foreach($data['insights']['performance']['strengths'] ?? [] as $strength)
                            <li><i class="ti ti-check-circle ti-xs text-success me-2"></i>{{ $strength }}</li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-warning">Areas for Improvement</h6>
                        <ul class="list-unstyled">
                            @foreach($data['insights']['performance']['weaknesses'] ?? [] as $weakness)
                            <li><i class="ti ti-alert-triangle ti-xs text-warning me-2"></i>{{ $weakness }}</li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-info">Opportunities</h6>
                        <ul class="list-unstyled">
                            @foreach($data['insights']['performance']['opportunities'] ?? [] as $opportunity)
                            <li><i class="ti ti-lightbulb ti-xs text-info me-2"></i>{{ $opportunity }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Predictive Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Document Usage Forecast</h6>
                        <div class="d-flex justify-content-between">
                            <span>Predicted Volume:</span>
                            <strong>{{ number_format($data['predictions']['document_usage']['predicted_volume'] ?? 0) }}</strong>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 75%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Storage Growth Prediction</h6>
                        <div class="d-flex justify-content-between">
                            <span>Growth Rate:</span>
                            <strong>{{ $data['predictions']['storage_growth']['predicted_growth'] ?? '0%' }}</strong>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 60%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Processing Demand</h6>
                        <div class="d-flex justify-content-between">
                            <span>Predicted Demand:</span>
                            <strong>{{ number_format($data['predictions']['processing_demand']['predicted_demand'] ?? 0) }}</strong>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
                        </div>
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
                            <button type="button" class="btn btn-outline-primary" onclick="exportBIData()">
                                <i class="ti ti-download ti-xs me-1"></i>Export Data
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshDashboard()">
                                <i class="ti ti-refresh ti-xs me-1"></i>Refresh Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPeriod = '{{ $period }}';

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializePeriodSelector();
});

// Initialize period selector
function initializePeriodSelector() {
    const periodInputs = document.querySelectorAll('input[name="period"]');
    periodInputs.forEach(input => {
        input.addEventListener('change', function() {
            currentPeriod = this.value;
            updateDashboard();
        });
    });
}

// Update dashboard data
function updateDashboard() {
    // Update KPIs
    fetch(`{{ route('bi.api.kpis') }}?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateKPIs(data.kpis);
            }
        })
        .catch(error => {
            console.error('Failed to update KPIs:', error);
        });
}

// Update KPIs
function updateKPIs(kpis) {
    document.getElementById('engagementRate').textContent = kpis.document_usage.engagement_rate.toFixed(1) + '%';
    document.getElementById('processingSuccess').textContent = kpis.processing.overall_processing_success.toFixed(1) + '%';
    document.getElementById('storageEfficiency').textContent = kpis.storage.storage_efficiency.toFixed(1) + '%';
    document.getElementById('userEfficiency').textContent = kpis.efficiency.user_efficiency.toFixed(1) + '%';
}

// Refresh dashboard
function refreshDashboard() {
    updateDashboard();
    showAlert('success', 'Dashboard refreshed successfully');
}

// Export BI data
function exportBIData() {
    const sections = ['kpis', 'trends', 'insights', 'predictions'];
    
    fetch('{{ route("bi.api.export") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            period: currentPeriod,
            format: 'json',
            sections: sections
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'BI data exported successfully');
            window.open(data.download_url, '_blank');
        } else {
            showAlert('error', data.message || 'Export failed');
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
