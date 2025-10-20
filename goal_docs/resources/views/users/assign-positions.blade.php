@extends('layouts.app')

@section('title', 'Assign Positions')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assign Positions to {{ $user->name }}</h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Users
                    </a>
                </div>
                <div class="card-body">
                    <form id="assignPositionsForm">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">User Information</label>
                                <div class="border rounded p-3 bg-light">
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Current Positions</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($user->positions->count() > 0)
                                        @foreach($user->positions as $position)
                                            <span class="badge {{ $position->pivot->is_primary ? 'bg-primary' : 'bg-secondary' }} me-1 mb-1">
                                                {{ $position->name }}
                                                @if($position->pivot->is_primary)
                                                    <small>(Primary)</small>
                                                @endif
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No positions assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">Available Positions</label>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">Select</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Level</th>
                                                <th width="100">Primary</th>
                                                <th width="120">Start Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($availablePositions as $position)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input position-checkbox" 
                                                                   type="checkbox" 
                                                                   name="positions[{{ $position->id }}][selected]"
                                                                   value="1"
                                                                   data-position-id="{{ $position->id }}"
                                                                   {{ $user->positions->contains($position->id) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $position->name }}</strong>
                                                        @if($user->positions->contains($position->id))
                                                            <span class="badge bg-success ms-2">Current</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $position->department->name }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ ucfirst($position->level) }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input primary-radio" 
                                                                   type="radio" 
                                                                   name="primary_position" 
                                                                   value="{{ $position->id }}"
                                                                   {{ $user->positions->contains($position->id) && $user->positions->find($position->id)->pivot->is_primary ? 'checked' : '' }}
                                                                   {{ !$user->positions->contains($position->id) ? 'disabled' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="date" 
                                                               class="form-control form-control-sm start-date-input" 
                                                               name="positions[{{ $position->id }}][start_date]"
                                                               value="{{ $user->positions->contains($position->id) ? $user->positions->find($position->id)->pivot->start_date : date('Y-m-d') }}"
                                                               {{ !$user->positions->contains($position->id) ? 'disabled' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Save Position Assignments
                                    </button>
                                </div>
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
    const form = document.getElementById('assignPositionsForm');
    const checkboxes = document.querySelectorAll('.position-checkbox');
    const primaryRadios = document.querySelectorAll('.primary-radio');
    const startDateInputs = document.querySelectorAll('.start-date-input');

    // Handle checkbox changes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const positionId = this.dataset.positionId;
            const primaryRadio = document.querySelector(`input[name="primary_position"][value="${positionId}"]`);
            const startDateInput = document.querySelector(`input[name="positions[${positionId}][start_date]"]`);
            
            if (this.checked) {
                primaryRadio.disabled = false;
                startDateInput.disabled = false;
            } else {
                primaryRadio.disabled = true;
                primaryRadio.checked = false;
                startDateInput.disabled = true;
            }
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const positions = [];
        
        // Collect selected positions
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const positionId = checkbox.dataset.positionId;
                const startDate = document.querySelector(`input[name="positions[${positionId}][start_date]"]`).value;
                const isPrimary = document.querySelector(`input[name="primary_position"][value="${positionId}"]`).checked;
                
                positions.push({
                    position_id: positionId,
                    start_date: startDate,
                    is_primary: isPrimary
                });
            }
        });

        // Validate at least one position is selected
        if (positions.length === 0) {
            alert('Please select at least one position.');
            return;
        }

        // Validate exactly one primary position
        const primaryCount = positions.filter(p => p.is_primary).length;
        if (primaryCount !== 1) {
            alert('Please select exactly one primary position.');
            return;
        }

        // Submit the form
        fetch('{{ route("hierarchy.positions.assign-multiple") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: formData.get('user_id'),
                positions: positions
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Positions assigned successfully!');
                window.location.href = '{{ route("users.index") }}';
            } else {
                alert('Error: ' + (data.message || 'Failed to assign positions'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning positions.');
        });
    });
});
</script>
@endsection
