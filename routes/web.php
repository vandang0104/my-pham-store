<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StyleguideController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Sản phẩm: danh sách (tìm + lọc), theo danh mục, theo thương hiệu, chi tiết — bind theo slug
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/danh-muc/{category:slug}', [ProductController::class, 'category'])->name('categories.show');
Route::get('/thuong-hieu/{brand:slug}', [ProductController::class, 'brand'])->name('brands.show');

// Giỏ hàng (khách vãng lai: session; đã đăng nhập: DB). {product:id} kèm withTrashed để xoá được dòng của SP đã xoá mềm
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{product:id}', [CartController::class, 'update'])->name('cart.update')->withTrashed();
Route::delete('/gio-hang/{product:id}', [CartController::class, 'destroy'])->name('cart.destroy')->withTrashed();

// Hỗ trợ: FAQ chỉ hiển thị từ seed; liên hệ lưu contact_messages (giới hạn 3 lượt / 10 phút theo IP trong ContactRequest)
Route::get('/cau-hoi-thuong-gap', FaqController::class)->name('faq.index');
Route::get('/lien-he', [ContactController::class, 'create'])->name('contact.create');
Route::post('/lien-he', [ContactController::class, 'store'])->name('contact.store');

// Thanh toán (khách vãng lai được đặt hàng — quy tắc 7) và trang xác nhận đơn (bind theo mã đơn)
Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/dat-hang-thanh-cong/{order:code}', [CheckoutController::class, 'success'])->name('checkout.success');

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

// Tài khoản khách hàng (chưa đăng nhập → /dang-nhap). Sổ địa chỉ, mua lại, avatar: đã cắt.
Route::middleware('auth')->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('dashboard');
    Route::get('/thong-tin', [Account\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/thong-tin', [Account\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/mat-khau', [Account\ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/mat-khau', [Account\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('/don-hang', [Account\OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order:code}', [Account\OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang/{order:code}/huy', [Account\OrderController::class, 'cancel'])->name('orders.cancel');
});

// Trang xem token và component theo DESIGN.md — chỉ đăng ký ở môi trường local
if (app()->environment('local')) {
    Route::get('/styleguide', StyleguideController::class)->name('styleguide');
}
