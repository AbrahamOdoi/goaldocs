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

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/verify/method', [VerificationController::class, 'chooseMethod'])->name('verification.method');
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
});
