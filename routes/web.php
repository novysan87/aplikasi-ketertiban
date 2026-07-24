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

// ===== Protected Routes (auth required) =====
Route::middleware('auth')->group(function () {

    // ===== Umum (permission-based) =====

    Route::middleware('permission:access-dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/calendar/data', [DashboardController::class, 'getCalendarData'])->name('calendar.data');
    });

    Route::middleware('permission:view-notifications')->group(function () {
        Route::get('/notifications/unread-count', [DashboardController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/recent', [DashboardController::class, 'getRecentNotifications'])->name('notifications.recent');
        Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
        Route::get('/notifications', [DashboardController::class, 'notificationsIndex'])->name('notifications.index');
    });

    Route::middleware('permission:input-violations')->group(function () {
        Route::get('/violations/create', [ViolationController::class, 'create'])->name('violations.create');
        Route::post('/violations', [ViolationController::class, 'store'])->name('violations.store');
    });

    Route::middleware('permission:view-violations')->group(function () {
        Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
        Route::get('/violations/{violation}', [ViolationController::class, 'show'])->name('violations.show');
        Route::post('/violations/{violation}/verify', [ViolationController::class, 'verify'])->name('violations.verify');
        Route::delete('/violations/{violation}', [ViolationController::class, 'destroy'])->name('violations.destroy');
        Route::post('/violations/{violation}/handling', [ViolationController::class, 'storeHandling'])->name('violations.handling.store');
        Route::post('/violations/{violation}/resolve', [ViolationController::class, 'resolveHandling'])->name('violations.resolve');
        Route::delete('/violations/{violation}/handling/{handling}', [ViolationController::class, 'destroyHandling'])->name('violations.handling.destroy');
        Route::get('/api/students/search', [ViolationController::class, 'searchStudents'])->name('api.students.search');
    });

    Route::middleware('permission:view-students')->group(function () {
        Route::get('/students', [StudentReportController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentReportController::class, 'show'])->name('students.show');
        Route::put('/students/{student}', [StudentReportController::class, 'update'])->name('students.update');
    });

    Route::middleware('permission:manage-attendance')->group(function () {
        Route::get('/attendances', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/attendances/create', [\App\Http\Controllers\AttendanceController::class, 'create'])->name('attendances.create');
        Route::post('/attendances', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('attendances.store');
        Route::get('/attendances/recap', [\App\Http\Controllers\AttendanceController::class, 'recap'])->name('attendances.recap');
        Route::get('/attendances/calendar-data', [\App\Http\Controllers\AttendanceController::class, 'calendarData'])->name('attendances.calendar-data');
        Route::get('/attendances/export-weekly', [\App\Http\Controllers\AttendanceController::class, 'exportWeekly'])->name('attendances.export-weekly');
        Route::get('/attendances/export-monthly', [\App\Http\Controllers\AttendanceController::class, 'exportMonthly'])->name('attendances.export-monthly');
    });

    Route::middleware('permission:view-sp-letters')->group(function () {
        Route::get('/sp-letters', [SpLetterController::class, 'index'])->name('sp-letters.index');
        Route::get('/sp-letters/{spLetter}', [SpLetterController::class, 'show'])->name('sp-letters.show');
        Route::get('/sp-letters/{spLetter}/print', [SpLetterController::class, 'print'])->name('sp-letters.print');
    });

    // ===== Master Data (permission-based) =====
    Route::middleware('permission:categories-manage')->group(function () {
        Route::get('/settings/categories', [MasterDataController::class, 'categories'])->name('settings.categories');
        Route::post('/settings/categories', [MasterDataController::class, 'storeCategory'])->name('settings.categories.store');
        Route::put('/settings/categories/{category}', [MasterDataController::class, 'updateCategory'])->name('settings.categories.update');
        Route::delete('/settings/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('settings.categories.destroy');
    });

    Route::middleware('permission:violation-types-manage')->group(function () {
        Route::get('/settings/violation-types', [MasterDataController::class, 'types'])->name('settings.violation-types');
        Route::post('/settings/violation-types', [MasterDataController::class, 'storeType'])->name('settings.violation-types.store');
        Route::put('/settings/violation-types/{type}', [MasterDataController::class, 'updateType'])->name('settings.violation-types.update');
        Route::delete('/settings/violation-types/{type}', [MasterDataController::class, 'destroyType'])->name('settings.violation-types.destroy');
    });

    Route::middleware('permission:thresholds-manage')->group(function () {
        Route::get('/settings/thresholds', [MasterDataController::class, 'thresholds'])->name('settings.thresholds');
        Route::put('/settings/thresholds', [MasterDataController::class, 'updateThresholds'])->name('settings.thresholds.update');
        Route::post('/settings/thresholds', [MasterDataController::class, 'storeThreshold'])->name('settings.thresholds.store');
        Route::delete('/settings/thresholds/{threshold}', [MasterDataController::class, 'destroyThreshold'])->name('settings.thresholds.destroy');
    });

    Route::middleware('permission:violations-export')->group(function () {
        Route::get('/export/violations', [\App\Http\Controllers\Admin\ViolationExportController::class, 'export'])->name('violations.export');
    });

    // ===== Administrasi (permission-based) =====
    Route::middleware('permission:settings-manage')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:import-data')->group(function () {
        Route::get('/settings/import/template', [ImportController::class, 'downloadTemplate'])->name('settings.import.template');
        Route::get('/settings/export/violation-types', [ImportController::class, 'exportViolationTypes'])->name('settings.export.violation-types');
        Route::post('/settings/import/violation-types', [ImportController::class, 'importViolationTypes'])->name('settings.import.violation-types');
    });

    Route::middleware('permission:backup-database')->group(function () {
        Route::get('/settings/backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('settings.backup');
        Route::post('/settings/backup/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('settings.backup.create');
        Route::get('/settings/backup/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('settings.backup.download');
        Route::delete('/settings/backup/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('settings.backup.destroy');
        Route::post('/settings/backup/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('settings.backup.restore');
    });

    Route::middleware('permission:sync-data')->group(function () {
        Route::get('/settings/sync', [SyncController::class, 'index'])->name('settings.sync');
        Route::post('/settings/sync', [SyncController::class, 'syncNow'])->name('settings.sync.run');
        Route::post('/settings/sync/test', [SyncController::class, 'testKesiswaanConnection'])->name('settings.sync.test');
        Route::post('/settings/sync/ejurnal-token', [SyncController::class, 'saveEjurnalToken'])->name('settings.sync.ejurnal-token');
        Route::post('/settings/sync/ejurnal-token/generate', [SyncController::class, 'generateEjurnalToken'])->name('settings.sync.ejurnal-token.generate');
    });

    Route::middleware('permission:users-manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/settings/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('settings.permissions');
        Route::post('/settings/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('settings.permissions.update');
    });

    Route::middleware('permission:reset-application')->group(function () {
        Route::get('/settings/reset', [\App\Http\Controllers\Admin\ResetController::class, 'index'])->name('settings.reset');
        Route::post('/settings/reset', [\App\Http\Controllers\Admin\ResetController::class, 'reset'])->name('settings.reset.run');
    });

    // Profile (all roles — no permission needed, manage own account)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ===== API: Internal Sync (token auth, not session) =====
Route::post('/api/v1/attendance/sync', [App\Http\Controllers\Api\AttendanceSyncController::class, 'sync'])
    ->name('api.attendance.sync');
Route::get('/api/v1/attendance/ping', [App\Http\Controllers\Api\AttendanceSyncController::class, 'ping'])
    ->name('api.attendance.ping');
