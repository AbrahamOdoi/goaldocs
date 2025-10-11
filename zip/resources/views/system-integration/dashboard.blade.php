@extends('layouts.contentLayoutMaster')

@section('title', 'System Integration Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('page-style')
<style>
    .card-header {
        border-bottom: 1px solid #ebe9f1;
        padding-bottom: 1rem;
    }
    .card-body {
        padding-top: 1rem;
    }
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-healthy {
        background-color: #28c76f;
    }
    .status-warning {
        background-color: #ff9f43;
    }
    .status-error {
        background-color: #ea5455;
    }
    .component-card {
        border-left: 4px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .component-card.healthy {
        border-left-color: #28c76f;
    }
    .component-card.warning {
        border-left-color: #ff9f43;
    }
    .component-card.error {
        border-left-color: #ea5455;
    }
    .test-button {
        min-width: 120px;
    }
    .progress-ring {
        transform: rotate(-90deg);
    }
    .progress-ring-circle {
        transition: stroke-dasharray 0.35s;
        transform-origin: 50% 50%;
    }
    .loading-spinner {
        display: none;
    }
    .loading-spinner.show {
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">System Integration /</span> Dashboard
    </h4>

    <!-- System Health Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <svg class="progress-ring" width="80" height="80">
                            <circle class="progress-ring-circle" stroke="#e9ecef" stroke-width="8" fill="transparent" r="32" cx="40" cy="40"/>
                            <circle class="progress-ring-circle" stroke="#28c76f" stroke-width="8" fill="transparent" r="32" cx="40" cy="40" id="healthProgress"/>
                        </svg>
                    </div>
                    <h4 class="mb-1" id="healthPercentage">0%</h4>
                    <p class="mb-0">System Health</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-plug-connected ti-3x text-primary mb-3"></i>
                    <h4 class="mb-1" id="integrationStatus">Checking...</h4>
                    <p class="mb-0">Integration Status</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-database ti-3x text-info mb-3"></i>
                    <h4 class="mb-1" id="dataConsistency">Checking...</h4>
                    <p class="mb-0">Data Consistency</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-test-pipe ti-3x text-warning mb-3"></i>
                    <h4 class="mb-1" id="crossModuleStatus">Checking...</h4>
                    <p class="mb-0">Cross-Module Tests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-primary w-100 test-button" onclick="runIntegrationCheck()">
                                <i class="ti ti-refresh me-2"></i>
                                <span class="loading-spinner me-2">
                                    <i class="ti ti-loader ti-spin"></i>
                                </span>
                                Integration Check
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-info w-100 test-button" onclick="runCrossModuleTest()">
                                <i class="ti ti-test-pipe me-2"></i>
                                <span class="loading-spinner me-2">
                                    <i class="ti ti-loader ti-spin"></i>
                                </span>
                                Cross-Module Test
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-warning w-100 test-button" onclick="checkDataConsistency()">
                                <i class="ti ti-database me-2"></i>
                                <span class="loading-spinner me-2">
                                    <i class="ti ti-loader ti-spin"></i>
                                </span>
                                Data Consistency
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-success w-100 test-button" onclick="runComprehensiveTest()">
                                <i class="ti ti-check me-2"></i>
                                <span class="loading-spinner me-2">
                                    <i class="ti ti-loader ti-spin"></i>
                                </span>
                                Full System Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Components Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Components Status</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="componentsStatus">
                        <!-- Components will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test Results</h5>
                </div>
                <div class="card-body">
                    <div id="testResults">
                        <div class="text-center text-muted">
                            <i class="ti ti-clipboard ti-lg"></i>
                            <p class="mt-2">No test results available. Run a test to see results.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    loadSystemHealth();
    
    // Auto-refresh every 60 seconds
    setInterval(function() {
        loadSystemHealth();
    }, 60000);
});

function loadSystemHealth() {
    fetch('/system-integration/api/system-health')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateHealthDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading system health:', error);
        });
}

