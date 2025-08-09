@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Analytics Dashboard</h5>
                            <p class="mb-4">Comprehensive insights into your document management system</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="Analytics">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total Files</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $analytics['overview']['total_files'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="ti ti-files ti-sm"></i>
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
                            <p class="card-text">Active Users</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $analytics['overview']['active_users'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ti ti-users ti-sm"></i>
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
                            <p class="card-text">Storage Used</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $analytics['overview']['total_storage_gb'] ?? 0 }} GB</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ti ti-database ti-sm"></i>
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
                            <p class="card-text">Recent Uploads</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0">{{ $analytics['overview']['recent_uploads'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="ti ti-upload ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">File Upload Trends</h5>
                </div>
                <div class="card-body">
                    <div id="uploadTrendsChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">File Types Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="fileTypesChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Analytics -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Performers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Files</th>
                                    <th>Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($analytics['top_performers']['uploaders'] ?? []) as $performer)
                                <tr>
                                    <td>{{ $performer['name'] ?? 'Unknown' }}</td>
                                    <td>{{ $performer['files_count'] ?? 0 }}</td>
                                    <td>{{ $performer['type'] ?? 'Uploader' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                    <div class="timeline-vertical">
                        @foreach(($analytics['recent_activity'] ?? []) as $activity)
                        <div class="timeline-item">
                            <span class="timeline-point"></span>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $activity['title'] ?? 'Activity' }}</h6>
                                <small class="text-muted">{{ $activity['time'] ?? 'Recently' }}</small>
                            </div>
                        </div>
                        @endforeach
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
    // Upload Trends Chart
    var uploadTrendsOptions = {
        series: [{
            name: 'Uploads',
            data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
        }],
        chart: {
            type: 'area',
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
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    var uploadTrendsChart = new ApexCharts(document.querySelector("#uploadTrendsChart"), uploadTrendsOptions);
    uploadTrendsChart.render();

    // File Types Chart
    var fileTypesOptions = {
        series: [44, 55, 13, 43, 22],
        chart: {
            type: 'donut',
            height: 300
        },
        labels: ['Documents', 'Images', 'Videos', 'Audio', 'Others'],
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

    var fileTypesChart = new ApexCharts(document.querySelector("#fileTypesChart"), fileTypesOptions);
    fileTypesChart.render();
});
</script>
@endpush

@push('styles')
<style>
.timeline-vertical {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-point {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #7367f0;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endpush
@endsection 