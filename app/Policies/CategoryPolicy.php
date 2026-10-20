<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/** Danh mục (docs/permissions.md): admin + staff xem/tạo/sửa; chỉ admin xoá (khi không còn SP / danh mục con) */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    public function create(User $user): bool
    {
        return $user->isBackOffice();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isBackOffice();
    }

    /** Quyền xoá; điều kiện dữ liệu (còn SP / danh mục con) do controller báo lý do cụ thể */
    public function delete(User $user, Category $category): bool
    {
        return $user->isAdmin();
    }
}
