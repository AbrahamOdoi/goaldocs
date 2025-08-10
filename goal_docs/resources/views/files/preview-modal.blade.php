<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="ti ti-file-text ti-md me-2"></i>
                    <div>
                        <h5 class="modal-title mb-0" id="documentPreviewModalLabel">Document Preview</h5>
                        <small id="documentInfo" class="opacity-75">Loading...</small>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- PDF Controls -->
                    <div id="pdfControls" class="d-flex gap-2 align-items-center" style="display: none !important;">
                        <button type="button" class="btn btn-sm btn-outline-light" id="prevPage" title="Previous Page">
                            <i class="ti ti-chevron-left ti-xs"></i>
                        </button>
                        <span class="text-white">
                            <span id="currentPage">1</span> / <span id="totalPages">1</span>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-light" id="nextPage" title="Next Page">
                            <i class="ti ti-chevron-right ti-xs"></i>
                        </button>
                        <div class="vr opacity-50 mx-2"></div>
                        <button type="button" class="btn btn-sm btn-outline-light" id="zoomOut" title="Zoom Out">
                            <i class="ti ti-zoom-out ti-xs"></i>
                        </button>
                        <span class="text-white">
                            <span id="zoomLevel">100</span>%
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-light" id="zoomIn" title="Zoom In">
                            <i class="ti ti-zoom-in ti-xs"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="fitToWidth" title="Fit to Width">
                            <i class="ti ti-arrows-horizontal ti-xs"></i>
                        </button>
                    </div>
                    
                    <!-- Annotation Controls -->
                    <div id="annotationControls" class="d-flex gap-2 align-items-center" style="display: none !important;">
                        <div class="vr opacity-50 mx-2"></div>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" title="Annotation Tools">
                                <i class="ti ti-pencil ti-xs"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-tool="highlight"><i class="ti ti-highlight ti-xs me-2"></i>Highlight</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="underline"><i class="ti ti-underline ti-xs me-2"></i>Underline</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="strikeout"><i class="ti ti-strikethrough ti-xs me-2"></i>Strikeout</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="text"><i class="ti ti-text ti-xs me-2"></i>Text</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="drawing"><i class="ti ti-pencil ti-xs me-2"></i>Drawing</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="sticky_note"><i class="ti ti-note ti-xs me-2"></i>Sticky Note</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="arrow"><i class="ti ti-arrow-right ti-xs me-2"></i>Arrow</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="rectangle"><i class="ti ti-square ti-xs me-2"></i>Rectangle</a></li>
                                <li><a class="dropdown-item" href="#" data-tool="circle"><i class="ti ti-circle ti-xs me-2"></i>Circle</a></li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light" id="annotationColor" title="Annotation Color">
                            <i class="ti ti-palette ti-xs"></i>
                        </button>
                        <input type="color" id="colorPicker" class="d-none" value="#FFEB3B">
                        <button type="button" class="btn btn-sm btn-outline-light" id="toggleAnnotationMode" title="Toggle Annotation Mode">
                            <i class="ti ti-edit ti-xs"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="showAnnotations" title="Show/Hide Annotations">
                            <i class="ti ti-eye ti-xs"></i>
                        </button>
                    </div>
                    
                    <!-- Collaboration Controls -->
                    <div id="collaborationControls" class="d-flex gap-2 align-items-center" style="display: none !important;">
                        <div class="vr opacity-50 mx-2"></div>
                        <button type="button" class="btn btn-sm btn-outline-light" id="joinCollaboration" title="Join Collaboration">
                            <i class="ti ti-users ti-xs"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="showParticipants" title="Show Participants">
                            <i class="ti ti-user-check ti-xs"></i>
                            <span id="participantCount" class="badge bg-primary ms-1">0</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="toggleCollaboration" title="Toggle Collaboration Mode">
                            <i class="ti ti-broadcast ti-xs"></i>
                        </button>
                    </div>
                    
                    <!-- Image Controls -->
                    <div id="imageControls" class="d-flex gap-2 align-items-center" style="display: none !important;">
                        <button type="button" class="btn btn-sm btn-outline-light" id="imageZoomOut" title="Zoom Out">
                            <i class="ti ti-zoom-out ti-xs"></i>
                        </button>
                        <span class="text-white">
                            <span id="imageZoomLevel">100</span>%
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-light" id="imageZoomIn" title="Zoom In">
                            <i class="ti ti-zoom-in ti-xs"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="imageResetZoom" title="Reset Zoom">
                            <i class="ti ti-refresh ti-xs"></i>
                        </button>
                    </div>
                    
                    <!-- General Controls -->
                    <div class="vr opacity-50 mx-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-light" id="downloadFile" title="Download">
                        <i class="ti ti-download ti-xs"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal" title="Close">
                        <i class="ti ti-x ti-xs"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark position-relative">
                <!-- Loading Indicator -->
                <div id="previewLoader" class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Loading document...</p>
                </div>
                
                <!-- PDF Viewer -->
                <div id="pdfViewer" class="h-100 overflow-auto position-relative" style="display: none;">
                    <canvas id="pdfCanvas" class="mx-auto d-block"></canvas>
                    <div id="annotationLayer" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none;"></div>
                    <div id="annotationOverlay" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none;"></div>
                    
                    <!-- Collaboration UI -->
                    <div id="collaborationLayer" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none;">
                        <!-- User cursors -->
                        <div id="userCursors"></div>
                        <!-- Collaboration notifications -->
                        <div id="collaborationNotifications" class="position-absolute top-0 end-0 p-3"></div>
                    </div>
                </div>
                
                <!-- Image Viewer -->
                <div id="imageViewer" class="h-100 d-flex align-items-center justify-content-center overflow-hidden" style="display: none;">
                    <img id="imagePreview" class="img-fluid" style="max-width: none; cursor: grab;" draggable="false">
                </div>
                
                <!-- Text Viewer -->
                <div id="textViewer" class="h-100 p-4" style="display: none;">
                    <pre id="textContent" class="text-white h-100 overflow-auto m-0" style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.5;"></pre>
                </div>
                
                <!-- Unsupported Format -->
                <div id="unsupportedViewer" class="d-flex flex-column align-items-center justify-content-center h-100 text-white" style="display: none;">
                    <i class="ti ti-file-x display-1 text-muted mb-3"></i>
                    <h5 class="text-white mb-2">Preview Not Available</h5>
                    <p class="text-muted mb-3">This file type cannot be previewed in the browser</p>
                    <button type="button" class="btn btn-primary" id="downloadUnsupported">
                        <i class="ti ti-download ti-xs me-1"></i>Download File
                    </button>
                </div>
                
                <!-- Error State -->
                <div id="errorViewer" class="d-flex flex-column align-items-center justify-content-center h-100 text-white" style="display: none;">
                    <i class="ti ti-alert-triangle display-1 text-warning mb-3"></i>
                    <h5 class="text-white mb-2">Error Loading Document</h5>
                    <p class="text-muted mb-3" id="errorMessage">An error occurred while loading the document</p>
                    <button type="button" class="btn btn-outline-light" onclick="retryPreview()">
                        <i class="ti ti-refresh ti-xs me-1"></i>Retry
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
#documentPreviewModal .modal-body {
    height: calc(100vh - 80px);
}

