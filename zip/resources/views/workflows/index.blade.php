@extends('layouts.app')

@section('title', 'Workflows - GoalDocs')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Workflows</h4>
                            <p class="text-muted mb-0">Manage and monitor your document approval workflows</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('workflows.dashboard') }}" class="btn btn-outline-primary">
                                <i class="ti ti-dashboard ti-xs me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus ti-xs me-1"></i>Create Workflow
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflows List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">All Workflows</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Steps</th>
                                    <th>Creator</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $workflow)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $workflow->name }}</h6>
                                            @if($workflow->description)
                                                <small class="text-muted">{{ Str::limit($workflow->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ ucfirst($workflow->type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $workflow->steps->count() }} steps</span>
                                    </td>
                                    <td>{{ $workflow->creator->name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="badge {{ $workflow->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ $workflow->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $workflow->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical ti-xs"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('workflows.show', $workflow) }}">
                                                        <i class="ti ti-eye ti-xs me-1"></i>View Details
                                                    </a>
                                                </li>
                                                @if(Auth::user()->is_admin || $workflow->created_by === Auth::id())
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="toggleWorkflowStatus({{ $workflow->id }}, {{ $workflow->is_active ? 'false' : 'true' }})">
                                                        <i class="ti ti-{{ $workflow->is_active ? 'pause' : 'play' }} ti-xs me-1"></i>
                                                        {{ $workflow->is_active ? 'Deactivate' : 'Activate' }}
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteWorkflow({{ $workflow->id }})">
                                                        <i class="ti ti-trash ti-xs me-1"></i>Delete
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-x ti-lg text-muted mb-3"></i>
                                            <h6 class="text-muted mb-2">No workflows found</h6>
                                            <p class="text-muted mb-3">Create your first workflow to get started</p>
                                            <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus ti-xs me-1"></i>Create Workflow
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($workflows->hasPages())
                    <div class="mt-3">
                        {{ $workflows->links() }}
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
function toggleWorkflowStatus(workflowId, isActive) {
    const action = isActive ? 'activate' : 'deactivate';
    if (!confirm(`Are you sure you want to ${action} this workflow?`)) return;
    
    // This would typically call an API endpoint to toggle the status
    // For now, just show a message
    alert(`Workflow ${action} functionality would be implemented here`);
}

function deleteWorkflow(workflowId) {
    if (!confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) return;
    
    // This would typically call an API endpoint to delete the workflow
    // For now, just show a message
    alert('Workflow deletion functionality would be implemented here');
}
</script>
@endpush
