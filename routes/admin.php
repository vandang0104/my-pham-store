<?php

use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Nạp trong bootstrap/app.php: prefix /admin, tên admin., middleware web + auth + role:admin,staff

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Chat trực tuyến: admin + staff
Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('chats/{conversation}', [ChatController::class, 'show'])->name('chats.show');
Route::get('chats/{conversation}/messages', [ChatController::class, 'messages'])->name('chats.messages');
Route::post('chats/{conversation}/messages', [ChatController::class, 'reply'])->name('chats.reply')->middleware('throttle:60,1');
Route::patch('chats/{conversation}/close', [ChatController::class, 'close'])->name('chats.close');

// Chỉ admin: quản lý tài khoản (khách + nhân viên gộp một màn) và thống kê doanh thu (quy tắc 9).
// Tạm là trang giữ chỗ; controller làm ở S9 (tài khoản) và S10 (thống kê).
Route::middleware('role:admin')->group(function () {
    Route::view('users', 'admin.placeholder', ['title' => 'Quản lý tài khoản'])->name('users.index');
    Route::view('reports', 'admin.placeholder', ['title' => 'Thống kê doanh thu'])->name('reports.index');
});
