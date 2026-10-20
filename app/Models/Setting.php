<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Cấu hình cửa hàng dạng key–value (màn quản trị cài đặt đã cắt: giá trị lấy từ SettingSeeder).
 * Đọc qua Setting::get() — cả bảng được cache một khoá, xoá cache khi lưu/xoá. Thiếu khoá → DEFAULTS.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const CACHE_KEY = 'settings.all';

    /** Giá trị mặc định khi bảng chưa có khoá (vd chạy test không seed) */
    public const DEFAULTS = [
        'shipping_fee' => '30000',
        'free_shipping_from' => '500000',
        'bank_name' => 'Ngân hàng TMCP Mẫu (dữ liệu mẫu)',
        'bank_account_number' => '0000 1234 5678',
        'bank_account_name' => 'CONG TY TNHH MOC LAM (MAU)',
        'store_hotline' => '1800 0000',
        'store_email' => 'lienhe@moclam.test',
        'store_address' => '12 Lê Lợi, Phường Bến Thành, TP. Hồ Chí Minh (địa chỉ mẫu)',
        'store_hours' => '8:00–21:00, cả cuối tuần',
        // '1' bật / '0' tắt nút chat trực tuyến
        'chat_enabled' => '1',
    ];

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return $all[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function integer(string $key, int $default = 0): int
    {
        return (int) (self::get($key) ?? $default);
    }
}
