@extends('layouts.app')

@section('title', 'Create New Report')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Create New Report
                </h1>
                <ul class="breadcrumb my-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('reports.index') }}">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Report Builder -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Report Builder</h5>
                </div>
                <div class="card-body">
                    <form id="reportForm" class="needs-validation" novalidate>
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Basic Information</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Report Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Please provide a report name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Report Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Report Type</option>
                                    @foreach($reportTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a report type.</div>
                            </div>
                            <div class="col-12 mt-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe what this report will contain..."></textarea>
                            </div>
                        </div>

                        <!-- Report Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Report Configuration</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="period" class="form-label">Time Period</label>
                                <select class="form-select" id="period" name="config[period]">
                                    <option value="7d">Last 7 Days</option>
                                    <option value="30d" selected>Last 30 Days</option>
                                    <option value="90d">Last 90 Days</option>
                                    <option value="1y">Last Year</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="userScope" class="form-label">User Scope</label>
                                <select class="form-select" id="userScope" name="config[user_scope]">
                                    <option value="all">All Users</option>
                                    <option value="current" selected>Current User Only</option>
                                    <option value="specific">Specific Users</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3" id="specificUsersDiv" style="display: none;">
                                <label for="specificUsers" class="form-label">Specific Users</label>
                                <select class="form-select" id="specificUsers" name="config[specific_users][]" multiple>
                                    <!-- Will be populated via AJAX -->
                                </select>
                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple users</small>
                            </div>
                        </div>

                        <!-- Scheduling -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Scheduling (Optional)</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enableSchedule" name="enable_schedule">
                                    <label class="form-check-label" for="enableSchedule">
                                        Enable Scheduled Generation
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6" id="scheduleConfig" style="display: none;">
                                <label for="scheduleType" class="form-label">Schedule Type</label>
                                <select class="form-select" id="scheduleType" name="schedule[type]">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3" id="scheduleDetails" style="display: none;">
                                <label for="scheduleTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="scheduleTime" name="schedule[time]" value="09:00">
                            </div>
                            <div class="col-md-6 mt-3" id="weeklyDay" style="display: none;">
                                <label for="scheduleDay" class="form-label">Day of Week</label>
                                <select class="form-select" id="scheduleDay" name="schedule[day]">
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3" id="monthlyDay" style="display: none;">
                                <label for="scheduleMonthDay" class="form-label">Day of Month</label>
                                <select class="form-select" id="scheduleMonthDay" name="schedule[month_day]">
                                    @for($i = 1; $i <= 28; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Report Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Report Settings</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isPublic" name="is_public">
                                    <label class="form-check-label" for="isPublic">
                                        Make Report Public
                                    </label>
                                    <small class="form-text text-muted d-block">Public reports can be viewed and generated by all users</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                    <label class="form-check-label" for="isActive">
                                        Active Report
                                    </label>
                                    <small class="form-text text-muted d-block">Inactive reports won't be generated automatically</small>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Report Preview</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div id="reportPreview">
                                            <div class="text-center text-muted py-4">
                                                <i class="ti ti-file-report ti-2x mb-2"></i>
                                                <p>Configure your report to see a preview</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left ti-xs me-1"></i>Cancel
                                    </a>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="previewReport()">
                                            <i class="ti ti-eye ti-xs me-1"></i>Preview
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-check ti-xs me-1"></i>Create Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Schedule configuration
document.getElementById('enableSchedule').addEventListener('change', function() {
    const scheduleConfig = document.getElementById('scheduleConfig');
    const scheduleDetails = document.getElementById('scheduleDetails');
    
    if (this.checked) {
        scheduleConfig.style.display = 'block';
        scheduleDetails.style.display = 'block';
    } else {
        scheduleConfig.style.display = 'none';
        scheduleDetails.style.display = 'none';
        document.getElementById('weeklyDay').style.display = 'none';
        document.getElementById('monthlyDay').style.display = 'none';
    }
});

document.getElementById('scheduleType').addEventListener('change', function() {
    const weeklyDay = document.getElementById('weeklyDay');
    const monthlyDay = document.getElementById('monthlyDay');
    
    weeklyDay.style.display = 'none';
    monthlyDay.style.display = 'none';
    
    if (this.value === 'weekly') {
        weeklyDay.style.display = 'block';
    } else if (this.value === 'monthly') {
        monthlyDay.style.display = 'block';
    }
});

// User scope configuration
document.getElementById('userScope').addEventListener('change', function() {
    const specificUsersDiv = document.getElementById('specificUsersDiv');
    
    if (this.value === 'specific') {
        specificUsersDiv.style.display = 'block';
        loadUsers();
    } else {
        specificUsersDiv.style.display = 'none';
    }
});

// Load users for specific user selection
function loadUsers() {
    fetch('{{ route("users.index") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('specificUsers');
                select.innerHTML = '';
                
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Failed to load users:', error);
        });
}

// Report type change handler
document.getElementById('type').addEventListener('change', function() {
    updatePreview();
});

// Update preview based on form data
function updatePreview() {
    const type = document.getElementById('type').value;
    const name = document.getElementById('name').value;
    const description = document.getElementById('description').value;
    const period = document.getElementById('period').value;
    
    const preview = document.getElementById('reportPreview');
    
    if (!type) {
        preview.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="ti ti-file-report ti-2x mb-2"></i>
                <p>Configure your report to see a preview</p>
            </div>
        `;
        return;
    }
    
    const typeLabels = {
        'document_usage': 'Document Usage Analytics',
        'processing': 'Processing Analytics',
        'storage': 'Storage Analytics',
        'user_activity': 'User Activity Analytics'
    };
    
    preview.innerHTML = `
        <div class="d-flex align-items-center mb-3">
            <div class="avatar avatar-sm me-3">
                <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti ti-file-report ti-xs"></i>
                </span>
            </div>
            <div>
                <h6 class="mb-0">${name || 'Untitled Report'}</h6>
                <small class="text-muted">${typeLabels[type] || type}</small>
            </div>
        </div>
        ${description ? `<p class="text-muted mb-3">${description}</p>` : ''}
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">Time Period:</small>
                <p class="mb-2">${period}</p>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Report Type:</small>
                <p class="mb-2">${typeLabels[type] || type}</p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="ti ti-info-circle ti-xs me-1"></i>
            This report will include comprehensive analytics data for ${typeLabels[type] || type.toLowerCase()}.
        </div>
    `;
}

// Preview report
function previewReport() {
    const formData = new FormData(document.getElementById('reportForm'));
    const data = Object.fromEntries(formData);
    
    // Validate required fields
    if (!data.name || !data.type) {
        showAlert('error', 'Please fill in all required fields before previewing');
        return;
    }
    
    // Show preview modal
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
    
    // Update preview content
    updatePreview();
}

// Form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key.includes('[')) {
            // Handle nested keys like config[period]
            const parts = key.match(/(\w+)\[(\w+)\]/);
            if (parts) {
                if (!data[parts[1]]) data[parts[1]] = {};
                data[parts[1]][parts[2]] = value;
            }
        } else {
            data[key] = value;
        }
    }
    
    // Handle checkboxes
    data.is_public = document.getElementById('isPublic').checked;
    data.is_active = document.getElementById('isActive').checked;
    
    // Handle schedule
    if (document.getElementById('enableSchedule').checked) {
        data.schedule = {
            type: data.schedule?.type || 'daily',
            time: data.schedule?.time || '09:00'
        };
        
        if (data.schedule.type === 'weekly') {
            data.schedule.day = data.schedule?.day || 'monday';
        } else if (data.schedule.type === 'monthly') {
            data.schedule.month_day = data.schedule?.month_day || '1';
        }
    }
    
    // Remove temporary data
    delete data.enable_schedule;
    
    // Submit form
    fetch('{{ route("reports.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Report created successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("reports.index") }}';
            }, 1500);
        } else {
            showAlert('error', data.message || 'Failed to create report');
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = data.errors[field][0];
                        }
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Create failed:', error);
        showAlert('error', 'Failed to create report');
    });
});

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

// Update preview on form changes
document.querySelectorAll('#reportForm input, #reportForm select, #reportForm textarea').forEach(element => {
    element.addEventListener('change', updatePreview);
    element.addEventListener('input', updatePreview);
});
</script>
@endpush
