@extends('layouts.app')

@section('title', $workflow->name . ' - Workflow Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="fw-bold text-primary mb-2">{{ $workflow->name }}</h4>
                            <p class="text-muted mb-3">{{ $workflow->description ?: 'No description provided' }}</p>
                            <div class="d-flex gap-3">
                                <span class="badge bg-label-info">{{ ucfirst($workflow->type) }}</span>
                                <span class="badge {{ $workflow->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $workflow->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <small class="text-muted">Created by {{ $workflow->creator->name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('workflows.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left ti-xs me-1"></i>Back to Dashboard
                            </a>
                            <a href="{{ route('workflows.index') }}" class="btn btn-outline-primary">
                                <i class="ti ti-list ti-xs me-1"></i>All Workflows
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar avatar-md mx-auto mb-2">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-file-check ti-md"></i>
                                </span>
                            </div>
                            <h5 class="mb-1">{{ $stats['total_instances'] ?? 0 }}</h5>
                            <p class="mb-0 text-muted">Total Instances</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar avatar-md mx-auto mb-2">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-clock ti-md"></i>
                                </span>
                            </div>
                            <h5 class="mb-1">{{ $stats['active_instances'] ?? 0 }}</h5>
                            <p class="mb-0 text-muted">Active Instances</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar avatar-md mx-auto mb-2">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-check ti-md"></i>
                                </span>
                            </div>
                            <h5 class="mb-1">{{ $stats['completed_instances'] ?? 0 }}</h5>
                            <p class="mb-0 text-muted">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar avatar-md mx-auto mb-2">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-x ti-md"></i>
                                </span>
                            </div>
                            <h5 class="mb-1">{{ $stats['rejected_instances'] ?? 0 }}</h5>
                            <p class="mb-0 text-muted">Rejected</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Steps -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Workflow Steps</h5>
                        </div>
                        <div class="card-body">
                            @forelse($workflow->steps ?? [] as $step)
                            <div class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        {{ $step->step_order ?? $loop->iteration }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $step->name ?? 'Step ' . $loop->iteration }}</h6>
                                    <p class="text-muted mb-1 small">{{ $step->description ?? 'No description' }}</p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-label-info">{{ ucfirst($step->approver_type ?? 'user') }}</span>
                                        @if($step->timeout_hours)
                                            <span class="badge bg-label-warning">{{ $step->timeout_hours }}h timeout</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4">
                                <i class="ti ti-list-details ti-lg text-muted mb-2"></i>
                                <p class="text-muted mb-0">No steps defined</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Instances -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Instances</h5>
                        </div>
                        <div class="card-body">
                            @forelse($instances->take(5) as $instance)
                            <div class="d-flex align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $instance->status == 'completed' ? 'success' : ($instance->status == 'rejected' ? 'danger' : 'primary') }}">
                                        <i class="ti ti-{{ $instance->status == 'completed' ? 'check' : ($instance->status == 'rejected' ? 'x' : 'clock') }} ti-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $instance->file->name ?? 'Unknown File' }}</h6>
                                    <p class="text-muted mb-1 small">
                                        Initiated by {{ $instance->initiator->name ?? 'Unknown' }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-label-{{ $instance->status == 'completed' ? 'success' : ($instance->status == 'rejected' ? 'danger' : 'primary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $instance->status)) }}
                                        </span>
                                        <small class="text-muted">{{ $instance->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4">
                                <i class="ti ti-file-x ti-lg text-muted mb-2"></i>
                                <p class="text-muted mb-0">No instances yet</p>
                                <small class="text-muted">Instances will appear when workflows are started</small>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Instances Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">All Workflow Instances</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Initiated By</th>
                                    <th>Current Step</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($instances as $instance)
                                <tr>
                                    <td>{{ $instance->file->name ?? 'N/A' }}</td>
                                    <td>{{ $instance->initiator->name ?? 'Unknown' }}</td>
                                    <td>Step {{ $instance->current_step ?? 1 }}</td>
                                    <td>
                                        @php
                                            $status = $instance->status ?? 'in_progress';
                                            $badgeClass = [
                                                'pending' => 'bg-label-warning',
                                                'in_progress' => 'bg-label-primary',
                                                'completed' => 'bg-label-success',
                                                'rejected' => 'bg-label-danger'
                                            ][$status] ?? 'bg-label-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    </td>
                                    <td>{{ $instance->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewWorkflowInstance({{ $instance->id }})">
                                            <i class="ti ti-eye ti-xs"></i>
                                        </button>
                                        @if($instance->status == 'in_progress')
                                        <button class="btn btn-sm btn-outline-success" onclick="approveWorkflowStep({{ $instance->id }})">
                                            <i class="ti ti-check ti-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="rejectWorkflowStep({{ $instance->id }})">
                                            <i class="ti ti-x ti-xs"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-x ti-lg text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No workflow instances</p>
                                            <small class="text-muted">Instances will appear when this workflow is used</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($instances->hasPages())
                    <div class="mt-3">
                        {{ $instances->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Reuse the same functions from dashboard
function viewWorkflowInstance(instanceId) {
    window.location.href = "{{ route('workflows.instance.show', ':id') }}".replace(':id', instanceId);
}

function approveWorkflowStep(instanceId) {
    if (!confirm('Are you sure you want to approve this workflow step?')) return;
    
    const comment = prompt('Add a comment (optional):');
    
    fetch("{{ route('workflows.instance.approve', ':id') }}".replace(':id', instanceId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: comment || '' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Workflow step approved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to approve step'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error approving workflow step');
    });
}

function rejectWorkflowStep(instanceId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    fetch("{{ route('workflows.instance.reject', ':id') }}".replace(':id', instanceId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Workflow step rejected successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to reject step'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rejecting workflow step');
    });
}
</script>
@endpush
