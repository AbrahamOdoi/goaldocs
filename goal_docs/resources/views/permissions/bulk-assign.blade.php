@extends('layouts.app')

@section('title', 'Bulk Permission Assignment')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-shield me-2"></i>Bulk Permission Assignment
                    </h5>
                    <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Files
                    </a>
                </div>
                <div class="card-body">
                    <form id="bulkPermissionForm">
                        @csrf
                        
                        <!-- Step 1: Select Resources -->
                        <div class="step" id="step1">
                            <h6 class="mb-3">Step 1: Select Resources</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Resource Type</label>
                                    <select class="form-select" id="resourceType" name="resource_type" required>
                                        <option value="">Choose resource type...</option>
                                        <option value="folder">Folders</option>
                                        <option value="file">Files</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Search Resources</label>
                                    <input type="text" class="form-control" id="resourceSearch" placeholder="Search by name...">
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" id="resourcesTable">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllResources" class="form-check-input">
                                            </th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Path</th>
                                            <th>Current Permissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                Select a resource type to load resources
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" id="nextToStep2" disabled>
                                    Next: Select Assignables <i class="bx bx-right-arrow-alt ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Select Assignables -->
                        <div class="step d-none" id="step2">
                            <h6 class="mb-3">Step 2: Select Assignables</h6>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="user" id="bulkAssignUser">
                                        <label class="form-check-label" for="bulkAssignUser">
                                            <i class="bx bx-user me-1"></i>Users
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="position" id="bulkAssignPosition">
                                        <label class="form-check-label" for="bulkAssignPosition">
                                            <i class="bx bx-briefcase me-1"></i>Positions
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="department" id="bulkAssignDepartment">
                                        <label class="form-check-label" for="bulkAssignDepartment">
                                            <i class="bx bx-buildings me-1"></i>Departments
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" id="assignablesTable">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllAssignables" class="form-check-input">
                                            </th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Select an assignable type to load options
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" id="backToStep1">
                                    <i class="bx bx-left-arrow-alt me-1"></i> Back: Select Resources
                                </button>
                                <button type="button" class="btn btn-primary" id="nextToStep3" disabled>
                                    Next: Set Permissions <i class="bx bx-right-arrow-alt ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Set Permissions -->
                        <div class="step d-none" id="step3">
                            <h6 class="mb-3">Step 3: Set Permissions</h6>
                            
                            <!-- Permission Templates -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Quick Templates</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary template-btn" data-template="read_only">
                                            <i class="bx bx-eye me-1"></i>Read Only
                                        </button>
                                        <button type="button" class="btn btn-outline-primary template-btn" data-template="viewer">
                                            <i class="bx bx-download me-1"></i>Viewer
                                        </button>
                                        <button type="button" class="btn btn-outline-primary template-btn" data-template="editor">
                                            <i class="bx bx-edit me-1"></i>Editor
                                        </button>
                                        <button type="button" class="btn btn-outline-primary template-btn" data-template="manager">
                                            <i class="bx bx-cog me-1"></i>Manager
                                        </button>
                                        <button type="button" class="btn btn-outline-primary template-btn" data-template="admin">
                                            <i class="bx bx-shield me-1"></i>Admin
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Permissions -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Custom Permissions</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[view]" id="bulkPermView">
                                                <label class="form-check-label" for="bulkPermView">
                                                    <i class="bx bx-show me-1"></i>View
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[download]" id="bulkPermDownload">
                                                <label class="form-check-label" for="bulkPermDownload">
                                                    <i class="bx bx-download me-1"></i>Download
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[edit]" id="bulkPermEdit">
                                                <label class="form-check-label" for="bulkPermEdit">
                                                    <i class="bx bx-edit me-1"></i>Edit
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[upload]" id="bulkPermUpload">
                                                <label class="form-check-label" for="bulkPermUpload">
                                                    <i class="bx bx-upload me-1"></i>Upload
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[delete]" id="bulkPermDelete">
                                                <label class="form-check-label" for="bulkPermDelete">
                                                    <i class="bx bx-trash me-1"></i>Delete
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[manage]" id="bulkPermManage">
                                                <label class="form-check-label" for="bulkPermManage">
                                                    <i class="bx bx-shield me-1"></i>Manage
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Assignment Summary</h6>
                                        <p class="mb-0">
                                            <strong id="summaryResources">0</strong> resources will be assigned to 
                                            <strong id="summaryAssignables">0</strong> assignables with the selected permissions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" id="backToStep2">
                                    <i class="bx bx-left-arrow-alt me-1"></i> Back: Select Assignables
                                </button>
                                <button type="button" class="btn btn-success" id="executeBulkAssignment">
                                    <i class="bx bx-check me-1"></i> Execute Assignment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkPermissionForm');
    const resourceType = document.getElementById('resourceType');
    const resourceSearch = document.getElementById('resourceSearch');
    const resourcesTable = document.getElementById('resourcesTable');
    const assignablesTable = document.getElementById('assignablesTable');
    const assignableTypeRadios = document.querySelectorAll('.assignable-type');
    const templateBtns = document.querySelectorAll('.template-btn');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    
    let selectedResources = [];
    let selectedAssignables = [];

    // Permission templates
    const templates = {
        'read_only': { view: true, download: false, edit: false, upload: false, delete: false, manage: false },
        'viewer': { view: true, download: true, edit: false, upload: false, delete: false, manage: false },
        'editor': { view: true, download: true, edit: true, upload: true, delete: false, manage: false },
        'manager': { view: true, download: true, edit: true, upload: true, delete: true, manage: false },
        'admin': { view: true, download: true, edit: true, upload: true, delete: true, manage: true }
    };

    // Event listeners
    resourceType.addEventListener('change', loadResources);
    resourceSearch.addEventListener('input', debounce(loadResources, 300));
    assignableTypeRadios.forEach(radio => {
        radio.addEventListener('change', loadAssignables);
    });
    templateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            applyTemplate(this.dataset.template);
        });
    });

    // Step navigation
    document.getElementById('nextToStep2').addEventListener('click', () => showStep(2));
    document.getElementById('backToStep1').addEventListener('click', () => showStep(1));
    document.getElementById('nextToStep3').addEventListener('click', () => showStep(3));
    document.getElementById('backToStep2').addEventListener('click', () => showStep(2));
    document.getElementById('executeBulkAssignment').addEventListener('click', executeBulkAssignment);

    // Load resources
    function loadResources() {
        if (!resourceType.value) return;
        
        fetch(`/api/resources/${resourceType.value}?search=${resourceSearch.value}`)
            .then(response => response.json())
            .then(data => {
                updateResourcesTable(data);
            })
            .catch(error => {
                console.error('Error loading resources:', error);
            });
    }

    // Load assignables
    function loadAssignables() {
        const selectedType = document.querySelector('input[name="assignable_type"]:checked');
        if (!selectedType) return;
        
        fetch(`/api/assignables/${selectedType.value}`)
            .then(response => response.json())
            .then(data => {
                updateAssignablesTable(data);
            })
            .catch(error => {
                console.error('Error loading assignables:', error);
            });
    }

    // Update resources table
    function updateResourcesTable(resources) {
        const tbody = resourcesTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        resources.forEach(resource => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" class="form-check-input resource-checkbox" value="${resource.id}">
                </td>
                <td>${resource.name}</td>
                <td><span class="badge bg-secondary">${resource.type}</span></td>
                <td>${resource.path || '/'}</td>
                <td><span class="badge bg-info">${resource.permission_count || 0} assignments</span></td>
            `;
            tbody.appendChild(row);
        });
        
        // Add event listeners to checkboxes
        document.querySelectorAll('.resource-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateResourceSelection);
        });
    }

    // Update assignables table
    function updateAssignablesTable(assignables) {
        const tbody = assignablesTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        assignables.forEach(assignable => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" class="form-check-input assignable-checkbox" value="${assignable.id}">
                </td>
                <td>${assignable.name}</td>
                <td><span class="badge bg-primary">${assignable.type}</span></td>
                <td>${assignable.description || '-'}</td>
            `;
            tbody.appendChild(row);
        });
        
        // Add event listeners to checkboxes
        document.querySelectorAll('.assignable-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateAssignableSelection);
        });
    }

    // Update resource selection
    function updateResourceSelection() {
        selectedResources = Array.from(document.querySelectorAll('.resource-checkbox:checked'))
            .map(cb => cb.value);
        
        document.getElementById('nextToStep2').disabled = selectedResources.length === 0;
    }

    // Update assignable selection
    function updateAssignableSelection() {
        selectedAssignables = Array.from(document.querySelectorAll('.assignable-checkbox:checked'))
            .map(cb => cb.value);
        
        document.getElementById('nextToStep3').disabled = selectedAssignables.length === 0;
        updateSummary();
    }

    // Apply permission template
    function applyTemplate(templateName) {
        const template = templates[templateName];
        if (!template) return;

        permissionCheckboxes.forEach(checkbox => {
            const permission = checkbox.name.match(/\[(.*?)\]/)[1];
            checkbox.checked = template[permission] || false;
        });
        
        // Update button states
        templateBtns.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-template="${templateName}"]`).classList.add('active');
    }

    // Update summary
    function updateSummary() {
        document.getElementById('summaryResources').textContent = selectedResources.length;
        document.getElementById('summaryAssignables').textContent = selectedAssignables.length;
    }

    // Show step
    function showStep(stepNumber) {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.add('d-none');
        });
        document.getElementById(`step${stepNumber}`).classList.remove('d-none');
    }

    // Execute bulk assignment
    function executeBulkAssignment() {
        const permissions = {};
        permissionCheckboxes.forEach(checkbox => {
            const permission = checkbox.name.match(/\[(.*?)\]/)[1];
            permissions[permission] = checkbox.checked;
        });

        const data = {
            resource_ids: selectedResources,
            resource_type: resourceType.value,
            assignable_type: document.querySelector('input[name="assignable_type"]:checked').value,
            assignable_ids: selectedAssignables,
            permissions: permissions
        };

        fetch('/permissions/bulk-assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(`Successfully assigned permissions to ${result.assigned_count} resources!`);
                window.location.href = '/files';
            } else {
                alert('Error: ' + (result.message || 'Failed to assign permissions'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning permissions.');
        });
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
@endsection
