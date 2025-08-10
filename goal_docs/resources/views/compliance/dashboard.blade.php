@extends('layouts.app')

@section('title', 'Compliance Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Compliance Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Compliance</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Compliance Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Compliance Events</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['total_compliance_events']) }}</h4>
                                <small class="text-success">+{{ number_format($stats['total_compliance_events'] * 0.1) }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-clipboard-check ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Security Incidents</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['security_incidents']) }}</h4>
                                <small class="text-danger">+{{ number_format($stats['security_incidents'] * 0.05) }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-alert-triangle ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Compliance Score</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['compliance_score'], 1) }}%</h4>
                                <small class="text-success">+2.5%</small>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-chart-line ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Standards Covered</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['compliance_standards']) }}</h4>
                                <small class="text-info">Active</small>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-shield-check ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Overview -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Compliance Standards Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Compliance Events by Standard</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>GDPR</span>
                                    <span>{{ $stats['gdpr_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['gdpr_events'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>HIPAA</span>
                                    <span>{{ $stats['hipaa_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['hipaa_events'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>SOX</span>
                                    <span>{{ $stats['sox_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['sox_events'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>PCI</span>
                                    <span>{{ $stats['pci_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['pci_events'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>ISO27001</span>
                                    <span>{{ $stats['iso27001_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['iso27001_events'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Compliance Status</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Violations Detected</span>
                                    <span>{{ $stats['violations_detected'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['violations_detected'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Security Incidents</span>
                                    <span>{{ $stats['security_incidents'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['total_compliance_events'] > 0 ? ($stats['security_incidents'] / $stats['total_compliance_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('compliance.reports') }}" class="btn btn-outline-primary">
                            <i class="ti ti-file-text ti-xs me-2"></i>Generate Reports
                        </a>
                        <a href="{{ route('compliance.audit-logs') }}" class="btn btn-outline-info">
                            <i class="ti ti-clipboard-list ti-xs me-2"></i>View Audit Logs
                        </a>
                        <a href="{{ route('compliance.violations') }}" class="btn btn-outline-warning">
                            <i class="ti ti-alert-triangle ti-xs me-2"></i>Check Violations
                        </a>
                        <a href="{{ route('compliance.recommendations') }}" class="btn btn-outline-success">
                            <i class="ti ti-lightbulb ti-xs me-2"></i>View Recommendations
                        </a>
                        <a href="{{ route('compliance.settings') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-settings ti-xs me-2"></i>Compliance Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Standards -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Compliance Standards</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-shield-check ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">GDPR</h6>
                                            <small class="text-muted">General Data Protection Regulation</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">European Union data protection and privacy regulation</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-primary">Active</span>
                                        <small class="text-muted">{{ $stats['gdpr_events'] }} events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="ti ti-heart ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">HIPAA</h6>
                                            <small class="text-muted">Health Insurance Portability and Accountability Act</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">US healthcare data protection and privacy regulation</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-success">Active</span>
                                        <small class="text-muted">{{ $stats['hipaa_events'] }} events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="ti ti-chart-line ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">SOX</h6>
                                            <small class="text-muted">Sarbanes-Oxley Act</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">US financial reporting and corporate governance regulation</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-warning">Active</span>
                                        <small class="text-muted">{{ $stats['sox_events'] }} events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-danger">
                                                <i class="ti ti-credit-card ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">PCI DSS</h6>
                                            <small class="text-muted">Payment Card Industry Data Security Standard</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Payment card industry security standards</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-danger">Active</span>
                                        <small class="text-muted">{{ $stats['pci_events'] }} events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="ti ti-shield ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">ISO 27001</h6>
                                            <small class="text-muted">Information Security Management System</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">International information security management standard</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-info">Active</span>
                                        <small class="text-muted">{{ $stats['iso27001_events'] }} events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Compliance Events -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Compliance Events</h5>
                    <a href="{{ route('compliance.audit-logs') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Standard</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Resource</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-primary">GDPR</span>
                                    </td>
                                    <td>Data Access</td>
                                    <td>John Doe</td>
                                    <td>User Profile</td>
                                    <td>2 hours ago</td>
                                    <td><span class="badge bg-label-success">Compliant</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-success">HIPAA</span>
                                    </td>
                                    <td>PHI Access</td>
                                    <td>Jane Smith</td>
                                    <td>Medical Record</td>
                                    <td>4 hours ago</td>
                                    <td><span class="badge bg-label-success">Compliant</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-warning">SOX</span>
                                    </td>
                                    <td>Financial Data Modification</td>
                                    <td>Bob Johnson</td>
                                    <td>Financial Report</td>
                                    <td>6 hours ago</td>
                                    <td><span class="badge bg-label-warning">Review Required</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-danger">PCI</span>
                                    </td>
                                    <td>Card Data Access</td>
                                    <td>Alice Brown</td>
                                    <td>Payment Record</td>
                                    <td>8 hours ago</td>
                                    <td><span class="badge bg-label-danger">Violation</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-info">ISO27001</span>
                                    </td>
                                    <td>Security Incident</td>
                                    <td>System</td>
                                    <td>Security Log</td>
                                    <td>12 hours ago</td>
                                    <td><span class="badge bg-label-warning">Investigation</span></td>
                                </tr>
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
<script>
// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
