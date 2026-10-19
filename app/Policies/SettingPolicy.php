<?php

namespace App\Policies;

use App\Models\User;

/**
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
