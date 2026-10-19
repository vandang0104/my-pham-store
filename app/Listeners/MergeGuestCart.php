<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;

/**
 * Gộp giỏ session vào giỏ DB khi đăng nhập (quy tắc 8). Sự kiện Login cũng bắn khi đăng ký thành công
 * (RegisterController gọi Auth::login) nên một listener phủ cả hai luồng. Laravel tự phát hiện listener trong app/Listeners.
 * Lưu ý: Login bắn TRƯỚC khi guard gán user → dùng $event->user, không dùng Auth::user().
 */
class MergeGuestCart
{
    public function __construct(private readonly CartService $cart) {}

    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->cart->mergeGuestCart($event->user);
        }
    }
}
