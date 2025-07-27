<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>User Management - GoalDocs</title>
    <meta name="description" content="Manage users and their positions" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}"/>
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    
    <!-- Custom Styles -->
    <style>
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 14px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
        }
    </style>
    
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu -->
            @include('partials.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                
                <!-- Navbar -->
                @include('partials.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-0">User Management</h5>
                                                <small class="text-muted">Manage users and their positions</small>
                                            </div>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>Add User
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ Header -->

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <!-- Users Table -->
                        <div class="card">
                            <div class="card-datatable table-responsive">
                                <table class="table table-bordered" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--/ Users Table -->
                        
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                <div>© 2025, made with ❤️ by <a href="https://pixinvent.com" target="_blank" class="fw-medium">Pixinvent</a></div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                    <p class="text-warning"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    
    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- Page JS -->
    <script>
        $(document).ready(function() {
            // Initialize Server-side DataTable
            var usersTable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("users.index") }}',
                    type: 'GET'
                },
                columns: [
                    {
                        data: null,
                        name: 'name',
                        render: function(data, type, row) {
                            var adminBadge = row.is_admin ? '<span class="badge admin-badge status-badge">Admin</span>' : '';
                            return `
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3" style="background: ${row.avatar_color};">
                                        ${row.initials}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${row.name}</h6>
                                        ${adminBadge}
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'position',
                        name: 'positions.name',
                        render: function(data, type, row) {
                            if (row.position) {
                                return `
                                    <div>
                                        <strong>${row.position.name}</strong>
                                    </div>
                                    <small class="text-muted">
                                        ${row.position.department_name} • ${row.position.level.charAt(0).toUpperCase() + row.position.level.slice(1)} Level
                                    </small>
                                `;
                            }
                            return '<span class="text-muted">No position assigned</span>';
                        },
                        orderable: true
                    },
                    {
                        data: 'email_verified_at',
                        name: 'email_verified_at',
                        render: function(data, type, row) {
                            if (row.email_verified_at) {
                                return '<span class="badge bg-label-success status-badge">Active</span>';
                            } else {
                                return '<span class="badge bg-label-warning status-badge">Pending</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var adminToggle = '';
                            @if(auth()->user()->is_admin)
                                adminToggle = `
                                    <button type="button" class="dropdown-item toggle-admin-btn" 
                                            data-user-id="${row.id}" 
                                            data-is-admin="${row.is_admin ? 'true' : 'false'}">
                                        <i class="ti ti-shield me-1"></i>
                                        ${row.is_admin ? 'Remove Admin' : 'Make Admin'}
                                    </button>
                                `;
                            @endif
                            
                            return `
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ url('users') }}/${row.id}/edit">
                                            <i class="ti ti-edit me-1"></i>Edit User
                                        </a>
                                        ${adminToggle}
                                        <button type="button" class="dropdown-item resend-credentials-btn" 
                                                data-user-id="${row.id}">
                                            <i class="ti ti-mail me-1"></i>Resend Credentials
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger delete-user-btn" 
                                                data-user-id="${row.id}"
                                                data-user-name="${row.name}">
                                            <i class="ti ti-trash me-1"></i>Delete User
                                        </button>
                                    </div>
                                </div>
                            `;
                        }
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']],
                responsive: true,
                language: {
                    emptyTable: `
                        <div class="text-center py-5">
                            <i class="ti ti-users-off ti-3x text-muted mb-3"></i>
                            <h5 class="mb-2">No Users Yet</h5>
                            <p class="text-muted mb-4">Get started by adding your first user</p>
                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Add User
                            </a>
                        </div>
                    `
                }
            });

            // Use event delegation for dynamically created content
            
            // Delete user functionality
            $(document).on('click', '.delete-user-btn', function() {
                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');
                
                $('#deleteUserName').text(userName);
                $('#deleteUserForm').attr('action', '{{ route("users.destroy", ":id") }}'.replace(':id', userId));
                $('#deleteUserModal').modal('show');
            });

            // Toggle admin functionality
            $(document).on('click', '.toggle-admin-btn', function() {
                const userId = $(this).data('user-id');
                const isAdmin = $(this).data('is-admin') === 'true';
                const button = $(this);
                
                $.post('{{ route("users.toggle-admin", ":id") }}'.replace(':id', userId), {
                    '_token': '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        // Reload the DataTable to reflect changes
                        usersTable.ajax.reload(null, false);
                        
                        // Show success message
                        showAlert('success', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Failed to update admin status');
                });
            });

            // Resend credentials functionality
            $(document).on('click', '.resend-credentials-btn', function() {
                const userId = $(this).data('user-id');
                const button = $(this);
                
                button.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Sending...');
                
                $.post('{{ route("users.resend-credentials", ":id") }}'.replace(':id', userId), {
                    '_token': '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Failed to resend credentials');
                })
                .always(function() {
                    button.prop('disabled', false).html('<i class="ti ti-mail me-1"></i>Resend Credentials');
                });
            });

            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('.container-xxl .card:first').before(alertHtml);
                
                // Auto dismiss after 5 seconds
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
</body>
</html> 