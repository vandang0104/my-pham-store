<?php

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    /** URL ảnh desktop trên disk public (đường dẫn tương đối "/storage/…") */
    protected function desktopUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_desktop ? Storage::disk('public')->url($this->image_desktop) : null);
    }

    /** URL ảnh mobile; thiếu thì dùng ảnh desktop */
    protected function mobileUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_mobile ? Storage::disk('public')->url($this->image_mobile) : $this->desktop_url);
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
