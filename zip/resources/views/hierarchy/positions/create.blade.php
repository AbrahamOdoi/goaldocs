<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Create - Position - GoalDocs</title>
    <meta name="description" content="Manage your {{ strtolower($displayName) }}s and positions" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    
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
                        
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">{{ $displayName }} Management /</span> Create Position
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <h5 class="card-header">Create New Position</h5>
                                    <div class="card-body">
                                        <form action="{{ route('hierarchy.positions.store', $department) }}" method="POST">
                                            @csrf
                                            
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Position Name</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                       id="name" name="name" value="{{ old('name') }}" 
                                                       placeholder="Enter position name" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="level" class="form-label">Level</label>
                                                <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                                                    <option value="">Select level</option>
                                                    @if($userType === 'family')
                                                        <option value="primary" {{ old('level') == 'primary' ? 'selected' : '' }}>Primary</option>
                                                        <option value="secondary" {{ old('level') == 'secondary' ? 'selected' : '' }}>Secondary</option>
                                                        <option value="adult" {{ old('level') == 'adult' ? 'selected' : '' }}>Adult</option>
                                                        <option value="teenager" {{ old('level') == 'teenager' ? 'selected' : '' }}>Teenager</option>
                                                        <option value="child" {{ old('level') == 'child' ? 'selected' : '' }}>Child</option>
                                                        <option value="infant" {{ old('level') == 'infant' ? 'selected' : '' }}>Infant</option>
                                                    @elseif($userType === 'government')
                                                        <option value="executive" {{ old('level') == 'executive' ? 'selected' : '' }}>Executive</option>
                                                        <option value="director" {{ old('level') == 'director' ? 'selected' : '' }}>Director</option>
                                                        <option value="manager" {{ old('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                                                        <option value="supervisor" {{ old('level') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                                                        <option value="officer" {{ old('level') == 'officer' ? 'selected' : '' }}>Officer</option>
                                                        <option value="assistant" {{ old('level') == 'assistant' ? 'selected' : '' }}>Assistant</option>
                                                    @elseif($userType === 'educational_institution')
                                                        <option value="chancellor" {{ old('level') == 'chancellor' ? 'selected' : '' }}>Chancellor</option>
                                                        <option value="dean" {{ old('level') == 'dean' ? 'selected' : '' }}>Dean</option>
                                                        <option value="professor" {{ old('level') == 'professor' ? 'selected' : '' }}>Professor</option>
                                                        <option value="associate" {{ old('level') == 'associate' ? 'selected' : '' }}>Associate</option>
                                                        <option value="assistant" {{ old('level') == 'assistant' ? 'selected' : '' }}>Assistant</option>
                                                        <option value="lecturer" {{ old('level') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                                                    @elseif($userType === 'social_group')
                                                        <option value="leader" {{ old('level') == 'leader' ? 'selected' : '' }}>Leader</option>
                                                        <option value="coordinator" {{ old('level') == 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                                                        <option value="member" {{ old('level') == 'member' ? 'selected' : '' }}>Member</option>
                                                        <option value="volunteer" {{ old('level') == 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                                                    @elseif($userType === 'professional_group')
                                                        <option value="senior" {{ old('level') == 'senior' ? 'selected' : '' }}>Senior</option>
                                                        <option value="mid" {{ old('level') == 'mid' ? 'selected' : '' }}>Mid</option>
                                                        <option value="junior" {{ old('level') == 'junior' ? 'selected' : '' }}>Junior</option>
                                                        <option value="apprentice" {{ old('level') == 'apprentice' ? 'selected' : '' }}>Apprentice</option>
                                                    @elseif($userType === 'non_profit')
                                                        <option value="executive" {{ old('level') == 'executive' ? 'selected' : '' }}>Executive</option>
                                                        <option value="manager" {{ old('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                                                        <option value="coordinator" {{ old('level') == 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                                                        <option value="volunteer" {{ old('level') == 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                                                    @elseif($userType === 'individual')
                                                        <option value="primary" {{ old('level') == 'primary' ? 'selected' : '' }}>Primary</option>
                                                        <option value="secondary" {{ old('level') == 'secondary' ? 'selected' : '' }}>Secondary</option>
                                                        <option value="tertiary" {{ old('level') == 'tertiary' ? 'selected' : '' }}>Tertiary</option>
                                                    @else
                                                        {{-- Default organizational levels --}}
                                                        <option value="executive" {{ old('level') == 'executive' ? 'selected' : '' }}>Executive</option>
                                                        <option value="manager" {{ old('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                                                        <option value="lead" {{ old('level') == 'lead' ? 'selected' : '' }}>Lead</option>
                                                        <option value="senior" {{ old('level') == 'senior' ? 'selected' : '' }}>Senior Level</option>
                                                        <option value="mid" {{ old('level') == 'mid' ? 'selected' : '' }}>Mid Level</option>
                                                        <option value="entry" {{ old('level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                                                    @endif
                                                </select>
                                                @error('level')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                                          id="description" name="description" rows="3" 
                                                          placeholder="Describe the responsibilities and requirements for this position">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-primary me-2">
                                                    <i class="ti ti-check me-1"></i>Create Position
                                                </button>
                                                <a href="{{ route('hierarchy.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ti ti-x me-1"></i>Cancel
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <h5 class="card-header">Department Info</h5>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar me-3" style="background-color: {{ $department->color }};">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-building ti-sm"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $department->name }}</h6>
                                                <small class="text-muted">{{ $department->activePositions->count() }} Positions</small>
                                            </div>
                                        </div>
                                        @if($department->description)
                                            <p class="text-muted">{{ $department->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="card mt-4">
                                    <h5 class="card-header">Preview</h5>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0" id="preview-name">Position Name</h6>
                                                <small class="text-muted" id="preview-level">Level</small>
                                            </div>
                                        </div>
                                        <p class="text-muted" id="preview-description">Description will appear here...</p>
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
                                <div>
                                    © <script>document.write(new Date().getFullYear())</script>, made with ❤️ by <a href="https://pixinvent.com" target="_blank" class="fw-medium">Pixinvent</a>
                                </div>
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
    
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <script>
        // Live preview functionality
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value || 'Position Name';
            document.getElementById('preview-name').textContent = name;
        });
        
        document.getElementById('level').addEventListener('change', function() {
            const level = this.options[this.selectedIndex].text || 'Level';
            document.getElementById('preview-level').textContent = level;
        });
        
        document.getElementById('description').addEventListener('input', function() {
            const description = this.value || 'Description will appear here...';
            document.getElementById('preview-description').textContent = description;
        });
    </script>
</body>
</html> 