<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\AttendanceController;

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
    Route::post('/attendance/clock-in',[AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/break-start',[AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end',[AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::post('/attendance/clock-out',[AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
});