function updateHealthDisplay(data) {
    // Update health percentage
    const healthPercentage = calculateHealthPercentage(data);
    document.getElementById('healthPercentage').textContent = healthPercentage + '%';
    
    // Update progress ring
    updateProgressRing(healthPercentage);
    
    // Update status indicators
    document.getElementById('integrationStatus').textContent = data.integration_status.status ? 'Healthy' : 'Issues';
    document.getElementById('dataConsistency').textContent = data.data_consistency_status.overall_consistent ? 'Consistent' : 'Inconsistent';
    document.getElementById('crossModuleStatus').textContent = data.cross_module_status.overall_status ? 'Passed' : 'Failed';
    
    // Update components status
    updateComponentsStatus(data.integration_status.results);
}

function calculateHealthPercentage(data) {
    let totalChecks = 0;
    let passedChecks = 0;
    
    // Integration checks
    Object.values(data.integration_status.results).forEach(result => {
        totalChecks++;
        if (result) passedChecks++;
    });
    
    // Cross-module checks
    Object.values(data.cross_module_status.tests).forEach(result => {
        totalChecks++;
        if (result) passedChecks++;
    });
    
    // Consistency checks
    Object.values(data.data_consistency_status.checks).forEach(result => {
        totalChecks++;
        if (result) passedChecks++;
    });
    
    return Math.round((passedChecks / totalChecks) * 100);
}

function updateProgressRing(percentage) {
    const circle = document.getElementById('healthProgress');
    const radius = circle.r.baseVal.value;
    const circumference = radius * 2 * Math.PI;
    
    circle.style.strokeDasharray = `${circumference} ${circumference}`;
    circle.style.strokeDashoffset = circumference;
    
    const offset = circumference - (percentage / 100 * circumference);
    circle.style.strokeDashoffset = offset;
    
    // Update color based on percentage
    if (percentage >= 90) {
        circle.style.stroke = '#28c76f';
    } else if (percentage >= 70) {
        circle.style.stroke = '#ff9f43';
    } else {
        circle.style.stroke = '#ea5455';
    }
}

