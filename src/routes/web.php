<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\AttendanceEditRequestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [StaffLoginController::class, 'login']);

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break.end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::get('/stamp_correction_request/list', [AttendanceEditRequestController::class, 'index'])->name('stamp_correction_request.list');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
});

Route::post('/logout', [StaffLoginController::class, 'logout'])->name('logout');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// 管理者用：勤怠詳細表示
Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
    ->name('admin.attendance_show');

Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staffList'])
    ->name('admin.attendance_staff_list');
Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])
    ->name('admin.attendance.staff');

Route::get('/attendance/{id}/detail', [AdminAttendanceController::class, 'detail'])->name('admin.attendance.detail');
Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.export_csv');