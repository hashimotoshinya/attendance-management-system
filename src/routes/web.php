<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\AttendanceCorrectRequestController;

Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [StaffLoginController::class, 'login']);
Route::post('/logout', [StaffLoginController::class, 'logout'])->name('logout');

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(AttendanceController::class)->group(function () {
        Route::get('/attendance', 'index')->name('attendance.index');
        Route::post('/attendance/start', 'start')->name('attendance.start');
        Route::post('/attendance/break/start', 'breakStart')->name('attendance.break.start');
        Route::post('/attendance/break/end', 'breakEnd')->name('attendance.break.end');
        Route::post('/attendance/end', 'end')->name('attendance.end');
        Route::get('/attendance/list', 'list')->name('attendance.list');
        Route::get('/attendance/{id}', 'show')->name('attendance.show');
        Route::put('/attendance/{id}', 'update')->name('attendance.update');
    });

    Route::get('/stamp_correction_request/list', [AttendanceCorrectRequestController::class, 'index'])->name('stamp_correction_request.list');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(AdminAttendanceController::class)->group(function () {
        Route::get('/attendance/list', 'index')->name('attendance.list');
        Route::get('/staff/list', 'staffList')->name('attendance_staff_list');
        Route::get('/attendance/staff/{id}', 'staffAttendance')->name('attendance.staff');
        Route::get('/attendance/staff/{id}/csv', 'exportCsv')->name('attendance.export_csv');
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/stamp_correction_request/{id}', [AttendanceCorrectRequestController::class, 'show'])->name('stamp_correction_request.show');
    Route::put('/stamp_correction_request/{id}/approve', [AttendanceCorrectRequestController::class, 'approve'])->name('stamp_correction_request.approve');
});