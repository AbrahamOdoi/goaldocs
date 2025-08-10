<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Tenant\DashboardController;

/*
|--------------------------------------------------------------------------
| Central Application Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/force-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/')->with('success', 'Logged out for testing.');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
});

Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth', \App\Http\Middleware\EnsureEmailIsVerified::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    // OTP PROCESS
    Route::get('/verify/method', [VerificationController::class, 'chooseMethod'])->name('verification.method');
    Route::post('/verify/send', [VerificationController::class, 'sendOtp'])->name('verification.send');
    Route::get('/verify/enter', [VerificationController::class, 'showOtpForm'])->name('verification.enter');
    Route::post('/verify/check', [VerificationController::class, 'checkOtp'])->name('verification.check');
    Route::post('/resend-otp', [VerificationController::class, 'resendOtp'])->name('otp.resend');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [\App\Http\Controllers\AccountController::class, 'index'])->name('account.settings');
    Route::put('/account', [\App\Http\Controllers\AccountController::class, 'update'])->name('account.settings.update');
    Route::post('/account/avatar', [\App\Http\Controllers\AccountController::class, 'updateAvatar'])->name('account.settings.avatar');
    Route::post('/account/deactivate', [\App\Http\Controllers\AccountController::class, 'deactivate'])->name('account.deactivate');
    Route::post('/account/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/account/security', function () {
        return view('accountSettings.security');
    })->name('account.security');
    Route::get('/account/billing', function () {
        return view('accountSettings.billing');
    })->name('account.billing');
    Route::get('/account/notifications', function () {
        return view('accountSettings.notifications');
    })->name('account.notifications');
    Route::get('/account/connections', function () {
        return view('accountSettings.connections');
    })->name('account.connections');
    Route::get('/profile', function () {
        return view('userProfile.profile');
    })->name('profile');
    
    // Hierarchy Management Routes
    Route::prefix('hierarchy')->name('hierarchy.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HierarchyController::class, 'index'])->name('index');
        Route::get('/departments/create', [\App\Http\Controllers\HierarchyController::class, 'createDepartment'])->name('departments.create');
        Route::post('/departments', [\App\Http\Controllers\HierarchyController::class, 'storeDepartment'])->name('departments.store');
        Route::get('/departments/{department}/edit', [\App\Http\Controllers\HierarchyController::class, 'editDepartment'])->name('departments.edit');
        Route::put('/departments/{department}', [\App\Http\Controllers\HierarchyController::class, 'updateDepartment'])->name('departments.update');
        Route::get('/departments/{department}/positions/create', [\App\Http\Controllers\HierarchyController::class, 'createPosition'])->name('positions.create');
        Route::post('/departments/{department}/positions', [\App\Http\Controllers\HierarchyController::class, 'storePosition'])->name('positions.store');
        Route::get('/positions/{position}/edit', [\App\Http\Controllers\HierarchyController::class, 'editPosition'])->name('positions.edit');
        Route::put('/positions/{position}', [\App\Http\Controllers\HierarchyController::class, 'updatePosition'])->name('positions.update');
        Route::post('/assign-position', [\App\Http\Controllers\HierarchyController::class, 'assignPosition'])->name('assign-position');
        Route::delete('/remove-position', [\App\Http\Controllers\HierarchyController::class, 'removePosition'])->name('remove-position');
    });
    
    // User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\UserManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [\App\Http\Controllers\UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-admin', [\App\Http\Controllers\UserManagementController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::post('/{user}/resend-credentials', [\App\Http\Controllers\UserManagementController::class, 'resendCredentials'])->name('resend-credentials');
    });

    // File Management Routes (all authenticated users)
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FileController::class, 'index'])->name('index');
        Route::post('/upload', [\App\Http\Controllers\FileController::class, 'upload'])->name('upload');
        Route::post('/folders', [\App\Http\Controllers\FileController::class, 'createFolder'])->name('folders.create');
        Route::get('/search', [\App\Http\Controllers\FileController::class, 'search'])->name('search');
        Route::get('/{file}/download', [\App\Http\Controllers\FileController::class, 'download'])->name('download');
        Route::get('/{file}/preview', [\App\Http\Controllers\FileController::class, 'preview'])->name('preview');
        
        // Advanced Search Routes
        Route::get('/advanced-search', [\App\Http\Controllers\AdvancedSearchController::class, 'index'])->name('advanced-search');
        Route::get('/ajax-search', [\App\Http\Controllers\AdvancedSearchController::class, 'ajaxSearch'])->name('ajax-search');
        Route::get('/search-analytics', [\App\Http\Controllers\AdvancedSearchController::class, 'analytics'])->name('search-analytics');
        
        // Document Preview Routes
        Route::get('/{file}/preview', [\App\Http\Controllers\DocumentPreviewController::class, 'show'])->name('preview');
        Route::get('/{file}/preview/data', [\App\Http\Controllers\DocumentPreviewController::class, 'getPreviewData'])->name('preview.data');
        Route::get('/{file}/preview/full-text', [\App\Http\Controllers\DocumentPreviewController::class, 'getFullText'])->name('preview.full-text');
        Route::get('/{file}/preview/pdf-page', [\App\Http\Controllers\DocumentPreviewController::class, 'getPdfPage'])->name('preview.pdf-page');
        Route::get('/{file}/preview/download', [\App\Http\Controllers\DocumentPreviewController::class, 'downloadPreview'])->name('preview.download');
        
        // Admin preview routes
        Route::post('/preview/batch-generate', [\App\Http\Controllers\DocumentPreviewController::class, 'batchGenerate'])->name('preview.batch-generate');
        Route::get('/preview/stats', [\App\Http\Controllers\DocumentPreviewController::class, 'getStats'])->name('preview.stats');
        
        // Version Control Routes
        Route::get('/{file}/versions', [\App\Http\Controllers\VersionControlController::class, 'showHistory'])->name('version.history');
        Route::post('/{file}/versions/upload', [\App\Http\Controllers\VersionControlController::class, 'uploadVersion'])->name('version.upload');
        Route::post('/{file}/versions/compare', [\App\Http\Controllers\VersionControlController::class, 'compareVersions'])->name('version.compare');
        Route::post('/{file}/versions/rollback', [\App\Http\Controllers\VersionControlController::class, 'rollbackVersion'])->name('version.rollback');
        Route::get('/{file}/versions/comparison', [\App\Http\Controllers\VersionControlController::class, 'showComparison'])->name('version.comparison');
        Route::get('/{file}/versions/stats', [\App\Http\Controllers\VersionControlController::class, 'getVersionStats'])->name('version.stats');
        Route::post('/{file}/versions/ajax', [\App\Http\Controllers\VersionControlController::class, 'ajaxVersionOperation'])->name('version.ajax');
        
        // Version-specific routes
        Route::delete('/versions/{version}', [\App\Http\Controllers\VersionControlController::class, 'deleteVersion'])->name('version.delete');
        Route::get('/versions/{version}/download', [\App\Http\Controllers\VersionControlController::class, 'downloadVersion'])->name('version.download');
        Route::get('/versions/{version}/preview', [\App\Http\Controllers\VersionControlController::class, 'previewVersion'])->name('version.preview');
        Route::get('/versions/{version}/metadata', [\App\Http\Controllers\VersionControlController::class, 'getVersionMetadata'])->name('version.metadata');
        
        // Collaboration Routes
        Route::get('/{file}/collaboration', [\App\Http\Controllers\CollaborationController::class, 'showCollaboration'])->name('collaboration.show');
        Route::get('/{file}/collaboration/comments', [\App\Http\Controllers\CollaborationController::class, 'getComments'])->name('collaboration.comments');
        Route::post('/{file}/collaboration/comments', [\App\Http\Controllers\CollaborationController::class, 'addComment'])->name('collaboration.add-comment');
        Route::put('/collaboration/comments/{comment}', [\App\Http\Controllers\CollaborationController::class, 'updateComment'])->name('collaboration.update-comment');
        Route::delete('/collaboration/comments/{comment}', [\App\Http\Controllers\CollaborationController::class, 'deleteComment'])->name('collaboration.delete-comment');
        Route::post('/{file}/collaboration/lock', [\App\Http\Controllers\CollaborationController::class, 'lockDocument'])->name('collaboration.lock');
        Route::post('/{file}/collaboration/unlock', [\App\Http\Controllers\CollaborationController::class, 'unlockDocument'])->name('collaboration.unlock');
        Route::get('/{file}/collaboration/lock-status', [\App\Http\Controllers\CollaborationController::class, 'getDocumentLock'])->name('collaboration.lock-status');
        Route::post('/{file}/collaboration/force-unlock', [\App\Http\Controllers\CollaborationController::class, 'forceUnlockDocument'])->name('collaboration.force-unlock');
        Route::get('/{file}/collaboration/active-users', [\App\Http\Controllers\CollaborationController::class, 'getActiveUsers'])->name('collaboration.active-users');
        Route::post('/{file}/collaboration/update-activity', [\App\Http\Controllers\CollaborationController::class, 'updateActivity'])->name('collaboration.update-activity');
        Route::get('/{file}/collaboration/stats', [\App\Http\Controllers\CollaborationController::class, 'getCollaborationStats'])->name('collaboration.stats');
        Route::get('/{file}/collaboration/feed', [\App\Http\Controllers\CollaborationController::class, 'getCollaborationFeed'])->name('collaboration.feed');
        Route::post('/{file}/collaboration/ajax', [\App\Http\Controllers\CollaborationController::class, 'ajaxCollaboration'])->name('collaboration.ajax');
        
        // Admin collaboration routes
        Route::post('/collaboration/cleanup-locks', [\App\Http\Controllers\CollaborationController::class, 'cleanupExpiredLocks'])->name('collaboration.cleanup-locks');
    });

    // Workflow Automation Routes (outside files prefix)
    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\WorkflowController::class, 'dashboard'])->name('dashboard');
        Route::get('/', [\App\Http\Controllers\WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\WorkflowController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WorkflowController::class, 'store'])->name('store');
        Route::get('/{workflow}', [\App\Http\Controllers\WorkflowController::class, 'show'])->name('show');
        Route::get('/instances/{instance}', [\App\Http\Controllers\WorkflowController::class, 'showInstance'])->name('instance.show');
        Route::post('/instances/{instance}/approve', [\App\Http\Controllers\WorkflowController::class, 'approveStep'])->name('instance.approve');
        Route::post('/instances/{instance}/reject', [\App\Http\Controllers\WorkflowController::class, 'rejectStep'])->name('instance.reject');
        Route::get('/pending-actions', [\App\Http\Controllers\WorkflowController::class, 'pendingActions'])->name('pending-actions');
        Route::get('/templates', [\App\Http\Controllers\WorkflowController::class, 'templates'])->name('templates');
        Route::get('/templates/create', [\App\Http\Controllers\WorkflowController::class, 'createTemplate'])->name('templates.create');
        Route::post('/templates', [\App\Http\Controllers\WorkflowController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/templates/{template}', [\App\Http\Controllers\WorkflowController::class, 'showTemplate'])->name('templates.show');
        Route::post('/templates/{template}/generate', [\App\Http\Controllers\WorkflowController::class, 'generateFromTemplate'])->name('templates.generate');
        Route::get('/stats', [\App\Http\Controllers\WorkflowController::class, 'getStats'])->name('stats');
        Route::post('/cleanup-actions', [\App\Http\Controllers\WorkflowController::class, 'cleanupExpiredActions'])->name('cleanup-actions');
    });

    // Advanced Security Routes (outside files prefix)
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\SecurityController::class, 'dashboard'])->name('dashboard');
        Route::get('/stats', [\App\Http\Controllers\SecurityController::class, 'showStats'])->name('stats');
        Route::get('/audit-logs', [\App\Http\Controllers\SecurityController::class, 'showAuditLogs'])->name('audit-logs');
        Route::get('/encryption-keys', [\App\Http\Controllers\SecurityController::class, 'showEncryptionKeys'])->name('encryption-keys');
        Route::get('/settings', [\App\Http\Controllers\SecurityController::class, 'showSettings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\SecurityController::class, 'updateSettings'])->name('settings.update');
        Route::get('/audit-logs/data', [\App\Http\Controllers\SecurityController::class, 'getAuditLogs'])->name('audit-logs.data');
        Route::get('/stats/data', [\App\Http\Controllers\SecurityController::class, 'getStats'])->name('stats.data');
        Route::get('/report', [\App\Http\Controllers\SecurityController::class, 'generateReport'])->name('report');
        Route::get('/encryption-keys/{key}', [\App\Http\Controllers\SecurityController::class, 'getEncryptionKey'])->name('encryption-keys.show');
        Route::post('/cleanup-audit-logs', [\App\Http\Controllers\SecurityController::class, 'cleanupAuditLogs'])->name('security.cleanup-audit-logs');
    });

    // Analytics Dashboard Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/data', [\App\Http\Controllers\AnalyticsController::class, 'getData'])->name('data');
        Route::get('/files', [\App\Http\Controllers\AnalyticsController::class, 'fileAnalytics'])->name('files');
        Route::get('/users', [\App\Http\Controllers\AnalyticsController::class, 'userAnalytics'])->name('users');
        Route::get('/search', [\App\Http\Controllers\AnalyticsController::class, 'searchAnalytics'])->name('search');
        Route::get('/collaboration', [\App\Http\Controllers\AnalyticsController::class, 'collaborationAnalytics'])->name('collaboration');
        Route::get('/workflows', [\App\Http\Controllers\AnalyticsController::class, 'workflowAnalytics'])->name('workflows');
        Route::get('/security', [\App\Http\Controllers\AnalyticsController::class, 'securityAnalytics'])->name('security');
        Route::get('/performance', [\App\Http\Controllers\AnalyticsController::class, 'performanceAnalytics'])->name('performance');
        Route::get('/trends', [\App\Http\Controllers\AnalyticsController::class, 'trends'])->name('trends');
        Route::get('/top-performers', [\App\Http\Controllers\AnalyticsController::class, 'topPerformers'])->name('top-performers');
        Route::post('/report', [\App\Http\Controllers\AnalyticsController::class, 'generateReport'])->name('report');
        Route::post('/export', [\App\Http\Controllers\AnalyticsController::class, 'export'])->name('export');
        Route::post('/clear-cache', [\App\Http\Controllers\AnalyticsController::class, 'clearCache'])->name('clear-cache');
        Route::get('/real-time', [\App\Http\Controllers\AnalyticsController::class, 'realTime'])->name('real-time');
    });

    // File-specific security routes (inside files prefix)
    Route::prefix('files')->name('files.')->group(function () {
        Route::post('/{file}/workflows/start', [\App\Http\Controllers\WorkflowController::class, 'startWorkflow'])->name('workflows.start');
        Route::post('/{file}/security/encrypt', [\App\Http\Controllers\SecurityController::class, 'encryptFile'])->name('security.encrypt');
        Route::post('/{file}/security/decrypt', [\App\Http\Controllers\SecurityController::class, 'decryptFile'])->name('security.decrypt');
        Route::post('/{file}/security/watermark', [\App\Http\Controllers\SecurityController::class, 'addWatermark'])->name('security.watermark');
        Route::get('/{file}/security/status', [\App\Http\Controllers\SecurityController::class, 'getFileSecurityStatus'])->name('security.status');
        Route::delete('/{file}', [\App\Http\Controllers\FileController::class, 'deleteFile'])->name('delete');
        Route::put('/{file}/update', [\App\Http\Controllers\FileController::class, 'update'])->name('update');
        Route::delete('/folders/{folder}', [\App\Http\Controllers\FileController::class, 'deleteFolder'])->name('folders.delete');
        Route::put('/folders/{folder}/update', [\App\Http\Controllers\FileController::class, 'updateFolder'])->name('folders.update');
        Route::get('/permissions', [\App\Http\Controllers\FileController::class, 'getPermissions'])->name('permissions.get');
        Route::post('/permissions/preset', [\App\Http\Controllers\FileController::class, 'applyPreset'])->name('permissions.preset');
        Route::get('/{file}/preview-info', [\App\Http\Controllers\FileController::class, 'getPreviewInfo'])->name('preview-info');
        Route::post('/{file}/generate-thumbnail', [\App\Http\Controllers\FileController::class, 'generateThumbnail'])->name('generate-thumbnail');
        
        // Annotation routes
        Route::get('/{file}/annotations', [\App\Http\Controllers\AnnotationController::class, 'getAnnotations'])->name('annotations.index');
        Route::get('/{file}/annotations/page/{page}', [\App\Http\Controllers\AnnotationController::class, 'getPageAnnotations'])->name('annotations.page');
        Route::post('/{file}/annotations', [\App\Http\Controllers\AnnotationController::class, 'store'])->name('annotations.store');
        Route::put('/annotations/{annotation}', [\App\Http\Controllers\AnnotationController::class, 'update'])->name('annotations.update');
        Route::delete('/annotations/{annotation}', [\App\Http\Controllers\AnnotationController::class, 'destroy'])->name('annotations.destroy');
        Route::post('/annotations/{annotation}/resolve', [\App\Http\Controllers\AnnotationController::class, 'resolve'])->name('annotations.resolve');
        Route::post('/annotations/{annotation}/unresolve', [\App\Http\Controllers\AnnotationController::class, 'unresolve'])->name('annotations.unresolve');
        Route::get('/annotations/{annotation}/comments', [\App\Http\Controllers\AnnotationController::class, 'getComments'])->name('annotations.comments');
        Route::post('/annotations/{annotation}/comments', [\App\Http\Controllers\AnnotationController::class, 'addComment'])->name('annotations.comments.store');
        Route::put('/comments/{comment}', [\App\Http\Controllers\AnnotationController::class, 'updateComment'])->name('comments.update');
        Route::delete('/comments/{comment}', [\App\Http\Controllers\AnnotationController::class, 'deleteComment'])->name('comments.destroy');
        
        // Real-time collaboration routes
        Route::post('/{file}/collaboration/join', [\App\Http\Controllers\RealTimeController::class, 'joinSession'])->name('collaboration.join');
        Route::post('/collaboration/{session}/leave', [\App\Http\Controllers\RealTimeController::class, 'leaveSession'])->name('collaboration.leave');
        Route::post('/collaboration/{session}/presence', [\App\Http\Controllers\RealTimeController::class, 'updatePresence'])->name('collaboration.presence');
        Route::get('/collaboration/{session}/participants', [\App\Http\Controllers\RealTimeController::class, 'getParticipants'])->name('collaboration.participants');
        Route::get('/collaboration/{session}/updates', [\App\Http\Controllers\RealTimeController::class, 'getUpdates'])->name('collaboration.updates');
        Route::post('/collaboration/{session}/broadcast', [\App\Http\Controllers\RealTimeController::class, 'broadcastAnnotation'])->name('collaboration.broadcast');
        Route::get('/{file}/collaboration/sessions', [\App\Http\Controllers\RealTimeController::class, 'getActiveSessions'])->name('collaboration.sessions');
        Route::post('/collaboration/cleanup', [\App\Http\Controllers\RealTimeController::class, 'cleanupSessions'])->name('collaboration.cleanup');
        
        // OCR routes
        Route::get('/{file}/ocr', [\App\Http\Controllers\OcrController::class, 'showOcr'])->name('ocr');
        Route::post('/{file}/ocr', [\App\Http\Controllers\OcrController::class, 'processOcr'])->name('ocr.process');
        Route::get('/{file}/ocr/result', [\App\Http\Controllers\OcrController::class, 'getOcrResult'])->name('ocr.result');
        Route::put('/ocr/results/{ocrResult}', [\App\Http\Controllers\OcrController::class, 'updateOcrResult'])->name('ocr.update');
        Route::delete('/ocr/results/{ocrResult}', [\App\Http\Controllers\OcrController::class, 'deleteOcrResult'])->name('ocr.delete');
        Route::post('/ocr/batch', [\App\Http\Controllers\OcrController::class, 'processBatchOcr'])->name('ocr.batch');
        Route::get('/ocr/stats', [\App\Http\Controllers\OcrController::class, 'getOcrStats'])->name('ocr.stats');
        Route::get('/ocr/languages', [\App\Http\Controllers\OcrController::class, 'getSupportedLanguages'])->name('ocr.languages');
        Route::get('/ocr/options', [\App\Http\Controllers\OcrController::class, 'getOcrOptions'])->name('ocr.options');
        Route::get('/ocr/queue-status', [\App\Http\Controllers\OcrController::class, 'getQueueStatus'])->name('ocr.queue-status');
        
        // Document Conversion routes
        Route::get('/{file}/conversion', [\App\Http\Controllers\DocumentConversionController::class, 'showConversion'])->name('conversion');
        Route::post('/{file}/conversion', [\App\Http\Controllers\DocumentConversionController::class, 'convertFile'])->name('conversion.process');
        Route::get('/{file}/conversion/supported', [\App\Http\Controllers\DocumentConversionController::class, 'getSupportedConversions'])->name('conversion.supported');
        Route::get('/{file}/conversion/history', [\App\Http\Controllers\DocumentConversionController::class, 'getConversionHistory'])->name('conversion.history');
        Route::get('/{file}/conversion/result/{targetFormat}', [\App\Http\Controllers\DocumentConversionController::class, 'getConversionResult'])->name('conversion.result');
        Route::delete('/conversion/{conversion}', [\App\Http\Controllers\DocumentConversionController::class, 'deleteConversion'])->name('conversion.delete');
        Route::get('/conversion/{conversion}/download', [\App\Http\Controllers\DocumentConversionController::class, 'downloadConvertedFile'])->name('conversion.download');
        Route::get('/conversion/{conversion}/preview', [\App\Http\Controllers\DocumentConversionController::class, 'previewConvertedFile'])->name('conversion.preview');
        Route::get('/conversion/stats', [\App\Http\Controllers\DocumentConversionController::class, 'getConversionStats'])->name('conversion.stats');
        Route::get('/conversion/queue-status', [\App\Http\Controllers\DocumentConversionController::class, 'getQueueStatus'])->name('conversion.queue-status');
        
        // Batch Processing routes
        Route::get('/batch-processing', [\App\Http\Controllers\BatchProcessingController::class, 'index'])->name('batch-processing.index');
        Route::get('/batch-processing/create', [\App\Http\Controllers\BatchProcessingController::class, 'create'])->name('batch-processing.create');
        Route::post('/batch-processing', [\App\Http\Controllers\BatchProcessingController::class, 'store'])->name('batch-processing.store');
        Route::get('/batch-processing/{batchJob}', [\App\Http\Controllers\BatchProcessingController::class, 'show'])->name('batch-processing.show');
        Route::post('/batch-processing/{batchJob}/start', [\App\Http\Controllers\BatchProcessingController::class, 'start'])->name('batch-processing.start');
        Route::post('/batch-processing/{batchJob}/cancel', [\App\Http\Controllers\BatchProcessingController::class, 'cancel'])->name('batch-processing.cancel');
        Route::delete('/batch-processing/{batchJob}', [\App\Http\Controllers\BatchProcessingController::class, 'destroy'])->name('batch-processing.destroy');
        Route::get('/batch-processing/{batchJob}/status', [\App\Http\Controllers\BatchProcessingController::class, 'getStatus'])->name('batch-processing.status');
        Route::get('/batch-processing/stats', [\App\Http\Controllers\BatchProcessingController::class, 'getStats'])->name('batch-processing.stats');
        Route::get('/batch-processing/operations', [\App\Http\Controllers\BatchProcessingController::class, 'getSupportedOperations'])->name('batch-processing.operations');
        Route::get('/batch-processing/files', [\App\Http\Controllers\BatchProcessingController::class, 'getUserFiles'])->name('batch-processing.files');
        
        // Analytics routes
        Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/analytics/document-usage', [\App\Http\Controllers\AnalyticsController::class, 'documentUsagePage'])->name('analytics.document-usage');
        Route::get('/analytics/processing', [\App\Http\Controllers\AnalyticsController::class, 'processingPage'])->name('analytics.processing');
        Route::get('/analytics/storage', [\App\Http\Controllers\AnalyticsController::class, 'storagePage'])->name('analytics.storage');
        Route::get('/analytics/user-activity', [\App\Http\Controllers\AnalyticsController::class, 'userActivityPage'])->name('analytics.user-activity');
        Route::get('/analytics/api/document-usage', [\App\Http\Controllers\AnalyticsController::class, 'documentUsage'])->name('analytics.api.document-usage');
        Route::get('/analytics/api/processing', [\App\Http\Controllers\AnalyticsController::class, 'processing'])->name('analytics.api.processing');
        Route::get('/analytics/api/storage', [\App\Http\Controllers\AnalyticsController::class, 'storage'])->name('analytics.api.storage');
        Route::get('/analytics/api/user-activity', [\App\Http\Controllers\AnalyticsController::class, 'userActivity'])->name('analytics.api.user-activity');
        Route::get('/analytics/api/data', [\App\Http\Controllers\AnalyticsController::class, 'getData'])->name('analytics.api.data');
        Route::get('/analytics/api/summary', [\App\Http\Controllers\AnalyticsController::class, 'summary'])->name('analytics.api.summary');
        Route::get('/analytics/api/trends', [\App\Http\Controllers\AnalyticsController::class, 'trends'])->name('analytics.api.trends');
        Route::get('/analytics/api/realtime', [\App\Http\Controllers\AnalyticsController::class, 'realTime'])->name('analytics.api.realtime');
        Route::post('/analytics/api/report', [\App\Http\Controllers\AnalyticsController::class, 'generateReport'])->name('analytics.api.report');
        Route::post('/analytics/api/export', [\App\Http\Controllers\AnalyticsController::class, 'export'])->name('analytics.api.export');
        Route::post('/analytics/api/clear-cache', [\App\Http\Controllers\AnalyticsController::class, 'clearCache'])->name('analytics.api.clear-cache');
        
        Route::post('/permissions/assign', [\App\Http\Controllers\FileController::class, 'assignPermissions'])->name('permissions.assign');
        Route::delete('/permissions/remove', [\App\Http\Controllers\FileController::class, 'removePermission'])->name('permissions.remove');
    });

    // Document Comments Routes
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentCommentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DocumentCommentController::class, 'store'])->name('store');
        Route::put('/{comment}', [\App\Http\Controllers\DocumentCommentController::class, 'update'])->name('update');
        Route::post('/{comment}/resolve', [\App\Http\Controllers\DocumentCommentController::class, 'resolve'])->name('resolve');
        Route::post('/{comment}/reopen', [\App\Http\Controllers\DocumentCommentController::class, 'reopen'])->name('reopen');
        Route::delete('/{comment}', [\App\Http\Controllers\DocumentCommentController::class, 'destroy'])->name('destroy');
        Route::get('/statistics', [\App\Http\Controllers\DocumentCommentController::class, 'statistics'])->name('statistics');
    });

    // Document Workflows Routes
    Route::prefix('document-workflows')->name('document-workflows.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentWorkflowController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DocumentWorkflowController::class, 'store'])->name('store');
        Route::post('/{workflow}/start', [\App\Http\Controllers\DocumentWorkflowController::class, 'start'])->name('start');
        Route::post('/steps/{step}/complete', [\App\Http\Controllers\DocumentWorkflowController::class, 'completeStep'])->name('steps.complete');
        Route::post('/assignments/{assignment}/delegate', [\App\Http\Controllers\DocumentWorkflowController::class, 'delegateAssignment'])->name('assignments.delegate');
        Route::post('/{workflow}/cancel', [\App\Http\Controllers\DocumentWorkflowController::class, 'cancel'])->name('cancel');
        Route::get('/statistics', [\App\Http\Controllers\DocumentWorkflowController::class, 'statistics'])->name('statistics');
        Route::get('/my-assignments', [\App\Http\Controllers\DocumentWorkflowController::class, 'myAssignments'])->name('my-assignments');
    });

    // Search & Organization Routes
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SearchController::class, 'index'])->name('index');
        Route::get('/tag-suggestions', [\App\Http\Controllers\SearchController::class, 'getTagSuggestions'])->name('tag-suggestions');
        Route::post('/tags', [\App\Http\Controllers\SearchController::class, 'addTag'])->name('tags.add');
        Route::delete('/tags/{tag}', [\App\Http\Controllers\SearchController::class, 'removeTag'])->name('tags.remove');
        Route::post('/favorites', [\App\Http\Controllers\SearchController::class, 'toggleFavorite'])->name('favorites.toggle');
        Route::get('/favorites', [\App\Http\Controllers\SearchController::class, 'getFavorites'])->name('favorites.list');
        Route::get('/activities', [\App\Http\Controllers\SearchController::class, 'getRecentActivities'])->name('activities.list');
        Route::get('/popular-tags', [\App\Http\Controllers\SearchController::class, 'getPopularTags'])->name('popular-tags');
    });

    // External Sharing Routes
    Route::prefix('shares')->name('shares.')->group(function () {
        Route::post('/create', [\App\Http\Controllers\ExternalShareController::class, 'createShare'])->name('create');
        Route::get('/my', [\App\Http\Controllers\ExternalShareController::class, 'getMyShares'])->name('my');
        Route::delete('/revoke/{share}', [\App\Http\Controllers\ExternalShareController::class, 'revokeShare'])->name('revoke');
    });
});

// Public share access routes (no auth required)
Route::prefix('shared')->name('shared.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\ExternalShareController::class, 'accessShared'])->name('access');
    Route::get('/{token}/download', [\App\Http\Controllers\ExternalShareController::class, 'downloadShared'])->name('download');
});
