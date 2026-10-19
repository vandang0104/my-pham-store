<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Nạp trong bootstrap/app.php: prefix /admin, tên admin., middleware web + auth + role:admin,staff

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Chỉ admin: quản lý tài khoản (khách + nhân viên gộp một màn) và thống kê doanh thu (quy tắc 9).
// Tạm là trang giữ chỗ; controller làm ở S9 (tài khoản) và S10 (thống kê).
Route::middleware('role:admin')->group(function () {
    Route::view('users', 'admin.placeholder', ['title' => 'Quản lý tài khoản'])->name('users.index');
    Route::view('reports', 'admin.placeholder', ['title' => 'Thống kê doanh thu'])->name('reports.index');
});
