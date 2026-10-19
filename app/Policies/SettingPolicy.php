<?php

namespace App\Policies;

use App\Models\User;

/**
 * ĐÃ CẮT màn cài đặt (phạm vi rút gọn 19/10): bảng settings vẫn dùng để đọc cấu hình (vd ngưỡng miễn phí giao hàng);
 * giữ policy vì test còn dùng.
 * Cài đặt cửa hàng là một trang chung, không theo từng bản ghi: Gate::allows('update', Setting::class).
 * Chỉ admin (quy tắc 9).
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}
