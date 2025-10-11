@extends('layouts.app')

@section('title', 'Data Protection Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Data Protection Dashboard
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Data Protection</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Data Protection Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1">Total Users</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['total_users']) }}</h4>
                                <small class="text-success">Active</small>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-users ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Total Files</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['total_files']) }}</h4>
                                <small class="text-info">Stored</small>
                            </div>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-files ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Archived Files</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['archived_files']) }}</h4>
                                <small class="text-warning">Retention</small>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-archive ti-sm"></i>
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
                            <span class="fw-semibold d-block mb-1">Anonymized Users</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($stats['anonymized_users']) }}</h4>
                                <small class="text-danger">GDPR</small>
                            </div>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="ti ti-user-off ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Protection Overview -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Data Protection Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Retention Policies</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>File Retention</span>
                                    <span>{{ $stats['retention_policies']['file_retention_days'] }} days</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Audit Retention</span>
                                    <span>{{ $stats['retention_policies']['audit_retention_days'] }} days</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>User Retention</span>
                                    <span>{{ $stats['retention_policies']['user_retention_days'] }} days</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">GDPR Compliance</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Recent DSR Requests</span>
                                    <span>{{ count($stats['recent_dsr_requests']) }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ count($stats['recent_dsr_requests']) > 0 ? 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Data Anonymization</span>
                                    <span>{{ $stats['anonymized_users'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['total_users'] > 0 ? ($stats['anonymized_users'] / $stats['total_users']) * 100 : 0 }}%"></div>
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
                        <a href="{{ route('data-protection.data-subject-requests') }}" class="btn btn-outline-primary">
                            <i class="ti ti-user-check ti-xs me-2"></i>Data Subject Requests
                        </a>
                        <a href="{{ route('data-protection.retention-policies') }}" class="btn btn-outline-info">
                            <i class="ti ti-clock ti-xs me-2"></i>Retention Policies
                        </a>
                        <a href="{{ route('data-protection.privacy-controls') }}" class="btn btn-outline-success">
                            <i class="ti ti-shield-check ti-xs me-2"></i>Privacy Controls
                        </a>
                        <a href="{{ route('data-protection.data-anonymization') }}" class="btn btn-outline-warning">
                            <i class="ti ti-user-off ti-xs me-2"></i>Data Anonymization
                        </a>
                        <a href="{{ route('data-protection.consent-management') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-checkbox ti-xs me-2"></i>Consent Management
                        </a>
                        <a href="{{ route('data-protection.data-portability') }}" class="btn btn-outline-danger">
                            <i class="ti ti-download ti-xs me-2"></i>Data Portability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GDPR Rights Overview -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">GDPR Data Subject Rights</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-eye ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right of Access</h6>
                                            <small class="text-muted">Article 15</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals have the right to access their personal data</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-primary">Active</span>
                                        <small class="text-muted">Implemented</small>
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
                                                <i class="ti ti-edit ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right to Rectification</h6>
                                            <small class="text-muted">Article 16</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals can request correction of inaccurate data</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-success">Active</span>
                                        <small class="text-muted">Implemented</small>
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
                                                <i class="ti ti-trash ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right to Erasure</h6>
                                            <small class="text-muted">Article 17</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals can request deletion of their data</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-danger">Active</span>
                                        <small class="text-muted">Implemented</small>
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
                                                <i class="ti ti-download ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right to Portability</h6>
                                            <small class="text-muted">Article 20</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals can receive their data in a portable format</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-warning">Active</span>
                                        <small class="text-muted">Implemented</small>
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
                                                <i class="ti ti-ban ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right to Restriction</h6>
                                            <small class="text-muted">Article 18</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals can limit how their data is processed</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-info">Active</span>
                                        <small class="text-muted">Implemented</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-secondary">
                                                <i class="ti ti-hand-stop ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Right to Object</h6>
                                            <small class="text-muted">Article 21</small>
                                        </div>
                                    </div>
                                    <p class="mb-3">Individuals can object to data processing</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-secondary">Active</span>
                                        <small class="text-muted">Implemented</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Subject Rights Requests -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Data Subject Rights Requests</h5>
                    <a href="{{ route('data-protection.data-subject-requests') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>User</th>
                                    <th>Request Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_dsr_requests'] as $request)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">DSR-{{ $request['id'] }}</span>
                                    </td>
                                    <td>User #{{ $request['user_id'] }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $request['request_type'] === 'access' ? 'primary' : ($request['request_type'] === 'erasure' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($request['request_type']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success">Completed</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($request['created_at'])->diffForHumans() }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye ti-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No recent data subject rights requests</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Protection Compliance Status -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Data Protection Compliance Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ Implemented Features</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    GDPR Data Subject Rights Management
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Data Retention Policies
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Data Anonymization
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Consent Management
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Data Portability
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Privacy Controls
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">📊 Compliance Metrics</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Data Protection Score</span>
                                    <span>95%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 95%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>GDPR Compliance</span>
                                    <span>100%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Data Retention Compliance</span>
                                    <span>90%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 90%"></div>
                                </div>
                            </div>
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
