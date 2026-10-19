<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Nạp trong bootstrap/app.php: prefix /admin, tên admin., middleware web + auth + role:admin,staff

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Chỉ admin: nhân viên, cài đặt, thống kê doanh thu (quy tắc 9). Tạm là trang giữ chỗ, controller làm ở giai đoạn quản trị.
Route::middleware('role:admin')->group(function () {
    Route::view('staff', 'admin.placeholder', ['title' => 'Quản lý nhân viên'])->name('staff.index');
    Route::view('settings', 'admin.placeholder', ['title' => 'Cài đặt cửa hàng'])->name('settings.edit');
    Route::view('reports', 'admin.placeholder', ['title' => 'Thống kê doanh thu'])->name('reports.index');
});
