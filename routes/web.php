<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Xác thực — URL tiếng Việt, giữ tên route chuẩn của Laravel
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [LoginController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [LoginController::class, 'store']);

    Route::get('/dang-ky', [RegisterController::class, 'create'])->name('register');
    Route::post('/dang-ky', [RegisterController::class, 'store']);

    Route::get('/quen-mat-khau', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/quen-mat-khau', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/dat-lai-mat-khau/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::post('/dang-xuat', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
