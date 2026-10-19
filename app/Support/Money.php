<?php

namespace App\Support;

/**
 * Định dạng tiền VND. Dùng number_format thay cho Illuminate\Support\Number vì máy chạy không có ext-intl.
 */
final class Money
{
    /** Ký hiệu đồng, cách số bằng khoảng trắng không ngắt dòng */
    public const SYMBOL = "\u{00A0}₫";

    /**
     * Vd 350000 → "350.000 ₫".
     */
    public static function format(int $amount): string
    {
        return number_format($amount, 0, ',', '.').self::SYMBOL;
    }
}