function updateComponentsStatus(results) {
    const container = document.getElementById('componentsStatus');
    container.innerHTML = '';
    
    Object.entries(results).forEach(([component, status]) => {
        const statusClass = status ? 'healthy' : 'error';
        const statusText = status ? 'Healthy' : 'Error';
        const statusColor = status ? '#28c76f' : '#ea5455';
        
        const componentCard = `
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card component-card ${statusClass}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${formatComponentName(component)}</h6>
                                <small class="text-muted">System Component</small>
                            </div>
                            <div>
                                <span class="status-indicator" style="background-color: ${statusColor}"></span>
                                <span class="fw-bold">${statusText}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML += componentCard;
    });
}

function formatComponentName(component) {
    return component.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

function showLoading(button) {
    const spinner = button.querySelector('.loading-spinner');
    const icon = button.querySelector('i');
    const text = button.textContent.trim();
    
    spinner.classList.add('show');
    icon.style.display = 'none';
    button.disabled = true;
    button.dataset.originalText = text;
}

function hideLoading(button) {
    const spinner = button.querySelector('.loading-spinner');
    const icon = button.querySelector('i');
    
    spinner.classList.remove('show');
    icon.style.display = 'inline-block';
    button.disabled = false;
    button.textContent = button.dataset.originalText;
}

function runIntegrationCheck() {
    const button = event.target.closest('button');
    showLoading(button);
    
    fetch('/system-integration/api/integration-check')
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            if (data.success) {
                displayTestResults('Integration Check', data.data);
                loadSystemHealth(); // Refresh health display
            } else {
                showError('Integration check failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading(button);
            showError('Integration check failed: ' + error.message);
        });
}

function runCrossModuleTest() {
    const button = event.target.closest('button');
    showLoading(button);
    
    fetch('/system-integration/api/cross-module-test')
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            if (data.success) {
                displayTestResults('Cross-Module Test', data.data);
                loadSystemHealth(); // Refresh health display
            } else {
                showError('Cross-module test failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading(button);
            showError('Cross-module test failed: ' + error.message);
        });
}

function checkDataConsistency() {
    const button = event.target.closest('button');
    showLoading(button);
    
    fetch('/system-integration/api/data-consistency')
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            if (data.success) {
                displayTestResults('Data Consistency Check', data.data);
                loadSystemHealth(); // Refresh health display
            } else {
                showError('Data consistency check failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading(button);
            showError('Data consistency check failed: ' + error.message);
        });
}

function runComprehensiveTest() {
    const button = event.target.closest('button');
    showLoading(button);
    
    fetch('/system-integration/api/comprehensive-test')
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            if (data.success) {
                displayTestResults('Comprehensive System Test', data.data);
                loadSystemHealth(); // Refresh health display
            } else {
                showError('Comprehensive test failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading(button);
            showError('Comprehensive test failed: ' + error.message);
        });
}

function displayTestResults(testName, data) {
    const container = document.getElementById('testResults');
    
    let resultsHtml = `
        <div class="mb-3">
            <h6 class="text-primary">${testName} Results</h6>
            <small class="text-muted">Executed at: ${new Date().toLocaleString()}</small>
        </div>
    `;
    
    if (data.execution_time_ms) {
        resultsHtml += `
            <div class="alert alert-info">
                <i class="ti ti-clock ti-sm me-2"></i>
                <strong>Execution Time:</strong> ${data.execution_time_ms}ms
            </div>
        `;
    }
    
    if (data.summary) {
        resultsHtml += `
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-primary">${data.summary.total_tests}</h4>
                            <small>Total Tests</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>${data.summary.passed_tests}</h4>
                            <small>Passed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>${data.summary.failed_tests}</h4>
                            <small>Failed</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    if (data.overall_status !== undefined) {
        const statusClass = data.overall_status ? 'success' : 'danger';
        const statusText = data.overall_status ? 'All Tests Passed' : 'Some Tests Failed';
        
        resultsHtml += `
            <div class="alert alert-${statusClass}">
                <i class="ti ti-${data.overall_status ? 'check' : 'x'} ti-sm me-2"></i>
                <strong>Overall Status:</strong> ${statusText}
            </div>
        `;
    }
    
    // Add detailed results if available
    if (data.integration_test || data.cross_module_test || data.consistency_test) {
        resultsHtml += '<div class="mt-3"><h6>Detailed Results:</h6>';
        
        if (data.integration_test) {
            resultsHtml += `
                <div class="mb-2">
                    <strong>Integration Test:</strong> 
                    <span class="badge bg-${data.integration_test.status ? 'success' : 'danger'}">
                        ${data.integration_test.status ? 'Passed' : 'Failed'}
                    </span>
                </div>
            `;
        }
        
        if (data.cross_module_test) {
            resultsHtml += `
                <div class="mb-2">
                    <strong>Cross-Module Test:</strong> 
                    <span class="badge bg-${data.cross_module_test.overall_status ? 'success' : 'danger'}">
                        ${data.cross_module_test.overall_status ? 'Passed' : 'Failed'}
                    </span>
                </div>
            `;
        }
        
        if (data.consistency_test) {
            resultsHtml += `
                <div class="mb-2">
                    <strong>Data Consistency Test:</strong> 
                    <span class="badge bg-${data.consistency_test.overall_consistent ? 'success' : 'danger'}">
                        ${data.consistency_test.overall_consistent ? 'Consistent' : 'Inconsistent'}
                    </span>
                </div>
            `;
        }
        
        resultsHtml += '</div>';
    }
    
    container.innerHTML = resultsHtml;
}

function showError(message) {
    const container = document.getElementById('testResults');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="ti ti-alert-triangle ti-sm me-2"></i>
            <strong>Error:</strong> ${message}
        </div>
    `;
}
</script>
@endsection
