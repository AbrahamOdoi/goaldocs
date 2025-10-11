<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="../../../assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Create - {{ $displayName }} Management - GoalDocs</title>
    <meta name="description" content="Manage your {{ strtolower($displayName) }}s and positions" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../../assets/img/favicon/favicon.ico" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="../../../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../../../assets/vendor/fonts/tabler-icons.css"/>
    <link rel="stylesheet" href="../../../assets/vendor/fonts/flag-icons.css" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/typeahead-js/typeahead.css" />
    
    <!-- Helpers -->
    <script src="../../../assets/vendor/js/helpers.js"></script>
    <script src="../../../assets/vendor/js/template-customizer.js"></script>
    <script src="../../../assets/js/config.js"></script>
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
                            <span class="text-muted fw-light">{{ $displayName }} Management /</span> Create {{ $displayName }}
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <h5 class="card-header">Create New {{ $displayName }}</h5>
                                    <div class="card-body">
                                        <form action="{{ route('hierarchy.departments.store') }}" method="POST">
                                            @csrf
                                            
                                            <div class="row">
                                                <div class="mb-3 col-md-8">
                                                    <label for="name" class="form-label">{{ $displayName }} Name</label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name') }}" 
                                                           placeholder="Enter {{ strtolower($displayName) }} name" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="mb-3 col-md-4">
                                                    <label for="color" class="form-label">Color</label>
                                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                                           id="color" name="color" value="{{ old('color', '#696cff') }}" 
                                                           title="Choose your color" required>
                                                    @error('color')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                                          id="description" name="description" rows="3" 
                                                          placeholder="Describe the purpose of this {{ strtolower($displayName) }}">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-primary me-2">
                                                    <i class="ti ti-check me-1"></i>Create {{ $displayName }}
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
                                    <h5 class="card-header">Preview</h5>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar me-3" id="preview-avatar" style="background-color: #696cff;">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-building ti-sm"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" id="preview-name">Department Name</h6>
                                                <small class="text-muted" id="preview-count">0 Positions</small>
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
    <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../assets/vendor/libs/hammer/hammer.js"></script>
    <script src="../../../assets/vendor/libs/i18n/i18n.js"></script>
    <script src="../../../assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="../../../assets/vendor/js/menu.js"></script>
    
    <!-- Main JS -->
    <script src="../../../assets/js/main.js"></script>
    
    <script>
        // Live preview functionality
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value || 'Department Name';
            document.getElementById('preview-name').textContent = name;
        });
        
        document.getElementById('description').addEventListener('input', function() {
            const description = this.value || 'Description will appear here...';
            document.getElementById('preview-description').textContent = description;
        });
        
        document.getElementById('color').addEventListener('input', function() {
            const color = this.value;
            document.getElementById('preview-avatar').style.backgroundColor = color;
        });
    </script>
</body>
</html> 