<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Create User - GoalDocs</title>
    <meta name="description" content="Create a new user and assign position" />
    
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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    
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
                        
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Create New User</h5>
                                        <small class="text-muted">Add a new user and assign them to a position</small>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('users.store') }}" method="POST">
                                            @csrf
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="first_name" class="form-label">First Name</label>
                                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                                           id="first_name" name="first_name" value="{{ old('first_name') }}" 
                                                           placeholder="Enter first name" required>
                                                    @error('first_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="last_name" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                                           id="last_name" name="last_name" value="{{ old('last_name') }}" 
                                                           placeholder="Enter last name" required>
                                                    @error('last_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                           id="email" name="email" value="{{ old('email') }}" 
                                                           placeholder="Enter email address" required>
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="phone" class="form-label">Phone Number</label>
                                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                                           placeholder="Enter phone number" required>
                                                    @error('phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label for="position_id" class="form-label">Position</label>
                                                    <select class="form-select @error('position_id') is-invalid @enderror" 
                                                            id="position_id" name="position_id" required>
                                                        <option value="">Select a position</option>
                                                        @foreach($departments as $department)
                                                            <optgroup label="{{ $department->name }}">
                                                                @foreach($department->activePositions as $position)
                                                                    <option value="{{ $position->id }}" 
                                                                            {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                                        {{ $position->name }} ({{ ucfirst($position->level) }} Level)
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    @error('position_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-4 mb-3">
                                                    <label for="start_date" class="form-label">Start Date</label>
                                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                           id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}">
                                                    @error('start_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            @if(auth()->user()->is_admin)
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input @error('is_admin') is-invalid @enderror" 
                                                           type="checkbox" id="is_admin" name="is_admin" value="1" 
                                                           {{ old('is_admin') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_admin">
                                                        Grant Administrative Access
                                                    </label>
                                                    <small class="text-muted d-block">Admin users can manage other users and organizational settings</small>
                                                    @error('is_admin')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ti ti-arrow-left me-1"></i>Back to Users
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti ti-user-plus me-1"></i>Create User
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">User Preview</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <div id="user-avatar" class="mx-auto mb-2" style="width: 60px; height: 60px; border-radius: 50%; background: #696cff; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 18px;">
                                                --
                                            </div>
                                            <h6 id="user-name" class="mb-0">Full Name</h6>
                                            <small id="user-position" class="text-muted">Position will appear here</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Contact Information</small>
                                            <div id="user-email" class="mb-1">email@example.com</div>
                                            <div id="user-phone">+1234567890</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Status</small>
                                            <span id="admin-badge" class="badge bg-label-danger d-none">Administrator</span>
                                            <span class="badge bg-label-success">Active</span>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="ti ti-info-circle me-1"></i>
                                            <small>Login credentials will be automatically generated and sent via email and SMS after creation.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- Page JS -->
    <script>
        $(document).ready(function() {
            // Live preview functionality
            function updatePreview() {
                const firstName = $('#first_name').val();
                const lastName = $('#last_name').val();
                const email = $('#email').val();
                const phone = $('#phone').val();
                const isAdmin = $('#is_admin').is(':checked');
                const positionId = $('#position_id').val();
                const positionText = $('#position_id option:selected').text();
                
                // Update name and avatar
                const fullName = `${firstName} ${lastName}`.trim();
                const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                
                $('#user-name').text(fullName || 'Full Name');
                $('#user-avatar').text(initials || '--');
                
                // Update contact info
                $('#user-email').text(email || 'email@example.com');
                $('#user-phone').text(phone || '+1234567890');
                
                // Update position
                if (positionId && positionText) {
                    $('#user-position').text(positionText);
                } else {
                    $('#user-position').text('Position will appear here');
                }
                
                // Update admin badge
                if (isAdmin) {
                    $('#admin-badge').removeClass('d-none');
                } else {
                    $('#admin-badge').addClass('d-none');
                }
            }
            
            // Bind events for live preview
            $('#first_name, #last_name, #email, #phone, #position_id, #is_admin').on('input change', updatePreview);
            
            // Initialize date picker
            $('#start_date').flatpickr({
                dateFormat: 'Y-m-d',
                defaultDate: 'today'
            });
            
            // Initialize bootstrap select
            $('#position_id').selectpicker();
        });
    </script>
</body>
</html> 