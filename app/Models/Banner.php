<?php

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title', 'image_desktop', 'image_mobile', 'link', 'position',
    'sort_order', 'starts_at', 'ends_at', 'is_active',
])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    /** Vị trí hero trang chủ (DESIGN.md §10.1) */
    public const POSITION_HOME_HERO = 'home_hero';

    protected static function booted(): void
    {
        // Banner hero trang chủ được cache (TTL ngắn): xoá khi banner thay đổi
        $forget = fn () => Cache::forget(config('home.cache.banners.key'));
        static::saved($forget);
        static::deleted($forget);
    }

    /** Đường dẫn công khai của ảnh trên disk public */
    public static function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** Banner đang bật và trong thời gian hiệu lực (mốc bỏ trống = không giới hạn) */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $now = now();

        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }
}
