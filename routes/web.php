<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SpLetterController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications/unread-count', [DashboardController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [DashboardController::class, 'getRecentNotifications'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

    // Violations
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
    Route::get('/violations/create', [ViolationController::class, 'create'])->name('violations.create');
    Route::post('/violations', [ViolationController::class, 'store'])->name('violations.store');
    Route::get('/violations/{violation}', [ViolationController::class, 'show'])->name('violations.show');
    Route::post('/violations/{violation}/verify', [ViolationController::class, 'verify'])->name('violations.verify');
    Route::delete('/violations/{violation}', [ViolationController::class, 'destroy'])->name('violations.destroy');
    Route::get('/api/students/search', [ViolationController::class, 'searchStudents'])->name('api.students.search');

    // Students
    Route::get('/students', [StudentReportController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentReportController::class, 'show'])->name('students.show');

    // SP Letters
    Route::get('/sp-letters', [SpLetterController::class, 'index'])->name('sp-letters.index');
    Route::get('/sp-letters/{spLetter}', [SpLetterController::class, 'show'])->name('sp-letters.show');
    Route::get('/sp-letters/{spLetter}/print', [SpLetterController::class, 'print'])->name('sp-letters.print');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Master Data
    Route::get('/settings/categories', [MasterDataController::class, 'categories'])->name('settings.categories');
    Route::post('/settings/categories', [MasterDataController::class, 'storeCategory'])->name('settings.categories.store');
    Route::put('/settings/categories/{category}', [MasterDataController::class, 'updateCategory'])->name('settings.categories.update');
    Route::delete('/settings/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('settings.categories.destroy');

    Route::get('/settings/violation-types', [MasterDataController::class, 'types'])->name('settings.violation-types');
    Route::post('/settings/violation-types', [MasterDataController::class, 'storeType'])->name('settings.violation-types.store');
    Route::put('/settings/violation-types/{type}', [MasterDataController::class, 'updateType'])->name('settings.violation-types.update');
    Route::delete('/settings/violation-types/{type}', [MasterDataController::class, 'destroyType'])->name('settings.violation-types.destroy');

    Route::get('/settings/thresholds', [MasterDataController::class, 'thresholds'])->name('settings.thresholds');
    Route::put('/settings/thresholds', [MasterDataController::class, 'updateThresholds'])->name('settings.thresholds.update');

    // Sync
    Route::get('/settings/sync', [SyncController::class, 'index'])->name('settings.sync');
    Route::post('/settings/sync', [SyncController::class, 'syncNow'])->name('settings.sync.run');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Reset Application
    Route::get('/settings/reset', [App\Http\Controllers\Admin\ResetController::class, 'index'])->name('settings.reset');
    Route::post('/settings/reset', [App\Http\Controllers\Admin\ResetController::class, 'reset'])->name('settings.reset.run');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