#pdfCanvas {
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    background: white;
    margin: 20px auto;
}

#imagePreview {
    transition: transform 0.1s ease;
}

#imagePreview.dragging {
    cursor: grabbing !important;
}

#textContent {
    background: #1a1a1a;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #333;
}

/* Custom scrollbar for text viewer */
#textContent::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

#textContent::-webkit-scrollbar-track {
    background: #2a2a2a;
    border-radius: 4px;
}

#textContent::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
}

#textContent::-webkit-scrollbar-thumb:hover {
    background: #777;
}

/* Syntax highlighting for common file types */
.syntax-json .string { color: #98d982; }
.syntax-json .number { color: #d19a66; }
.syntax-json .boolean { color: #c678dd; }
.syntax-json .null { color: #e06c75; }
.syntax-json .key { color: #61afef; }

.syntax-js .keyword { color: #c678dd; }
.syntax-js .string { color: #98d982; }
.syntax-js .comment { color: #5c6370; font-style: italic; }
.syntax-js .function { color: #61afef; }

.syntax-css .property { color: #61afef; }
.syntax-css .value { color: #98d982; }
.syntax-css .selector { color: #e06c75; }
</style>
@endpush

@push('scripts')
<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Configure PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Global variables for PDF viewer
let currentPDF = null;
let currentPage = 1;
let totalPages = 1;
let currentScale = 1.0;
let isRendering = false;

// Global variables for image viewer
let currentImageScale = 1.0;
let imageIsDragging = false;
let imageStartX = 0;
let imageStartY = 0;
let imageTranslateX = 0;
let imageTranslateY = 0;

// Current file data
let currentFileData = null;

/**
 * Open document preview modal
 */
function openDocumentPreview(fileId, fileName, fileUrl, mimeType, fileSize) {
    currentFileData = { fileId, fileName, fileUrl, mimeType, fileSize };
    
    // Update modal title
    document.getElementById('documentPreviewModalLabel').textContent = fileName;
    document.getElementById('documentInfo').textContent = `${formatFileSize(fileSize)} • ${mimeType}`;
    
    // Reset viewer state
    resetViewerState();
    
    // Show loading
    showLoader();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    modal.show();
    
    // Load document based on type
    setTimeout(() => {
        loadDocument(fileUrl, mimeType);
    }, 100);
}

/**
 * Reset viewer state
 */
function resetViewerState() {
    // Hide all viewers
    document.getElementById('pdfViewer').style.display = 'none';
    document.getElementById('imageViewer').style.display = 'none';
    document.getElementById('textViewer').style.display = 'none';
    document.getElementById('unsupportedViewer').style.display = 'none';
    document.getElementById('errorViewer').style.display = 'none';
    
    // Hide all controls
    document.getElementById('pdfControls').style.display = 'none';
    document.getElementById('imageControls').style.display = 'none';
    
    // Reset PDF state
    currentPDF = null;
    currentPage = 1;
    totalPages = 1;
    currentScale = 1.0;
    
    // Reset image state
    currentImageScale = 1.0;
    imageTranslateX = 0;
    imageTranslateY = 0;
    
    // Update download button
    document.getElementById('downloadFile').onclick = () => downloadCurrentFile();
    document.getElementById('downloadUnsupported').onclick = () => downloadCurrentFile();
}

/**
 * Show loading indicator
 */
function showLoader() {
    document.getElementById('previewLoader').style.display = 'flex';
}

/**
 * Hide loading indicator
 */
function hideLoader() {
    document.getElementById('previewLoader').style.display = 'none';
}

/**
 * Load document based on MIME type
 */
function loadDocument(url, mimeType) {
    try {
        if (mimeType === 'application/pdf') {
            loadPDF(url);
        } else if (mimeType.startsWith('image/')) {
            loadImage(url);
        } else if (isTextFile(mimeType)) {
            loadTextFile(url, mimeType);
        } else {
            showUnsupportedFormat();
        }
    } catch (error) {
        showError('Failed to load document: ' + error.message);
    }
}

/**
 * Load PDF document
 */
async function loadPDF(url) {
    try {
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        
        currentPDF = pdf;
        totalPages = pdf.numPages;
        currentPage = 1;
        
        // Update UI
        document.getElementById('totalPages').textContent = totalPages;
        document.getElementById('currentPage').textContent = currentPage;
        document.getElementById('pdfControls').style.display = 'flex';
        
        // Render first page
        await renderPDFPage(currentPage);
        
        // Show PDF viewer
        hideLoader();
        document.getElementById('pdfViewer').style.display = 'block';
        
    } catch (error) {
        showError('Failed to load PDF: ' + error.message);
    }
}

/**
 * Render PDF page
 */
async function renderPDFPage(pageNum) {
    if (isRendering) return;
    
    isRendering = true;
    
    try {
        const page = await currentPDF.getPage(pageNum);
        const canvas = document.getElementById('pdfCanvas');
        const context = canvas.getContext('2d');
        
        // Calculate scale to fit width
        const viewport = page.getViewport({ scale: 1 });
        const container = document.getElementById('pdfViewer');
        const containerWidth = container.clientWidth - 40; // Account for margins
        const scale = Math.min(currentScale, containerWidth / viewport.width);
        
        const scaledViewport = page.getViewport({ scale: scale });
        
        // Set canvas dimensions
        canvas.height = scaledViewport.height;
        canvas.width = scaledViewport.width;
        
        // Render page
        const renderContext = {
            canvasContext: context,
            viewport: scaledViewport
        };
        
        await page.render(renderContext).promise;
        
        // Update zoom level display
        document.getElementById('zoomLevel').textContent = Math.round(scale * 100);
        
        currentPage = pageNum;
        document.getElementById('currentPage').textContent = currentPage;
        
        // Update navigation buttons
        document.getElementById('prevPage').disabled = currentPage <= 1;
        document.getElementById('nextPage').disabled = currentPage >= totalPages;
        
    } catch (error) {
        showError('Failed to render PDF page: ' + error.message);
    } finally {
        isRendering = false;
    }
}

/**
 * Load image file
 */
function loadImage(url) {
    const img = document.getElementById('imagePreview');
    
    img.onload = function() {
        // Reset image state
        currentImageScale = 1.0;
        imageTranslateX = 0;
        imageTranslateY = 0;
        updateImageTransform();
        
        // Show image controls
        document.getElementById('imageControls').style.display = 'flex';
        updateImageZoomDisplay();
        
        // Show image viewer
        hideLoader();
        document.getElementById('imageViewer').style.display = 'flex';
    };
    
    img.onerror = function() {
        showError('Failed to load image');
    };
    
    img.src = url;
}

/**
 * Load text file
 */
async function loadTextFile(url, mimeType) {
    try {
        const response = await fetch(url);
        const text = await response.text();
        
        const textContent = document.getElementById('textContent');
        textContent.textContent = text;
        
        // Apply syntax highlighting based on file type
        applySyntaxHighlighting(textContent, mimeType);
        
        // Show text viewer
        hideLoader();
        document.getElementById('textViewer').style.display = 'block';
        
    } catch (error) {
        showError('Failed to load text file: ' + error.message);
    }
}

/**
 * Check if file is a text file
 */
function isTextFile(mimeType) {
    const textTypes = [
        'text/plain',
        'text/html',
        'text/css',
        'text/javascript',
        'application/javascript',
        'application/json',
        'application/xml',
        'text/xml',
        'text/csv',
        'text/markdown',
        'application/x-php',
        'application/x-python-code',
        'text/x-python',
        'text/x-java-source',
        'text/x-c',
        'text/x-c++',
        'text/x-csharp'
    ];
    
    return textTypes.includes(mimeType) || mimeType.startsWith('text/');
}

/**
 * Apply basic syntax highlighting
 */
function applySyntaxHighlighting(element, mimeType) {
    const text = element.textContent;
    let highlightedText = text;
    
    // Basic JSON highlighting
    if (mimeType === 'application/json') {
        highlightedText = highlightJSON(text);
        element.classList.add('syntax-json');
    }
    // Basic JavaScript highlighting
    else if (mimeType === 'application/javascript' || mimeType === 'text/javascript') {
        highlightedText = highlightJavaScript(text);
        element.classList.add('syntax-js');
    }
    // Basic CSS highlighting
    else if (mimeType === 'text/css') {
        highlightedText = highlightCSS(text);
        element.classList.add('syntax-css');
    }
    
    element.innerHTML = highlightedText;
}

/**
 * Simple JSON syntax highlighting
 */
function highlightJSON(text) {
    return text
        .replace(/"([^"]+)"(\s*:)/g, '<span class="key">"$1"</span>$2')
        .replace(/:\s*"([^"]*)"/g, ': <span class="string">"$1"</span>')
        .replace(/:\s*(-?\d+\.?\d*)/g, ': <span class="number">$1</span>')
        .replace(/:\s*(true|false)/g, ': <span class="boolean">$1</span>')
        .replace(/:\s*(null)/g, ': <span class="null">$1</span>');
}

/**
 * Simple JavaScript syntax highlighting
 */
function highlightJavaScript(text) {
    return text
        .replace(/\b(function|var|let|const|if|else|for|while|return|class|extends)\b/g, '<span class="keyword">$1</span>')
        .replace(/'([^']*)'/g, '<span class="string">\'$1\'</span>')
        .replace(/"([^"]*)"/g, '<span class="string">"$1"</span>')
        .replace(/\/\*[\s\S]*?\*\/|\/\/.*$/gm, '<span class="comment">$&</span>');
}

/**
 * Simple CSS syntax highlighting
 */
function highlightCSS(text) {
    return text
        .replace(/([a-zA-Z-]+)(\s*:)/g, '<span class="property">$1</span>$2')
        .replace(/:\s*([^;{]+)/g, ': <span class="value">$1</span>')
        .replace(/([^{}]+)(\s*{)/g, '<span class="selector">$1</span>$2');
}

/**
 * Show unsupported format message
 */
function showUnsupportedFormat() {
    hideLoader();
    document.getElementById('unsupportedViewer').style.display = 'flex';
}

/**
 * Show error message
 */
function showError(message) {
    hideLoader();
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorViewer').style.display = 'flex';
}

/**
 * Retry preview
 */
function retryPreview() {
    if (currentFileData) {
        resetViewerState();
        showLoader();
        setTimeout(() => {
            loadDocument(currentFileData.fileUrl, currentFileData.mimeType);
        }, 100);
    }
}

/**
 * Download current file
 */
function downloadCurrentFile() {
    if (currentFileData) {
        const link = document.createElement('a');
        link.href = currentFileData.fileUrl;
        link.download = currentFileData.fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// PDF Controls Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // PDF navigation
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            renderPDFPage(currentPage - 1);
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage < totalPages) {
            renderPDFPage(currentPage + 1);
        }
    });
    
    // PDF zoom controls
    document.getElementById('zoomIn').addEventListener('click', function() {
        currentScale = Math.min(currentScale * 1.2, 3.0);
        renderPDFPage(currentPage);
    });
    
    document.getElementById('zoomOut').addEventListener('click', function() {
        currentScale = Math.max(currentScale / 1.2, 0.3);
        renderPDFPage(currentPage);
    });
    
    document.getElementById('fitToWidth').addEventListener('click', function() {
        currentScale = 1.0;
        renderPDFPage(currentPage);
    });
    
    // Image zoom controls
    document.getElementById('imageZoomIn').addEventListener('click', function() {
        currentImageScale = Math.min(currentImageScale * 1.2, 5.0);
        updateImageTransform();
        updateImageZoomDisplay();
    });
    
    document.getElementById('imageZoomOut').addEventListener('click', function() {
        currentImageScale = Math.max(currentImageScale / 1.2, 0.1);
        updateImageTransform();
        updateImageZoomDisplay();
    });
    
    document.getElementById('imageResetZoom').addEventListener('click', function() {
        currentImageScale = 1.0;
        imageTranslateX = 0;
        imageTranslateY = 0;
        updateImageTransform();
        updateImageZoomDisplay();
    });
    
    // Image pan functionality
    const imagePreview = document.getElementById('imagePreview');
    
    imagePreview.addEventListener('mousedown', function(e) {
        if (currentImageScale > 1.0) {
            imageIsDragging = true;
            imageStartX = e.clientX - imageTranslateX;
            imageStartY = e.clientY - imageTranslateY;
            imagePreview.classList.add('dragging');
            e.preventDefault();
        }
    });
    
    document.addEventListener('mousemove', function(e) {
        if (imageIsDragging) {
            imageTranslateX = e.clientX - imageStartX;
            imageTranslateY = e.clientY - imageStartY;
            updateImageTransform();
        }
    });
    
    document.addEventListener('mouseup', function() {
        if (imageIsDragging) {
            imageIsDragging = false;
            imagePreview.classList.remove('dragging');
        }
    });
    
    // Image wheel zoom
    imagePreview.addEventListener('wheel', function(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        currentImageScale = Math.max(0.1, Math.min(5.0, currentImageScale * delta));
        updateImageTransform();
        updateImageZoomDisplay();
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('documentPreviewModal').classList.contains('show')) {
            switch(e.key) {
                case 'ArrowLeft':
                    if (currentPDF && currentPage > 1) {
                        renderPDFPage(currentPage - 1);
                    }
                    break;
                case 'ArrowRight':
                    if (currentPDF && currentPage < totalPages) {
                        renderPDFPage(currentPage + 1);
                    }
                    break;
                case 'Escape':
                    const modal = bootstrap.Modal.getInstance(document.getElementById('documentPreviewModal'));
                    if (modal) modal.hide();
                    break;
            }
        }
    });
});

/**
 * Update image transform
 */
function updateImageTransform() {
    const img = document.getElementById('imagePreview');
    img.style.transform = `scale(${currentImageScale}) translate(${imageTranslateX / currentImageScale}px, ${imageTranslateY / currentImageScale}px)`;
}

/**
 * Update image zoom display
 */
        function updateImageZoomDisplay() {
            document.getElementById('imageZoomLevel').textContent = Math.round(currentImageScale * 100);
        }

        // ===== ANNOTATION SYSTEM =====
        
        // Global annotation variables
        let currentAnnotationTool = null;
        let isAnnotationMode = false;
        let currentAnnotations = [];
        let annotationLayer = null;
        let annotationOverlay = null;
        let currentFileId = null;
        let currentFileUrl = null;
        let currentMimeType = null;
        let currentFileSize = null;
        let isDrawing = false;
        let drawingPath = [];
        let annotationColor = '#FFEB3B';
        let annotationOpacity = 1.0;

        /**
         * Initialize annotation system
         */
        function initializeAnnotationSystem() {
            annotationLayer = document.getElementById('annotationLayer');
            annotationOverlay = document.getElementById('annotationOverlay');
            
            // Show annotation controls for PDFs
            if (currentMimeType === 'application/pdf') {
                document.getElementById('annotationControls').style.display = 'flex';
            }
            
            // Load existing annotations
            if (currentFileId) {
                loadAnnotations();
            }
        }

        /**
         * Load annotations for current file
         */
        function loadAnnotations() {
            if (!currentFileId) return;
            
            fetch(`/files/${currentFileId}/annotations`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentAnnotations = data.annotations;
                        renderAnnotations();
                    }
                })
                .catch(error => {
                    console.error('Failed to load annotations:', error);
                });
        }

        /**
         * Render all annotations
         */
        function renderAnnotations() {
            if (!annotationLayer) return;
            
            annotationLayer.innerHTML = '';
            
            currentAnnotations.forEach(annotation => {
                if (annotation.pageNumber === currentPage) {
                    createAnnotationElement(annotation);
                }
            });
        }

        /**
         * Create annotation element
         */
        function createAnnotationElement(annotation) {
            const element = document.createElement('div');
            element.className = 'annotation-element';
            element.dataset.annotationId = annotation.id;
            element.style.position = 'absolute';
            element.style.pointerEvents = 'auto';
            element.style.cursor = 'pointer';
            
            // Position the annotation
            const coords = annotation.coordinates;
            element.style.left = coords.x + 'px';
            element.style.top = coords.y + 'px';
            element.style.width = coords.width + 'px';
            element.style.height = coords.height + 'px';
            
            // Style based on annotation type
            switch (annotation.type) {
                case 'highlight':
                    element.style.backgroundColor = annotation.color;
                    element.style.opacity = annotation.opacity;
                    element.style.mixBlendMode = 'multiply';
                    break;
                case 'underline':
                    element.style.borderBottom = `3px solid ${annotation.color}`;
                    element.style.opacity = annotation.opacity;
                    break;
                case 'strikeout':
                    element.style.textDecoration = 'line-through';
                    element.style.textDecorationColor = annotation.color;
                    element.style.textDecorationThickness = '3px';
                    element.style.opacity = annotation.opacity;
                    break;
                case 'text':
                    element.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                    element.style.border = `1px solid ${annotation.color}`;
                    element.style.padding = '2px 4px';
                    element.style.fontSize = '12px';
                    element.style.color = annotation.color;
                    element.textContent = annotation.content || '';
                    break;
                case 'sticky_note':
                    element.style.backgroundColor = annotation.color;
                    element.style.borderRadius = '4px';
                    element.style.padding = '8px';
                    element.style.fontSize = '12px';
                    element.style.color = '#000';
                    element.style.boxShadow = '2px 2px 4px rgba(0,0,0,0.2)';
                    element.textContent = annotation.content || '📝';
                    break;
                case 'drawing':
                    if (annotation.metadata && annotation.metadata.path) {
                        element.innerHTML = `<svg width="100%" height="100%" style="position: absolute; top: 0; left: 0;">
                            <path d="${annotation.metadata.path}" stroke="${annotation.color}" stroke-width="2" fill="none" opacity="${annotation.opacity}"/>
                        </svg>`;
                    }
                    break;
                case 'arrow':
                case 'rectangle':
                case 'circle':
                    element.style.border = `2px solid ${annotation.color}`;
                    element.style.opacity = annotation.opacity;
                    if (annotation.type === 'circle') {
                        element.style.borderRadius = '50%';
                    }
                    break;
            }
            
            // Add click handler for annotation details
            element.addEventListener('click', (e) => {
                e.stopPropagation();
                showAnnotationDetails(annotation);
            });
            
            annotationLayer.appendChild(element);
        }

        /**
         * Show annotation details/comments
         */
        function showAnnotationDetails(annotation) {
            // Create a modal or sidebar to show annotation details
            const detailsHtml = `
                <div class="annotation-details bg-white border rounded p-3" style="position: absolute; z-index: 1000; max-width: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${annotation.type.charAt(0).toUpperCase() + annotation.type.slice(1)}</h6>
                        <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                    </div>
                    <p class="text-muted mb-2">By ${annotation.createdBy}</p>
                    ${annotation.content ? `<p class="mb-2">${annotation.content}</p>` : ''}
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="addCommentToAnnotation(${annotation.id})">
                            <i class="ti ti-message ti-xs me-1"></i>Comment
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="resolveAnnotation(${annotation.id})">
                            <i class="ti ti-check ti-xs me-1"></i>Resolve
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAnnotation(${annotation.id})">
                            <i class="ti ti-trash ti-xs me-1"></i>Delete
                        </button>
                    </div>
                </div>
            `;
            
            const detailsElement = document.createElement('div');
            detailsElement.innerHTML = detailsHtml;
            detailsElement.style.position = 'absolute';
            detailsElement.style.left = '50px';
            detailsElement.style.top = '50px';
            detailsElement.style.zIndex = '1000';
            
            annotationOverlay.appendChild(detailsElement);
        }

        /**
         * Set annotation tool
         */
        function setAnnotationTool(tool) {
            currentAnnotationTool = tool;
            isAnnotationMode = true;
            
            // Update UI
            document.getElementById('toggleAnnotationMode').classList.add('btn-primary');
            document.getElementById('toggleAnnotationMode').classList.remove('btn-outline-light');
            
            // Enable drawing mode for drawing tool
            if (tool === 'drawing') {
                enableDrawingMode();
            } else {
                disableDrawingMode();
            }
        }

        /**
         * Toggle annotation mode
         */
        function toggleAnnotationMode() {
            isAnnotationMode = !isAnnotationMode;
            
            if (isAnnotationMode) {
                document.getElementById('toggleAnnotationMode').classList.add('btn-primary');
                document.getElementById('toggleAnnotationMode').classList.remove('btn-outline-light');
                annotationLayer.style.pointerEvents = 'auto';
            } else {
                document.getElementById('toggleAnnotationMode').classList.remove('btn-primary');
                document.getElementById('toggleAnnotationMode').classList.add('btn-outline-light');
                annotationLayer.style.pointerEvents = 'none';
                disableDrawingMode();
            }
        }

        /**
         * Enable drawing mode
         */
        function enableDrawingMode() {
            isDrawing = true;
            drawingPath = [];
            
            const canvas = document.getElementById('pdfCanvas');
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', endDrawing);
        }

        /**
         * Disable drawing mode
         */
        function disableDrawingMode() {
            isDrawing = false;
            drawingPath = [];
            
            const canvas = document.getElementById('pdfCanvas');
            canvas.removeEventListener('mousedown', startDrawing);
            canvas.removeEventListener('mousemove', draw);
            canvas.removeEventListener('mouseup', endDrawing);
        }

        /**
         * Start drawing
         */
        function startDrawing(e) {
            if (!isDrawing || currentAnnotationTool !== 'drawing') return;
            
            const rect = e.target.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            drawingPath = [`M ${x} ${y}`];
        }

        /**
         * Draw
         */
        function draw(e) {
            if (!isDrawing || currentAnnotationTool !== 'drawing' || drawingPath.length === 0) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            drawingPath.push(`L ${x} ${y}`);
        }

        /**
         * End drawing
         */
        function endDrawing(e) {
            if (!isDrawing || currentAnnotationTool !== 'drawing') return;
            
            const pathData = drawingPath.join(' ');
            createAnnotation({
                type: 'drawing',
                coordinates: { x: 0, y: 0, width: 100, height: 100 },
                metadata: { path: pathData }
            });
            
            drawingPath = [];
        }

        /**
         * Create annotation
         */
        function createAnnotation(annotationData) {
            if (!currentFileId) return;
            
            const data = {
                annotation_type: annotationData.type,
                page_number: currentPage,
                coordinates: annotationData.coordinates,
                content: annotationData.content,
                color: annotationColor,
                opacity: annotationOpacity,
                metadata: annotationData.metadata
            };
            
            fetch(`/files/${currentFileId}/annotations`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentAnnotations.push(data.annotation);
                    createAnnotationElement(data.annotation);
                }
            })
            .catch(error => {
                console.error('Failed to create annotation:', error);
            });
        }

        /**
         * Delete annotation
         */
        function deleteAnnotation(annotationId) {
            fetch(`/annotations/${annotationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentAnnotations = currentAnnotations.filter(a => a.id !== annotationId);
                    const element = document.querySelector(`[data-annotation-id="${annotationId}"]`);
                    if (element) element.remove();
                }
            })
            .catch(error => {
                console.error('Failed to delete annotation:', error);
            });
        }

        /**
         * Resolve annotation
         */
        function resolveAnnotation(annotationId) {
            fetch(`/annotations/${annotationId}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const annotation = currentAnnotations.find(a => a.id === annotationId);
                    if (annotation) {
                        annotation.isResolved = true;
                    }
                }
            })
            .catch(error => {
                console.error('Failed to resolve annotation:', error);
            });
        }

        /**
         * Add comment to annotation
         */
        function addCommentToAnnotation(annotationId) {
            const comment = prompt('Enter your comment:');
            if (!comment) return;
            
            fetch(`/annotations/${annotationId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ content: comment })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment added successfully!');
                }
            })
            .catch(error => {
                console.error('Failed to add comment:', error);
            });
        }

        // Event listeners for annotation controls
        document.addEventListener('DOMContentLoaded', function() {
            // Annotation tool selection
            document.querySelectorAll('[data-tool]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    setAnnotationTool(this.dataset.tool);
                });
            });
            
            // Toggle annotation mode
            document.getElementById('toggleAnnotationMode').addEventListener('click', toggleAnnotationMode);
            
            // Color picker
            document.getElementById('annotationColor').addEventListener('click', function() {
                document.getElementById('colorPicker').click();
            });
            
            document.getElementById('colorPicker').addEventListener('change', function() {
                annotationColor = this.value;
            });
            
            // Show/hide annotations
            document.getElementById('showAnnotations').addEventListener('click', function() {
                const isVisible = annotationLayer.style.display !== 'none';
                annotationLayer.style.display = isVisible ? 'none' : 'block';
                this.innerHTML = isVisible ? '<i class="ti ti-eye-off ti-xs"></i>' : '<i class="ti ti-eye ti-xs"></i>';
            });
        });

        // Update the openDocumentPreview function to initialize annotations
        const originalOpenDocumentPreview = window.openDocumentPreview;
        window.openDocumentPreview = function(fileId, fileName, fileUrl, mimeType, fileSize) {
            currentFileId = fileId;
            currentFileUrl = fileUrl;
            currentMimeType = mimeType;
            currentFileSize = fileSize;
            
            if (originalOpenDocumentPreview) {
                originalOpenDocumentPreview(fileId, fileName, fileUrl, mimeType, fileSize);
            }
            
            // Initialize annotation system after modal is shown
            setTimeout(() => {
                initializeAnnotationSystem();
                initializeCollaborationSystem();
            }, 500);
        };

        // ===== COLLABORATION SYSTEM =====
        
        // Global collaboration variables
        let currentSession = null;
        let currentPresence = null;
        let collaborationInterval = null;
        let updateInterval = null;
        let isCollaborationMode = false;
        let participants = [];
        let userCursors = {};

        /**
         * Initialize collaboration system
         */
        function initializeCollaborationSystem() {
            // Show collaboration controls for PDFs
            if (currentMimeType === 'application/pdf') {
                document.getElementById('collaborationControls').style.display = 'flex';
            }
            
            // Check for existing collaboration sessions
            checkActiveSessions();
        }

        /**
         * Check for active collaboration sessions
         */
        function checkActiveSessions() {
            if (!currentFileId) return;
            
            fetch(`/files/${currentFileId}/collaboration/sessions`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sessions.length > 0) {
                        showCollaborationInvite(data.sessions[0]);
                    }
                })
                .catch(error => {
                    console.error('Failed to check active sessions:', error);
                });
        }

        /**
         * Show collaboration invite
         */
        function showCollaborationInvite(session) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-info alert-dismissible fade show';
            notification.innerHTML = `
                <strong>Collaboration Session Active!</strong><br>
                ${session.active_users_count} users are currently collaborating on this document.
                <button type="button" class="btn btn-sm btn-primary ms-2" onclick="joinCollaborationSession('${session.session_id}')">
                    Join Session
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.getElementById('collaborationNotifications').appendChild(notification);
        }

        /**
         * Join collaboration session
         */
        function joinCollaborationSession(sessionId = null) {
            if (!currentFileId) {
                console.log('❌ No current file ID available');
                return;
            }
            
            console.log('🚀 Attempting to join collaboration session for file:', currentFileId);
            
            const data = {
                session_name: 'Document Review Session',
                connection_id: generateConnectionId()
            };
            
            console.log('📤 Sending join request with data:', data);
            
            fetch(`/files/${currentFileId}/collaboration/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('📥 Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📥 Response data:', data);
                if (data.success) {
                    currentSession = data.session;
                    currentPresence = data.presence;
                    isCollaborationMode = true;
                    
                    console.log('✅ Successfully joined session:', currentSession);
                    
                    // Update UI
                    document.getElementById('toggleCollaboration').classList.add('btn-primary');
                    document.getElementById('toggleCollaboration').classList.remove('btn-outline-light');
                    
                    // Start real-time updates
                    startRealTimeUpdates();
                    
                    // Show success notification
                    showNotification('Joined collaboration session!', 'success');
                } else {
                    console.error('❌ Failed to join session:', data.error);
                    showNotification('Failed to join collaboration session: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('❌ Network error joining collaboration session:', error);
                showNotification('Failed to join collaboration session', 'error');
            });
        }

        /**
         * Leave collaboration session
         */
        function leaveCollaborationSession() {
            if (!currentSession) return;
            
            fetch(`/collaboration/${currentSession.id}/leave`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentSession = null;
                    currentPresence = null;
                    isCollaborationMode = false;
                    
                    // Update UI
                    document.getElementById('toggleCollaboration').classList.remove('btn-primary');
                    document.getElementById('toggleCollaboration').classList.add('btn-outline-light');
                    
                    // Stop real-time updates
                    stopRealTimeUpdates();
                    
                    // Clear participants
                    participants = [];
                    updateParticipantCount();
                    clearUserCursors();
                    
                    showNotification('Left collaboration session', 'info');
                }
            })
            .catch(error => {
                console.error('Failed to leave collaboration session:', error);
            });
        }

        /**
         * Start real-time updates
         */
        function startRealTimeUpdates() {
            if (!currentSession) return;
            
            // Update presence every 30 seconds
            collaborationInterval = setInterval(() => {
                updatePresence();
            }, 30000);
            
            // Get updates every 5 seconds
            updateInterval = setInterval(() => {
                getRealTimeUpdates();
            }, 5000);
            
            // Initial update
            updatePresence();
            getRealTimeUpdates();
        }

        /**
         * Stop real-time updates
         */
        function stopRealTimeUpdates() {
            if (collaborationInterval) {
                clearInterval(collaborationInterval);
                collaborationInterval = null;
            }
            
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        }

        /**
         * Update user presence
         */
        function updatePresence() {
            if (!currentSession) return;
            
            const data = {
                status: 'online',
                current_page: currentPage,
                cursor_position: getCurrentCursorPosition(),
                activity_data: {
                    zoom_level: currentScale,
                    annotation_mode: isAnnotationMode,
                    current_tool: currentAnnotationTool
                }
            };
            
            fetch(`/collaboration/${currentSession.id}/presence`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .catch(error => {
                console.error('Failed to update presence:', error);
            });
        }

        /**
         * Get real-time updates
         */
        function getRealTimeUpdates() {
            if (!currentSession) return;
            
            fetch(`/collaboration/${currentSession.id}/updates`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const updates = data.updates;
                        
                        // Update participants
                        participants = updates.participants;
                        updateParticipantCount();
                        updateUserCursors();
                        
                        // Handle new annotations
                        if (updates.recent_annotations.length > 0) {
                            handleNewAnnotations(updates.recent_annotations);
                        }
                        
                        // Handle new comments
                        if (updates.recent_comments.length > 0) {
                            handleNewComments(updates.recent_comments);
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to get real-time updates:', error);
                });
        }

        /**
         * Handle new annotations from other users
         */
        function handleNewAnnotations(newAnnotations) {
            newAnnotations.forEach(annotation => {
                // Check if annotation already exists
                const existingAnnotation = currentAnnotations.find(a => a.id === annotation.id);
                if (!existingAnnotation) {
                    currentAnnotations.push(annotation);
                    createAnnotationElement(annotation);
                    
                    // Show notification
                    showNotification(`${annotation.createdBy} added a ${annotation.type} annotation`, 'info');
                }
            });
        }

        /**
         * Handle new comments from other users
         */
        function handleNewComments(newComments) {
            newComments.forEach(comment => {
                // Show notification for new comments
                showNotification(`${comment.user_name} added a comment`, 'info');
            });
        }

        /**
         * Update participant count
         */
        function updateParticipantCount() {
            const count = participants.filter(p => p.is_active).length;
            document.getElementById('participantCount').textContent = count;
        }

        /**
         * Update user cursors
         */
        function updateUserCursors() {
            clearUserCursors();
            
            participants.forEach(participant => {
                if (participant.is_active && participant.cursor_position && participant.current_page === currentPage) {
                    createUserCursor(participant);
                }
            });
        }

        /**
         * Create user cursor element
         */
        function createUserCursor(participant) {
            const cursor = document.createElement('div');
            cursor.className = 'user-cursor';
            cursor.dataset.userId = participant.user_id;
            cursor.style.position = 'absolute';
            cursor.style.left = participant.cursor_position.x + 'px';
            cursor.style.top = participant.cursor_position.y + 'px';
            cursor.style.width = '20px';
            cursor.style.height = '20px';
            cursor.style.backgroundColor = getRandomColor(participant.user_id);
            cursor.style.borderRadius = '50%';
            cursor.style.border = '2px solid white';
            cursor.style.boxShadow = '0 2px 4px rgba(0,0,0,0.3)';
            cursor.style.zIndex = '1000';
            cursor.style.pointerEvents = 'none';
            
            // Add user name tooltip
            cursor.title = participant.user_name;
            
            document.getElementById('userCursors').appendChild(cursor);
            userCursors[participant.user_id] = cursor;
        }

        /**
         * Clear user cursors
         */
        function clearUserCursors() {
            document.getElementById('userCursors').innerHTML = '';
            userCursors = {};
        }

        /**
         * Get current cursor position
         */
        function getCurrentCursorPosition() {
            // This would be implemented based on mouse position tracking
            return { x: 0, y: 0 };
        }

        /**
         * Generate connection ID
         */
        function generateConnectionId() {
            return 'conn_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        /**
         * Get random color for user
         */
        function getRandomColor(userId) {
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];
            const index = userId % colors.length;
            return colors[index];
        }

        /**
         * Show notification
         */
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.getElementById('collaborationNotifications').appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        /**
         * Toggle collaboration mode
         */
        function toggleCollaborationMode() {
            if (isCollaborationMode) {
                leaveCollaborationSession();
            } else {
                joinCollaborationSession();
            }
        }

        /**
         * Show participants modal
         */
        function showParticipants() {
            if (!currentSession) return;
            
            fetch(`/collaboration/${currentSession.id}/participants`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showParticipantsModal(data.participants, data.session_stats);
                    }
                })
                .catch(error => {
                    console.error('Failed to get participants:', error);
                });
        }

        /**
         * Show participants modal
         */
        function showParticipantsModal(participants, stats) {
            const modalHtml = `
                <div class="modal fade" id="participantsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Session Participants</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>Session Stats:</strong><br>
                                    Active Users: ${stats.active_users}<br>
                                    Total Users: ${stats.total_users}<br>
                                    Duration: ${stats.session_duration} minutes
                                </div>
                                <div class="participants-list">
                                    ${participants.map(p => `
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar avatar-sm me-2" style="background-color: ${getRandomColor(p.user_id)};">
                                                ${p.user_name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <strong>${p.user_name}</strong><br>
                                                <small class="text-muted">${p.status} • Page ${p.current_page || 'N/A'}</small>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal
            const existingModal = document.getElementById('participantsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('participantsModal'));
            modal.show();
        }

        // Event listeners for collaboration controls
        document.addEventListener('DOMContentLoaded', function() {
            // Join collaboration
            document.getElementById('joinCollaboration').addEventListener('click', joinCollaborationSession);
            
            // Toggle collaboration mode
            document.getElementById('toggleCollaboration').addEventListener('click', toggleCollaborationMode);
            
            // Show participants
            document.getElementById('showParticipants').addEventListener('click', showParticipants);
        });

        // Update annotation creation to broadcast to collaboration session
        const originalCreateAnnotation = window.createAnnotation;
        window.createAnnotation = function(annotationData) {
            if (originalCreateAnnotation) {
                originalCreateAnnotation(annotationData);
            }
            
            // Broadcast to collaboration session
            if (currentSession && annotationData) {
                broadcastAnnotation(annotationData);
            }
        };

        /**
         * Broadcast annotation to collaboration session
         */
        function broadcastAnnotation(annotationData) {
            if (!currentSession) return;
            
            // This would be called after annotation is created
            setTimeout(() => {
                fetch(`/collaboration/${currentSession.id}/broadcast`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        annotation_id: annotationData.id,
                        action: 'created'
                    })
                })
                .catch(error => {
                    console.error('Failed to broadcast annotation:', error);
                });
            }, 1000);
        }
    </script>
    @endpush
