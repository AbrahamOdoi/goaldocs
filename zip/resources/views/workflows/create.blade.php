@extends('layouts.app')

@section('title', 'Create Workflow - GoalDocs')

@push('styles')
<style>
.step-item {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #f8f9fc;
}
.step-item:hover {
    border-color: #007bff;
}
.step-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.step-number {
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 0.5rem;
}
.remove-step {
    background: #dc3545;
    border: none;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Create New Workflow</h5>
                    <a href="{{ route('workflows.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-arrow-left ti-xs me-1"></i>Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('workflows.store') }}" method="POST" id="createWorkflowForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">Basic Information</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Workflow Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Workflow Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="approval" {{ old('type') == 'approval' ? 'selected' : '' }}>Approval</option>
                                        <option value="review" {{ old('type') == 'review' ? 'selected' : '' }}>Review</option>
                                        <option value="notification" {{ old('type') == 'notification' ? 'selected' : '' }}>Notification</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Workflow Steps -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-primary mb-0">Workflow Steps</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addWorkflowStep()">
                                    <i class="ti ti-plus ti-xs me-1"></i>Add Step
                                </button>
                            </div>
                            
                            <div id="workflowSteps">
                                <!-- Steps will be added here dynamically -->
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('workflows.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check ti-xs me-1"></i>Create Workflow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stepCounter = 0;

function addWorkflowStep() {
    stepCounter++;
    const stepHtml = `
        <div class="step-item" data-step="${stepCounter}">
            <div class="step-header">
                <div class="d-flex align-items-center">
                    <span class="step-number">${stepCounter}</span>
                    <strong>Step ${stepCounter}</strong>
                </div>
                <button type="button" class="remove-step" onclick="removeStep(${stepCounter})">
                    <i class="ti ti-x ti-xs"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Step Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="steps[${stepCounter}][name]" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Approver Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="steps[${stepCounter}][approver_type]" required onchange="toggleApproverFields(${stepCounter}, this.value)">
                        <option value="">Select Type</option>
                        <option value="user">Specific User</option>
                        <option value="role">User Role</option>
                        <option value="manager">Line Manager</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3 approver-user-field" id="approver-user-${stepCounter}" style="display:none;">
                    <label class="form-label">Select User</label>
                    <select class="form-select" name="steps[${stepCounter}][approver_id]">
                        <option value="">Select User</option>
                        <!-- Users will be loaded here -->
                    </select>
                </div>
                
                <div class="col-md-6 mb-3 approver-role-field" id="approver-role-${stepCounter}" style="display:none;">
                    <label class="form-label">User Role</label>
                    <input type="text" class="form-control" name="steps[${stepCounter}][approver_role]" placeholder="e.g. Manager, Admin">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Timeout (Hours) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="steps[${stepCounter}][timeout_hours]" min="1" max="168" value="24" required>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('workflowSteps').insertAdjacentHTML('beforeend', stepHtml);
    updateStepNumbers();
}

function removeStep(stepId) {
    const stepElement = document.querySelector(`[data-step="${stepId}"]`);
    if (stepElement) {
        stepElement.remove();
        updateStepNumbers();
    }
}

function updateStepNumbers() {
    const steps = document.querySelectorAll('.step-item');
    steps.forEach((step, index) => {
        const stepNumber = index + 1;
        step.querySelector('.step-number').textContent = stepNumber;
        step.querySelector('.step-header strong').textContent = `Step ${stepNumber}`;
    });
}

function toggleApproverFields(stepId, approverType) {
    const userField = document.getElementById(`approver-user-${stepId}`);
    const roleField = document.getElementById(`approver-role-${stepId}`);
    
    // Hide all fields first
    userField.style.display = 'none';
    roleField.style.display = 'none';
    
    // Show relevant field
    if (approverType === 'user') {
        userField.style.display = 'block';
    } else if (approverType === 'role') {
        roleField.style.display = 'block';
    }
}

// Add first step on page load
document.addEventListener('DOMContentLoaded', function() {
    addWorkflowStep();
});

// Form validation
document.getElementById('createWorkflowForm').addEventListener('submit', function(e) {
    const steps = document.querySelectorAll('.step-item');
    if (steps.length === 0) {
        e.preventDefault();
        alert('Please add at least one workflow step.');
        return false;
    }
});
</script>
@endpush
