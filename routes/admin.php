<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

// Nạp trong bootstrap/app.php: prefix /admin, tên admin., middleware web + auth + role:admin,staff.
// Khách chưa đăng nhập → /admin/login (redirectGuestsTo). Quyền chi tiết theo Policy (docs/permissions.md).

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Danh mục, sản phẩm: bind theo id (khác URL khách theo slug); staff thêm/sửa, admin xoá (Policy)
Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('categories/{category:id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('categories/{category:id}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('categories/{category:id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('products', [ProductController::class, 'store'])->name('products.store');
Route::get('products/{product:id}/edit', [ProductController::class, 'edit'])->name('products.edit')->withTrashed();
Route::put('products/{product:id}', [ProductController::class, 'update'])->name('products.update')->withTrashed();
Route::delete('products/{product:id}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::patch('products/{product:id}/restore', [ProductController::class, 'restore'])->name('products.restore')->withTrashed();

// Đơn hàng, tin liên hệ: trang giữ chỗ tới bước A2
Route::view('orders', 'admin.placeholder', ['title' => 'Đơn hàng'])->name('orders.index');
Route::view('contacts', 'admin.placeholder', ['title' => 'Tin liên hệ'])->name('contacts.index');

// Chat trực tuyến: admin + staff
Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('chats/{conversation}', [ChatController::class, 'show'])->name('chats.show');
Route::get('chats/{conversation}/messages', [ChatController::class, 'messages'])->name('chats.messages');
Route::post('chats/{conversation}/messages', [ChatController::class, 'reply'])->name('chats.reply')->middleware('throttle:60,1');
Route::patch('chats/{conversation}/close', [ChatController::class, 'close'])->name('chats.close');

// Chỉ admin: quản lý tài khoản (khách + nhân viên gộp một màn) và thống kê doanh thu (quy tắc 9) — giữ chỗ tới A2
Route::middleware('role:admin')->group(function () {
    Route::view('users', 'admin.placeholder', ['title' => 'Quản lý tài khoản'])->name('users.index');
    Route::view('reports', 'admin.placeholder', ['title' => 'Thống kê doanh thu'])->name('reports.index');
});
