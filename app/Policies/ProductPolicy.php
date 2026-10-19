<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Quản lý sản phẩm trong khu quản trị. Trang khách xem sản phẩm công khai, không qua policy.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isBackOffice();
    }

    public function create(User $user): bool
    {
        return $user->isBackOffice();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isBackOffice();
    }

    /** Xoá mềm: chỉ admin (staff không xoá dữ liệu) */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /** Xoá cứng: chỉ admin và chỉ khi sản phẩm chưa từng có trong đơn (quy tắc 10) */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin() && ! $product->orderItems()->exists();
    }
}
