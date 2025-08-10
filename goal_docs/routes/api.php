<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Mobile API Routes
Route::prefix('mobile')->name('mobile.')->group(function () {
    // Public routes (no authentication required)
    Route::post('/login', [MobileApiController::class, 'login'])->name('login');
    
    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileApiController::class, 'logout'])->name('logout');
        Route::get('/profile', [MobileApiController::class, 'profile'])->name('profile');
        Route::get('/files', [MobileApiController::class, 'files'])->name('files');
        Route::get('/folders', [MobileApiController::class, 'folders'])->name('folders');
        Route::get('/files/{fileId}', [MobileApiController::class, 'fileDetails'])->name('file-details');
        Route::get('/files/{fileId}/download', [MobileApiController::class, 'download'])->name('download');
        Route::get('/files/{fileId}/preview', [MobileApiController::class, 'preview'])->name('preview');
        Route::post('/upload', [MobileApiController::class, 'upload'])->name('upload');
        Route::post('/folders', [MobileApiController::class, 'createFolder'])->name('create-folder');
        Route::get('/search', [MobileApiController::class, 'search'])->name('search');
        Route::get('/stats', [MobileApiController::class, 'stats'])->name('stats');
        Route::post('/refresh-session', [MobileApiController::class, 'refreshSession'])->name('refresh-session');
    });
});

// API Documentation
Route::get('/docs', function () {
    return response()->json([
        'message' => 'GoalDocs Mobile API',
        'version' => '1.0.0',
        'endpoints' => [
            'mobile' => [
                'POST /api/mobile/login' => 'Authenticate mobile user',
                'POST /api/mobile/logout' => 'Logout mobile user',
                'GET /api/mobile/profile' => 'Get user profile',
                'GET /api/mobile/files' => 'Get user files',
                'GET /api/mobile/folders' => 'Get user folders',
                'GET /api/mobile/files/{id}' => 'Get file details',
                'GET /api/mobile/files/{id}/download' => 'Download file',
                'GET /api/mobile/files/{id}/preview' => 'Preview file',
                'POST /api/mobile/upload' => 'Upload file',
                'POST /api/mobile/folders' => 'Create folder',
                'GET /api/mobile/search' => 'Search files and folders',
                'GET /api/mobile/stats' => 'Get user statistics',
                'POST /api/mobile/refresh-session' => 'Refresh session',
            ],
        ],
    ]);
})->name('api.docs');
