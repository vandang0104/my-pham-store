<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Quản trị viên',
            self::Staff => 'Nhân viên',
            self::Customer => 'Khách hàng',
        };
    }

    /** Vai trò được vào khu quản trị /admin */
    public function isBackOffice(): bool
    {
        return $this === self::Admin || $this === self::Staff;
    }
}
