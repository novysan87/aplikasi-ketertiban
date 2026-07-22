<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ImportController;
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
    Route::get('/calendar/data', [DashboardController::class, 'getCalendarData'])->name('calendar.data');
    Route::get('/attendances', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/create', [\App\Http\Controllers\AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/attendances', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/attendances/recap', [\App\Http\Controllers\AttendanceController::class, 'recap'])->name('attendances.recap');
    Route::get('/attendances/calendar-data', [\App\Http\Controllers\AttendanceController::class, 'calendarData'])->name('attendances.calendar-data');

    Route::get('/notifications/recent', [DashboardController::class, 'getRecentNotifications'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    Route::get('/notifications', [DashboardController::class, 'notificationsIndex'])->name('notifications.index');

    // Violations
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
    Route::get('/violations/create', [ViolationController::class, 'create'])->name('violations.create');
    Route::post('/violations', [ViolationController::class, 'store'])->name('violations.store');
    Route::get('/violations/{violation}', [ViolationController::class, 'show'])->name('violations.show');
    Route::post('/violations/{violation}/verify', [ViolationController::class, 'verify'])->name('violations.verify');
    Route::delete('/violations/{violation}', [ViolationController::class, 'destroy'])->name('violations.destroy');
    Route::post('/violations/{violation}/handling', [ViolationController::class, 'storeHandling'])->name('violations.handling.store');
    Route::post('/violations/{violation}/resolve', [ViolationController::class, 'resolveHandling'])->name('violations.resolve');
    Route::delete('/violations/{violation}/handling/{handling}', [ViolationController::class, 'destroyHandling'])->name('violations.handling.destroy');
    Route::get('/api/students/search', [ViolationController::class, 'searchStudents'])->name('api.students.search');

    // Students
    Route::get('/students', [StudentReportController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentReportController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentReportController::class, 'update'])->name('students.update');

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

    // Import
    Route::get('/settings/import/template', [ImportController::class, 'downloadTemplate'])->name('settings.import.template');
    Route::get('/settings/export/violation-types', [ImportController::class, 'exportViolationTypes'])->name('settings.export.violation-types');
    Route::get('/settings/backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('settings.backup');
    Route::post('/settings/backup/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('settings.backup.create');
    Route::get('/settings/backup/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('settings.backup.download');
    Route::delete('/settings/backup/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('settings.backup.destroy');
    Route::post('/settings/backup/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('settings.backup.restore');
    Route::get('/export/violations', [\App\Http\Controllers\Admin\ViolationExportController::class, 'export'])->name('violations.export');
    Route::post('/settings/import/violation-types', [ImportController::class, 'importViolationTypes'])->name('settings.import.violation-types');

    Route::get('/settings/thresholds', [MasterDataController::class, 'thresholds'])->name('settings.thresholds');
    Route::put('/settings/thresholds', [MasterDataController::class, 'updateThresholds'])->name('settings.thresholds.update');
    Route::post('/settings/thresholds', [MasterDataController::class, 'storeThreshold'])->name('settings.thresholds.store');
    Route::delete('/settings/thresholds/{threshold}', [MasterDataController::class, 'destroyThreshold'])->name('settings.thresholds.destroy');

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
