<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminCorrectionController;


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

Route::get('/register', [RegisterController::class, 'index'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [LoginController::class, 'create'])->name('login.create');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('profile.edit');
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login.create');
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

/* 出勤登録画面 */
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');

    /* 勤怠一覧画面 */
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    /*勤怠詳細画面*/
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}/correction', [AttendanceController::class, 'storeCorrection'])->name('attendance.correction.store');

    /*申請一覧画面*/
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])->name('stamp_correction_request.index');
});

Route::middleware('auth')->group(function () {
    /* 管理者用勤怠一覧画面 */
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
    /* 管理者用勤怠詳細画面 */
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/{id}/update', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    /*管理者用スタッフ一覧画面*/
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');
    /*スタッフ月次勤怠一覧*/
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'attendanceList'])->name('admin.staff.attendance.list');
    /*CSV出力用*/
    Route::get('admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'exportCsv'])->name('admin.staff.attendance.csv');


    /*管理者用 修正申請一覧*/
    Route::get('/admin/stamp_correction_request/list', [AdminCorrectionController::class, 'index'])
        ->name('admin.correction.index');

    /*管理者用 修正申請詳細*/
    Route::get('/admin/stamp_correction_request/{id}', [AdminCorrectionController::class, 'show'])
        ->name('admin.correction.show');

    /*管理者用 承認*/
    Route::post('/admin/stamp_correction_request/{id}/approve', [AdminCorrectionController::class, 'approve'])
        ->name('admin.correction.approve');
});






