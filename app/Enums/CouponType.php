<?php

namespace App\Enums;

enum CouponType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Giảm theo phần trăm',
            self::Fixed => 'Giảm số tiền cố định',
        };
    }
}
