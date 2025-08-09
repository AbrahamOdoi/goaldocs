@extends('layouts.app')

@section('title', 'Security Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Security Center</h5>
                            <p class="mb-4">Monitor and manage document security, encryption, and audit logs</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/security-light.png') }}" height="140" alt="Security">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Overview -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Encrypted Files</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['encrypted_files'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ti ti-lock ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Watermarked Files</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['watermarked_files'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ti ti-brand-watermark ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Security Audits</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['security_audits'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="ti ti-file-analytics ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Security Score</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $stats['security_score'] ?? 0 }}/100</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="ti ti-shield-check ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Charts -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Security Events Timeline</h5>
                </div>
                <div class="card-body">
                    <div id="securityEventsChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Security Status</h5>
                </div>
                <div class="card-body">
                    <div id="securityStatusChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Security Audits</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Severity</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($recentAudits ?? []) as $audit)
                                <tr>
                                    <td>{{ $audit['event_type'] ?? 'Unknown Event' }}</td>
                                    <td>{{ $audit['user_name'] ?? 'System' }}</td>
                                    <td>{{ $audit['ip_address'] ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $severity = $audit['severity'] ?? 'info';
                                            $badgeClass = [
                                                'low' => 'bg-label-success',
                                                'medium' => 'bg-label-warning',
                                                'high' => 'bg-label-danger',
                                                'info' => 'bg-label-info'
                                            ][$severity] ?? 'bg-label-info';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($severity) }}</span>
                                    </td>
                                    <td>{{ $audit['created_at'] ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Encryption Keys -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Active Encryption Keys</h5>
                    <button class="btn btn-primary btn-sm">
                        <i class="ti ti-plus ti-xs me-1"></i>Generate New Key
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Key ID</th>
                                    <th>File</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($encryptionKeys ?? []) as $key)
                                <tr>
                                    <td>{{ $key['key_id'] ?? 'N/A' }}</td>
                                    <td>{{ $key['file_name'] ?? 'N/A' }}</td>
                                    <td>{{ $key['created_by'] ?? 'System' }}</td>
                                    <td>{{ $key['created_at'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-label-success">Active</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash ti-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Security Events Chart
    var securityEventsOptions = {
        series: [{
            name: 'Security Events',
            data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
        }],
        chart: {
            type: 'line',
            height: 300
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
        },
        colors: ['#dc3545']
    };

    var securityEventsChart = new ApexCharts(document.querySelector("#securityEventsChart"), securityEventsOptions);
    securityEventsChart.render();

    // Security Status Chart
    var securityStatusOptions = {
        series: [70, 20, 10],
        chart: {
            type: 'donut',
            height: 300
        },
        labels: ['Secure', 'Warning', 'Critical'],
        colors: ['#28a745', '#ffc107', '#dc3545'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var securityStatusChart = new ApexCharts(document.querySelector("#securityStatusChart"), securityStatusOptions);
    securityStatusChart.render();
});
</script>
@endpush
@endsection 