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
        Route::delete('/{file}', [\App\Http\Controllers\FileController::class, 'deleteFile'])->name('delete');
        Route::delete('/folders/{folder}', [\App\Http\Controllers\FileController::class, 'deleteFolder'])->name('folders.delete');
        Route::post('/{file}/permissions', [\App\Http\Controllers\FileController::class, 'assignPermissions'])->name('permissions.assign');
        Route::delete('/{file}/permissions/{permission}', [\App\Http\Controllers\FileController::class, 'removePermission'])->name('permissions.remove');
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
