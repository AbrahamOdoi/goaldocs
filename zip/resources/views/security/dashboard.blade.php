@extends('layouts.app')

@section('title', 'Security Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Security Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Security</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Security Events</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['total_security_events']) }}</h4>
                                <small class="text-success">+{{ number_format($stats['total_security_events'] * 0.1) }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-shield ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Suspicious Events</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['suspicious_events']) }}</h4>
                                <small class="text-danger">+{{ number_format($stats['suspicious_events'] * 0.05) }}</small>
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
                            <span class="fw-semibold d-block mb-1">Critical Events</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['critical_events']) }}</h4>
                                <small class="text-danger">+{{ number_format($stats['critical_events'] * 0.02) }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="ti ti-exclamation-triangle ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Active Sessions</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['active_sessions']) }}</h4>
                                <small class="text-success">+{{ number_format($stats['active_sessions'] * 0.15) }}</small>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-users ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Overview -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Security Events Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Event Severity Distribution</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Critical</span>
                                    <span>{{ $stats['critical_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $stats['total_security_events'] > 0 ? ($stats['critical_events'] / $stats['total_security_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>High</span>
                                    <span>{{ $stats['high_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['total_security_events'] > 0 ? ($stats['high_events'] / $stats['total_security_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Medium</span>
                                    <span>{{ $stats['medium_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $stats['total_security_events'] > 0 ? ($stats['medium_events'] / $stats['total_security_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Low</span>
                                    <span>{{ $stats['low_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['total_security_events'] > 0 ? ($stats['low_events'] / $stats['total_security_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Compliance Status</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Compliance Events</span>
                                    <span>{{ $stats['compliance_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $stats['total_audit_events'] > 0 ? ($stats['compliance_events'] / $stats['total_audit_events']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Audit Events</span>
                                    <span>{{ $stats['total_audit_events'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 100%"></div>
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
                        <a href="{{ route('security.logs') }}" class="btn btn-outline-primary">
                            <i class="ti ti-file-text ti-xs me-2"></i>View Security Logs
                        </a>
                        <a href="{{ route('security.audit-logs') }}" class="btn btn-outline-info">
                            <i class="ti ti-clipboard-list ti-xs me-2"></i>View Audit Logs
                        </a>
                        <a href="{{ route('security.sessions') }}" class="btn btn-outline-warning">
                            <i class="ti ti-users ti-xs me-2"></i>Manage Sessions
                        </a>
                        <a href="{{ route('security.policies') }}" class="btn btn-outline-success">
                            <i class="ti ti-settings ti-xs me-2"></i>Security Policies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Security Events -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Security Events</h5>
                    <a href="{{ route('security.logs') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Severity</th>
                                    <th>Location</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSecurityEvents as $event)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-label-{{ $event->severity === 'critical' ? 'danger' : ($event->severity === 'high' ? 'warning' : ($event->severity === 'medium' ? 'info' : 'success')) }}">
                                                    <i class="ti ti-{{ $event->event_type === 'login' ? 'login' : ($event->event_type === 'logout' ? 'logout' : 'alert-circle') }} ti-xs"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $event->event_type_label }}</h6>
                                                <small class="text-muted">{{ Str::limit($event->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $event->user ? $event->user->name : 'System' }}</td>
                                    <td>
                                        <span class="{{ $event->severity_badge_class }}">{{ ucfirst($event->severity) }}</span>
                                    </td>
                                    <td>{{ $event->location_info }}</td>
                                    <td>{{ $event->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if($event->isResolved())
                                            <span class="badge bg-label-success">Resolved</span>
                                        @elseif($event->requires_review)
                                            <span class="badge bg-label-warning">Review Required</span>
                                        @else
                                            <span class="badge bg-label-secondary">Open</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No recent security events</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Audit Events -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Audit Events</h5>
                    <a href="{{ route('security.audit-logs') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Resource</th>
                                    <th>Compliance</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAuditEvents as $event)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-label-{{ $event->action === 'create' ? 'success' : ($event->action === 'update' ? 'warning' : ($event->action === 'delete' ? 'danger' : 'info')) }}">
                                                    <i class="ti ti-{{ $event->action === 'create' ? 'plus' : ($event->action === 'update' ? 'edit' : ($event->action === 'delete' ? 'trash' : 'eye')) }} ti-xs"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $event->action_label }}</h6>
                                                <small class="text-muted">{{ $event->resource_type_label }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $event->user ? $event->user->name : 'System' }}</td>
                                    <td>{{ $event->resource_name ?: $event->resource_type }}</td>
                                    <td>
                                        @if($event->is_compliance_related)
                                            <span class="{{ $event->compliance_badge_class }}">{{ $event->compliance_standard }}</span>
                                        @else
                                            <span class="badge bg-label-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $event->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent audit events</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Active Sessions</h5>
                    <a href="{{ route('security.sessions') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Device</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeSessions as $session)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-user ti-xs"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $session->user->name }}</h6>
                                                <small class="text-muted">{{ $session->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti {{ $session->device_type_icon }} ti-sm me-2"></i>
                                            <span>{{ $session->device_type_label }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $session->location_info }}</td>
                                    <td>
                                        <span class="{{ $session->status_badge_class }}">{{ $session->status_label }}</span>
                                    </td>
                                    <td>{{ $session->last_activity ? $session->last_activity->diffForHumans() : 'Never' }}</td>
                                    <td>{{ $session->session_duration }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No active sessions</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Policies -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Active Security Policies</h5>
                    <a href="{{ route('security.policies') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($policies as $policy)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti {{ $policy->type_icon }} ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $policy->name }}</h6>
                                            <small class="text-muted">{{ $policy->type_label }}</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">{{ Str::limit($policy->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="{{ $policy->status_badge_class }}">{{ $policy->status_label }}</span>
                                        <small class="text-muted">{{ $policy->scope_label }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="ti ti-settings ti-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No active security policies</h6>
                                <p class="text-muted">Create security policies to protect your system</p>
                            </div>
                        </div>
                        @endforelse
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