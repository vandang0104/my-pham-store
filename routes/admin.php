<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
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

// Đơn hàng (bind theo mã đơn): xem, đổi trạng thái theo luồng (huỷ = hoàn kho), đánh dấu đã thanh toán. Không có xoá.
Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/{order:code}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('orders/{order:code}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
Route::patch('orders/{order:code}/paid', [OrderController::class, 'markPaid'])->name('orders.paid');

// Tin liên hệ
Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
Route::patch('contacts/{contact}/handled', [ContactController::class, 'markHandled'])->name('contacts.handled');

// Chat trực tuyến: admin + staff
Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('chats/{conversation}', [ChatController::class, 'show'])->name('chats.show');
Route::get('chats/{conversation}/messages', [ChatController::class, 'messages'])->name('chats.messages');
Route::post('chats/{conversation}/messages', [ChatController::class, 'reply'])->name('chats.reply')->middleware('throttle:60,1');
Route::patch('chats/{conversation}/close', [ChatController::class, 'close'])->name('chats.close');

// Chỉ admin: quản lý tài khoản (khách + nhân viên gộp một màn) và thống kê doanh thu (quy tắc 9)
Route::middleware('role:admin')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::get('reports', ReportController::class)->name('reports.index');
});
