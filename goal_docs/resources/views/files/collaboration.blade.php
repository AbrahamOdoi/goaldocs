@extends('layouts.app')

@section('title', 'Collaboration - ' . $file->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="ti ti-users me-2"></i>
                        Collaboration
                    </h4>
                    <p class="text-muted mb-0">
                        Collaborating on: <strong>{{ $file->name }}</strong>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Back to Files
                    </a>
                    <a href="{{ route('files.preview', $file) }}" class="btn btn-primary" target="_blank">
                        <i class="ti ti-eye me-1"></i>
                        Preview Document
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Collaboration Area -->
                <div class="col-lg-8">
                    <!-- Comments Section -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-message-circle me-2"></i>
                                Comments & Discussion
                            </h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCommentModal">
                                <i class="ti ti-plus me-1"></i>
                                Add Comment
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="commentsContainer">
                                @if(isset($comments) && count($comments) > 0)
                                    @foreach($comments as $comment)
                                        <div class="comment-item border-bottom pb-3 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($comment['user']['name'], 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h6 class="mb-0">{{ $comment['user']['name'] }}</h6>
                                                        <small class="text-muted">{{ $comment['created_at'] }}</small>
                                                    </div>
                                                    <p class="mb-2">{{ $comment['content'] }}</p>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="replyToComment({{ $comment['id'] }})">
                                                            <i class="ti ti-reply me-1"></i>
                                                            Reply
                                                        </button>
                                                        @if($comment['can_edit'])
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editComment({{ $comment['id'] }})">
                                                                <i class="ti ti-edit me-1"></i>
                                                                Edit
                                                            </button>
                                                        @endif
                                                        @if($comment['can_delete'])
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteComment({{ $comment['id'] }})">
                                                                <i class="ti ti-trash me-1"></i>
                                                                Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="ti ti-message-circle text-muted display-4 mb-3"></i>
                                        <h5 class="text-muted">No comments yet</h5>
                                        <p class="text-muted">Be the first to start the discussion!</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCommentModal">
                                            <i class="ti ti-plus me-1"></i>
                                            Add First Comment
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Activity Feed -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-activity me-2"></i>
                                Recent Activity
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="activityFeed">
                                @if(isset($activities) && count($activities) > 0)
                                    @foreach($activities as $activity)
                                        <div class="activity-item d-flex align-items-center py-2">
                                            <div class="avatar me-3">
                                                <span class="avatar-initial rounded-circle bg-label-info">
                                                    {{ substr($activity['user']['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-medium">{{ $activity['user']['name'] }}</span>
                                                <span class="text-muted">{{ $activity['description'] }}</span>
                                                <small class="text-muted d-block">{{ $activity['timestamp'] }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-3">
                                        <i class="ti ti-activity text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No recent activity</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Document Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-file me-2"></i>
                                Document Info
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Name:</strong>
                                <p class="mb-0">{{ $file->name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Type:</strong>
                                <p class="mb-0">{{ $file->mime_type }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Size:</strong>
                                <p class="mb-0">{{ $file->human_size }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Uploaded:</strong>
                                <p class="mb-0">{{ $file->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <div class="mb-0">
                                <strong>Status:</strong>
                                <span class="badge bg-label-success">Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-users me-2"></i>
                                Active Users
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="activeUsers">
                                @if(isset($activeUsers) && count($activeUsers) > 0)
                                    @foreach($activeUsers as $user)
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($user['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fw-medium">{{ $user['name'] }}</p>
                                                <small class="text-muted">Last seen: {{ $user['last_seen'] }}</small>
                                            </div>
                                            <span class="badge bg-label-success">Online</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-3">
                                        <i class="ti ti-users text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No active users</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Document Lock Status -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-lock me-2"></i>
                                Document Lock
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="lockStatus">
                                @if(isset($lockInfo) && $lockInfo)
                                    <div class="alert alert-warning">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-lock me-2"></i>
                                            <div>
                                                <strong>Document Locked</strong>
                                                <p class="mb-0">By: {{ $lockInfo['user_name'] }}</p>
                                                <small>Expires: {{ $lockInfo['expires_at'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @if($lockInfo['can_unlock'])
                                        <button type="button" class="btn btn-warning btn-sm w-100" onclick="unlockDocument()">
                                            <i class="ti ti-unlock me-1"></i>
                                            Force Unlock
                                        </button>
                                    @endif
                                @else
                                    <div class="text-center py-3">
                                        <i class="ti ti-unlock text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Document is unlocked</p>
                                        <button type="button" class="btn btn-primary btn-sm mt-2" onclick="lockDocument()">
                                            <i class="ti ti-lock me-1"></i>
                                            Lock Document
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Comment Modal -->
<div class="modal fade" id="addCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCommentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="commentContent" class="form-label">Comment</label>
                        <textarea class="form-control" id="commentContent" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Comment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCommentForm">
                <div class="modal-body">
                    <input type="hidden" id="editCommentId" name="comment_id">
                    <div class="mb-3">
                        <label for="editCommentContent" class="form-label">Comment</label>
                        <textarea class="form-control" id="editCommentContent" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Comment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Collaboration functionality
let currentFileId = {{ $file->id }};

// Add comment
document.getElementById('addCommentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const content = document.getElementById('commentContent').value;
    
    fetch(`/files/${currentFileId}/collaboration/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ content: content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add comment');
    });
});

// Edit comment
function editComment(commentId) {
    // Implementation for editing comments
    console.log('Edit comment:', commentId);
}

// Delete comment
function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete comment');
        });
    }
}

// Reply to comment
function replyToComment(commentId) {
    // Implementation for replying to comments
    console.log('Reply to comment:', commentId);
}

// Lock document
function lockDocument() {
    fetch(`/files/${currentFileId}/lock`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to lock document');
    });
}

// Unlock document
function unlockDocument() {
    if (confirm('Are you sure you want to unlock this document?')) {
        fetch(`/files/${currentFileId}/unlock`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to unlock document');
        });
    }
}

// Refresh collaboration data periodically
setInterval(function() {
    // Refresh active users
    fetch(`/files/${currentFileId}/collaboration/active-users`)
        .then(response => response.json())
        .then(data => {
            // Update active users display
            console.log('Active users updated:', data);
        })
        .catch(error => console.error('Error updating active users:', error));
}, 30000); // Refresh every 30 seconds
</script>
@endpush
