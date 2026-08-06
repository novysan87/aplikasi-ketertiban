<?php

use App\Http\Controllers\Api\ParentAuthController;
use App\Http\Controllers\Api\ParentNotificationController;
use App\Http\Controllers\Api\ParentSchoolInformationController;
use App\Http\Controllers\Api\ParentStudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Aplikasi Wali Murid (Mobile iOS & Android)
|--------------------------------------------------------------------------
| Base URL: https://tatib.smkn1-wonorejo.sch.id/api/v1
| Auth: Laravel Sanctum (Bearer token)
*/

// ===== Publik: registrasi & login wali murid =====
Route::post('/parent/register', [ParentAuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('api.parent.register');

Route::post('/parent/login', [ParentAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.parent.login');

// ===== Terproteksi: Bearer token =====
Route::middleware('auth:sanctum')->prefix('parent')->group(function () {
    // Akun
    Route::get('/me', [ParentAuthController::class, 'me'])->name('api.parent.me');
    Route::get('/school-info', [ParentAuthController::class, 'schoolInfo'])->name('api.parent.school-info');
    Route::post('/logout', [ParentAuthController::class, 'logout'])->name('api.parent.logout');

    // Anak (wali)
    Route::get('/students', [ParentStudentController::class, 'index'])->name('api.parent.students');
    Route::post('/students/link', [ParentStudentController::class, 'link'])->middleware('throttle:5,10')->name('api.parent.students.link');
    Route::get('/students/{student}/violations', [ParentStudentController::class, 'violations'])->name('api.parent.students.violations');
    Route::get('/students/{student}/sp-letters', [ParentStudentController::class, 'spLetters'])->name('api.parent.students.sp-letters');
    Route::get('/students/{student}/schedule', [ParentStudentController::class, 'schedule'])->name('api.parent.students.schedule');
    Route::get('/students/{student}/attendance', [ParentStudentController::class, 'attendance'])->name('api.parent.students.attendance');
    Route::get('/students/{student}/points-history', [ParentStudentController::class, 'pointsHistory'])->name('api.parent.students.points-history');

    // Notifikasi & perangkat
    Route::get('/notifications', [ParentNotificationController::class, 'index'])->name('api.parent.notifications');
    Route::get('/school-informations', [ParentSchoolInformationController::class, 'index'])->name('api.parent.school-informations');
    Route::post('/devices', [ParentNotificationController::class, 'registerDevice'])->name('api.parent.devices');
    Route::post('/push-debug', [ParentNotificationController::class, 'pushDebug'])->name('api.parent.push-debug');
});
