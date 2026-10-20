<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Quản lý tài khoản trong khu quản trị (khách + nhân viên gộp một màn): CHỈ admin (quy tắc 9, phạm vi rút gọn).
 * Luôn còn ít nhất 1 admin đang hoạt động.
 */
class UserPolicy
{
    /** Danh sách tài khoản */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /** Tạo tài khoản nhân viên */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Sửa thông tin tài khoản người khác trong khu quản trị (khách tự sửa hồ sơ ở /tai-khoan, không qua đây) */
    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /** Đổi vai trò: chỉ admin; không được hạ quyền admin đang hoạt động cuối cùng */
    public function changeRole(User $user, User $target, UserRole $newRole): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }
        // Không tự hạ quyền chính mình (tránh tự khoá mình khỏi khu quản trị)
        if ($user->is($target) && $newRole !== UserRole::Admin) {
            return false;
        }

        return $newRole === UserRole::Admin || ! $this->isLastActiveAdmin($target);
    }

    /** Khoá / mở khoá tài khoản: chỉ admin; không tự khoá mình, không khoá admin đang hoạt động cuối cùng */
    public function toggleActive(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $user->is($target)
            && ! ($target->is_active && $this->isLastActiveAdmin($target));
    }

    /** Xoá tài khoản: chỉ admin; không tự xoá mình, không xoá admin đang hoạt động cuối cùng */
    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $user->is($target)
            && ! $this->isLastActiveAdmin($target);
    }

    private function isLastActiveAdmin(User $target): bool
    {
        if ($target->role !== UserRole::Admin || ! $target->is_active) {
            return false;
        }

        return User::query()->where('role', UserRole::Admin)->where('is_active', true)->count() <= 1;
    }
}
