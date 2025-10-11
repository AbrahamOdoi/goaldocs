@extends('layouts.app')

@section('title', 'Advanced Insights Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Advanced Insights Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Advanced Insights</li>
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

    <!-- Performance Insights -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Performance Insights</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-4">
                                <h4 class="text-primary">{{ number_format($data['insights']['performance_insights']['performance_score'] ?? 0, 1) }}%</h4>
                                <p class="text-muted">Overall Performance Score</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-success">Strengths</h6>
                                <ul class="list-unstyled">
                                    @foreach($data['insights']['performance_insights']['strengths'] ?? [] as $strength)
                                    <li><i class="ti ti-check-circle ti-xs text-success me-2"></i>{{ $strength }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-warning">Areas for Improvement</h6>
                                <ul class="list-unstyled">
                                    @foreach($data['insights']['performance_insights']['weaknesses'] ?? [] as $weakness)
                                    <li><i class="ti ti-alert-triangle ti-xs text-warning me-2"></i>{{ $weakness }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-info">Opportunities</h6>
                                <ul class="list-unstyled">
                                    @foreach($data['insights']['performance_insights']['opportunities'] ?? [] as $opportunity)
                                    <li><i class="ti ti-lightbulb ti-xs text-info me-2"></i>{{ $opportunity }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Key Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Activities</span>
                            <strong>{{ number_format($data['insights']['performance_insights']['key_metrics']['total_activities'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Files</span>
                            <strong>{{ number_format($data['insights']['performance_insights']['key_metrics']['total_files'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Avg File Size</span>
                            <strong>{{ formatBytes($data['insights']['performance_insights']['key_metrics']['average_file_size'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimization Suggestions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Optimization Suggestions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Storage Optimization</h6>
                            @foreach($data['optimization']['storage_optimization'] ?? [] as $suggestion)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="alert-heading">{{ $suggestion['title'] }}</h6>
                                        <p class="mb-1">{{ $suggestion['description'] }}</p>
                                        <small class="text-muted">Potential savings: {{ $suggestion['potential_savings'] }}</small>
                                    </div>
                                    <span class="badge bg-{{ $suggestion['priority'] === 'high' ? 'danger' : ($suggestion['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($suggestion['priority']) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-success">Processing Optimization</h6>
                            @foreach($data['optimization']['processing_optimization'] ?? [] as $suggestion)
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="alert-heading">{{ $suggestion['title'] }}</h6>
                                        <p class="mb-1">{{ $suggestion['description'] }}</p>
                                        <small class="text-muted">Potential improvement: {{ $suggestion['potential_improvement'] }}</small>
                                    </div>
                                    <span class="badge bg-{{ $suggestion['priority'] === 'high' ? 'danger' : ($suggestion['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($suggestion['priority']) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-warning">Workflow Optimization</h6>
                            @foreach($data['optimization']['workflow_optimization'] ?? [] as $suggestion)
                            <div class="alert alert-warning">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="alert-heading">{{ $suggestion['title'] }}</h6>
                                        <p class="mb-1">{{ $suggestion['description'] }}</p>
                                        <small class="text-muted">Potential improvement: {{ $suggestion['potential_improvement'] }}</small>
                                    </div>
                                    <span class="badge bg-{{ $suggestion['priority'] === 'high' ? 'danger' : ($suggestion['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($suggestion['priority']) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-info">User Experience Optimization</h6>
                            @foreach($data['optimization']['user_experience_optimization'] ?? [] as $suggestion)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="alert-heading">{{ $suggestion['title'] }}</h6>
                                        <p class="mb-1">{{ $suggestion['description'] }}</p>
                                        <small class="text-muted">Potential improvement: {{ $suggestion['potential_improvement'] }}</small>
                                    </div>
                                    <span class="badge bg-{{ $suggestion['priority'] === 'high' ? 'danger' : ($suggestion['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($suggestion['priority']) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations and Action Items -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">AI Recommendations</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-danger">Immediate Actions</h6>
                        @foreach($data['recommendations']['immediate'] ?? [] as $action)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-alert-circle ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $action['action'] }}</h6>
                                <p class="mb-1 text-muted">{{ $action['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-{{ $action['priority'] === 'high' ? 'danger' : ($action['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($action['priority']) }}
                                    </span>
                                    <span class="badge bg-label-secondary">{{ ucfirst($action['effort']) }} effort</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-warning">Short-term Goals</h6>
                        @foreach($data['recommendations']['short_term'] ?? [] as $goal)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-target ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $goal['goal'] }}</h6>
                                <p class="mb-1 text-muted">{{ $goal['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-info">Timeline: {{ $goal['timeline'] }}</span>
                                    <span class="badge bg-label-{{ $goal['impact'] === 'high' ? 'danger' : ($goal['impact'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($goal['impact']) }} impact
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div>
                        <h6 class="text-info">Long-term Strategies</h6>
                        @foreach($data['recommendations']['long_term'] ?? [] as $strategy)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-rocket ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $strategy['strategy'] }}</h6>
                                <p class="mb-1 text-muted">{{ $strategy['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-primary">Timeline: {{ $strategy['timeline'] }}</span>
                                    <span class="badge bg-label-{{ $strategy['impact'] === 'high' ? 'danger' : ($strategy['impact'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($strategy['impact']) }} impact
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Action Items</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-danger">Critical Actions</h6>
                        @foreach($data['action_items']['critical'] ?? [] as $action)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-exclamation-triangle ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $action['action'] }}</h6>
                                <p class="mb-1 text-muted">{{ $action['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-danger">{{ ucfirst($action['priority']) }}</span>
                                    <span class="badge bg-label-secondary">Deadline: {{ $action['deadline'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-warning">Important Actions</h6>
                        @foreach($data['action_items']['important'] ?? [] as $action)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-alert-circle ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $action['action'] }}</h6>
                                <p class="mb-1 text-muted">{{ $action['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-warning">{{ ucfirst($action['priority']) }}</span>
                                    <span class="badge bg-label-secondary">Deadline: {{ $action['deadline'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div>
                        <h6 class="text-info">Improvement Actions</h6>
                        @foreach($data['action_items']['improvement'] ?? [] as $action)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-trending-up ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $action['action'] }}</h6>
                                <p class="mb-1 text-muted">{{ $action['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-label-info">{{ ucfirst($action['priority']) }}</span>
                                    <span class="badge bg-label-secondary">Deadline: {{ $action['deadline'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Best Practices -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Best Practices</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($data['recommendations']['best_practices'] ?? [] as $practice)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-check ti-xs"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $practice['practice'] }}</h6>
                                    <p class="mb-1 text-muted">{{ $practice['benefit'] }}</p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-label-primary">Frequency: {{ $practice['frequency'] }}</span>
                                        <span class="badge bg-label-info">{{ $practice['implementation'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
                            <button type="button" class="btn btn-outline-primary" onclick="exportInsights()">
                                <i class="ti ti-download ti-xs me-1"></i>Export Insights
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshInsights()">
                                <i class="ti ti-refresh ti-xs me-1"></i>Refresh Insights
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="showConfigModal()">
                                <i class="ti ti-settings ti-xs me-1"></i>Configure
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Insights Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="configContent">
                    <!-- Configuration content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveConfig()">Save Configuration</button>
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
            updateInsights();
        });
    });
}

// Update insights data
function updateInsights() {
    // Update insights
    fetch(`{{ route('advanced-insights.api.insights') }}?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateInsightsDisplay(data.insights);
            }
        })
        .catch(error => {
            console.error('Failed to update insights:', error);
        });

    // Update optimization suggestions
    fetch(`{{ route('advanced-insights.api.optimization') }}?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOptimizationDisplay(data.optimization);
            }
        })
        .catch(error => {
            console.error('Failed to update optimization:', error);
        });

    // Update recommendations
    fetch(`{{ route('advanced-insights.api.recommendations') }}?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateRecommendationsDisplay(data.recommendations);
            }
        })
        .catch(error => {
            console.error('Failed to update recommendations:', error);
        });
}

// Update insights display
function updateInsightsDisplay(insights) {
    // Update performance score
    const performanceScore = insights.performance_insights?.performance_score || 0;
    document.querySelector('.text-primary').textContent = performanceScore.toFixed(1) + '%';
    
    // Update other insights sections as needed
}

// Update optimization display
function updateOptimizationDisplay(optimization) {
    // Update optimization suggestions display
}

// Update recommendations display
function updateRecommendationsDisplay(recommendations) {
    // Update recommendations display
}

// Refresh insights
function refreshInsights() {
    updateInsights();
    showAlert('success', 'Insights refreshed successfully');
}

// Export insights
function exportInsights() {
    const sections = ['insights', 'optimization', 'recommendations', 'actions'];
    
    fetch('{{ route("advanced-insights.api.export") }}', {
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
            showAlert('success', 'Insights exported successfully');
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

// Show configuration modal
function showConfigModal() {
    const modal = new bootstrap.Modal(document.getElementById('configModal'));
    modal.show();
    
    // Load configuration
    fetch('{{ route("advanced-insights.api.config") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('configContent').innerHTML = generateConfigForm(data.config);
            }
        })
        .catch(error => {
            console.error('Failed to load configuration:', error);
        });
}

// Generate configuration form
function generateConfigForm(config) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>Insight Thresholds</h6>
                <div class="mb-3">
                    <label class="form-label">Performance Score Threshold (%)</label>
                    <input type="number" class="form-control" name="insight_thresholds[performance_score]" value="${config.insight_thresholds?.performance_score || 70}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Engagement Rate Threshold (%)</label>
                    <input type="number" class="form-control" name="insight_thresholds[engagement_rate]" value="${config.insight_thresholds?.engagement_rate || 60}">
                </div>
            </div>
            <div class="col-md-6">
                <h6>Optimization Settings</h6>
                <div class="mb-3">
                    <label class="form-label">Storage Threshold (MB)</label>
                    <input type="number" class="form-control" name="optimization_settings[storage_threshold]" value="${(config.optimization_settings?.storage_threshold || 10485760) / (1024 * 1024)}">
                </div>
                <div class="mb-3">
                    <label class="form-label">OCR Success Threshold (%)</label>
                    <input type="number" class="form-control" name="optimization_settings[ocr_success_threshold]" value="${config.optimization_settings?.ocr_success_threshold || 90}">
                </div>
            </div>
        </div>
    `;
}

// Save configuration
function saveConfig() {
    const form = document.querySelector('#configContent');
    const formData = new FormData(form);
    const config = {};
    
    for (let [key, value] of formData.entries()) {
        if (key.includes('[')) {
            const parts = key.match(/(\w+)\[(\w+)\]/);
            if (parts) {
                if (!config[parts[1]]) config[parts[1]] = {};
                config[parts[1]][parts[2]] = value;
            }
        } else {
            config[key] = value;
        }
    }
    
    fetch('{{ route("advanced-insights.api.update-config") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Configuration saved successfully');
            bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
        } else {
            showAlert('error', data.message || 'Failed to save configuration');
        }
    })
    .catch(error => {
        console.error('Save failed:', error);
        showAlert('error', 'Failed to save configuration');
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
