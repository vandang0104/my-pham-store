<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

/**
 * Staff chỉ xem coupon; tạo/sửa/xoá thuộc về admin.
 */
class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->isBackOffice();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }

    /** Coupon đã dùng trong đơn thì không xoá (FK restrict) — tắt bằng is_active */
    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin() && ! $coupon->orders()->exists();
    }
}
