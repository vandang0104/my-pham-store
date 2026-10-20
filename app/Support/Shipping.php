<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Phí giao hàng đồng giá theo settings: shipping_fee, miễn phí khi tạm tính ≥ free_shipping_from.
 */
final class Shipping
{
    public static function fee(int $subtotal): int
    {
        return $subtotal >= self::freeFrom() ? 0 : Setting::integer('shipping_fee');
    }

    public static function freeFrom(): int
    {
        return Setting::integer('free_shipping_from');
    }

    /** Số tiền còn thiếu để được miễn phí giao hàng (0 nếu đã đạt) */
    public static function remainingForFree(int $subtotal): int
    {
        return max(0, self::freeFrom() - $subtotal);
    }
}
