<!-- Permission Assignment Modal -->
<div class="modal fade" id="permissionAssignmentModal" tabindex="-1" aria-labelledby="permissionAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionAssignmentModalLabel">
                    <i class="bx bx-shield me-2"></i>Manage Access Permissions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionAssignmentForm">
                    @csrf
                    <input type="hidden" id="resourceId" name="resource_id">
                    <input type="hidden" id="resourceType" name="resource_type">
                    
                    <!-- Resource Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong id="resourceName">Resource Name</strong>
                                <span class="badge bg-secondary ms-2" id="resourceTypeBadge">Folder</span>
                            </div>
                        </div>
                    </div>

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

                    <!-- Assignable Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Assign to</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="user" id="assignUser">
                                        <label class="form-check-label" for="assignUser">
                                            <i class="bx bx-user me-1"></i>Individual User
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="position" id="assignPosition">
                                        <label class="form-check-label" for="assignPosition">
                                            <i class="bx bx-briefcase me-1"></i>Position
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input assignable-type" type="radio" name="assignable_type" value="department" id="assignDepartment">
                                        <label class="form-check-label" for="assignDepartment">
                                            <i class="bx bx-buildings me-1"></i>Department
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignable Selection Dropdown -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Select Assignable</label>
                            <select class="form-select" id="assignableSelect" name="assignable_id" required>
                                <option value="">Choose...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Custom Permissions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Custom Permissions</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[view]" id="permView">
                                        <label class="form-check-label" for="permView">
                                            <i class="bx bx-show me-1"></i>View
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[download]" id="permDownload">
                                        <label class="form-check-label" for="permDownload">
                                            <i class="bx bx-download me-1"></i>Download
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[edit]" id="permEdit">
                                        <label class="form-check-label" for="permEdit">
                                            <i class="bx bx-edit me-1"></i>Edit
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[upload]" id="permUpload">
                                        <label class="form-check-label" for="permUpload">
                                            <i class="bx bx-upload me-1"></i>Upload
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[delete]" id="permDelete">
                                        <label class="form-check-label" for="permDelete">
                                            <i class="bx bx-trash me-1"></i>Delete
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[manage]" id="permManage">
                                        <label class="form-check-label" for="permManage">
                                            <i class="bx bx-shield me-1"></i>Manage
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="permissionNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="permissionNotes" name="notes" rows="2" placeholder="Add any notes about this permission assignment..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePermissionBtn">
                    <i class="bx bx-save me-1"></i>Save Permission
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('permissionAssignmentModal');
    const form = document.getElementById('permissionAssignmentForm');
    const assignableTypeRadios = document.querySelectorAll('.assignable-type');
    const assignableSelect = document.getElementById('assignableSelect');
    const templateBtns = document.querySelectorAll('.template-btn');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const saveBtn = document.getElementById('savePermissionBtn');

    // Permission templates
    const templates = {
        'read_only': { view: true, download: false, edit: false, upload: false, delete: false, manage: false },
        'viewer': { view: true, download: true, edit: false, upload: false, delete: false, manage: false },
        'editor': { view: true, download: true, edit: true, upload: true, delete: false, manage: false },
        'manager': { view: true, download: true, edit: true, upload: true, delete: true, manage: false },
        'admin': { view: true, download: true, edit: true, upload: true, delete: true, manage: true }
    };

    // Handle assignable type change
    assignableTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                loadAssignables(this.value);
            }
        });
    });

    // Handle template selection
    templateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const template = this.dataset.template;
            applyTemplate(template);
            
            // Update button states
            templateBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Handle save button
    saveBtn.addEventListener('click', function() {
        savePermission();
    });

    // Load assignables based on type
    function loadAssignables(type) {
        assignableSelect.innerHTML = '<option value="">Loading...</option>';
        
        fetch(`/api/assignables/${type}`)
            .then(response => response.json())
            .then(data => {
                assignableSelect.innerHTML = '<option value="">Choose...</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    assignableSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading assignables:', error);
                assignableSelect.innerHTML = '<option value="">Error loading data</option>';
            });
    }

    // Apply permission template
    function applyTemplate(templateName) {
        const template = templates[templateName];
        if (!template) return;

        permissionCheckboxes.forEach(checkbox => {
            const permission = checkbox.name.match(/\[(.*?)\]/)[1];
            checkbox.checked = template[permission] || false;
        });
    }

    // Save permission
    function savePermission() {
        const formData = new FormData(form);
        const data = {
            resource_id: formData.get('resource_id'),
            resource_type: formData.get('resource_type'),
            assignable_type: formData.get('assignable_type'),
            assignable_id: formData.get('assignable_id'),
            permissions: {},
            notes: formData.get('notes')
        };

        // Collect permissions
        permissionCheckboxes.forEach(checkbox => {
            const permission = checkbox.name.match(/\[(.*?)\]/)[1];
            data.permissions[permission] = checkbox.checked;
        });

        // Validate
        if (!data.assignable_type || !data.assignable_id) {
            alert('Please select an assignable type and item.');
            return;
        }

        // Submit
        fetch('/permissions/assign', {
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
                alert('Permission assigned successfully!');
                modal.hide();
                // Refresh the page or update the permissions list
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to assign permission'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning permission.');
        });
    }

    // Public function to open modal with resource data
    window.openPermissionModal = function(resourceId, resourceType, resourceName) {
        document.getElementById('resourceId').value = resourceId;
        document.getElementById('resourceType').value = resourceType;
        document.getElementById('resourceName').textContent = resourceName;
        document.getElementById('resourceTypeBadge').textContent = resourceType.charAt(0).toUpperCase() + resourceType.slice(1);
        
        // Reset form
        form.reset();
        templateBtns.forEach(btn => btn.classList.remove('active'));
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    };
});
</script>
