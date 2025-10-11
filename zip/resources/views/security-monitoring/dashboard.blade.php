@extends('layouts.app')

@section('title', 'Security Monitoring Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Security Monitoring Dashboard</h5>
                            <p class="mb-4">Real-time security monitoring, threat detection, and incident response</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex justify-content-center">
                                <div class="security-score-circle">
                                    <div class="score-value" id="securityScore">--</div>
                                    <div class="score-label">Security Score</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active Threats</p>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="text-danger mb-0 me-2" id="activeThreats">0</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-danger rounded p-2">
                                <i class="ti ti-alert-triangle ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Security Alerts</p>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="text-warning mb-0 me-2" id="securityAlerts">0</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ti ti-bell ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Critical Events</p>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="text-danger mb-0 me-2" id="criticalEvents">0</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-danger rounded p-2">
                                <i class="ti ti-exclamation-circle ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Vulnerability Score</p>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="text-success mb-0 me-2" id="vulnerabilityScore">--</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ti ti-shield-check ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('security-monitoring.real-time') }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-eye ti-sm me-2"></i>
                                Real-time Monitoring
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('security-monitoring.threat-detection') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-search ti-sm me-2"></i>
                                Threat Detection
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('security-monitoring.incident-response') }}" class="btn btn-outline-danger w-100">
                                <i class="ti ti-alert-triangle ti-sm me-2"></i>
                                Incident Response
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('security-monitoring.reporting') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-file-report ti-sm me-2"></i>
                                Security Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Status -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Security Status</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="ti ti-shield-check ti-sm me-2"></i>
                        <strong>System Status:</strong> All security systems are operational
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-eye ti-sm me-2"></i>
                        <strong>Monitoring:</strong> Real-time monitoring is active
                    </div>
                    <div class="alert alert-warning">
                        <i class="ti ti-bell ti-sm me-2"></i>
                        <strong>Alerts:</strong> No critical alerts at this time
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted">
                        <i class="ti ti-clock ti-lg"></i>
                        <p class="mt-2">No recent security events</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.security-score-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.score-value {
    font-size: 1.5rem;
    line-height: 1;
}

.score-label {
    font-size: 0.7rem;
    opacity: 0.9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    loadDashboardData();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadDashboardData();
    }, 30000);
});

function loadDashboardData() {
    // Mock data for demonstration
    document.getElementById('activeThreats').textContent = '0';
    document.getElementById('securityAlerts').textContent = '0';
    document.getElementById('criticalEvents').textContent = '0';
    document.getElementById('securityScore').textContent = '95';
    document.getElementById('vulnerabilityScore').textContent = '85';
}

// Add tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
