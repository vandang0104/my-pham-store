<?php

namespace App\Http\Controllers;

use App\Enums\SkinType;
use App\Models\Product;
use App\View\Components\Icon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Trang /styleguide (chỉ môi trường local): xem bằng mắt token và component theo DESIGN.md.
 */
class StyleguideController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.styleguide', [
            'colors' => self::colors(),
            'typeSteps' => self::typeSteps(),
            'skinTypes' => SkinType::cases(),
            'cards' => self::sampleProducts(),
            'icons' => array_keys(Icon::PATHS),
            'paginated' => Product::query()->active()->paginate(4, ['id'])->withQueryString(),
        ]);
    }

    /**
     * 4 sản phẩm minh hoạ đủ trạng thái thẻ: đang giảm giá, giá thường, hết hàng, giảm giá (tông tối).
     *
     * @return Collection<int, Product>
     */
    public static function sampleProducts(): Collection
    {
        $base = fn () => Product::query()->active()->with(['images', 'brand']);

        return collect([
            $base()->onSale()->orderBy('id')->first(),
            $base()->whereNull('sale_price')->where('stock', '>', 0)->orderBy('id')->first(),
            $base()->where('stock', 0)->first(),
            $base()->onSale()->orderByDesc('id')->first(),
        ])->filter()->unique('id')->values();
    }

    /**
     * Bảng màu (DESIGN.md §3).
     *
     * @return list<array{name: string, token: string, hex: string, role: string}>
     */
    public static function colors(): array
    {
        return [
            ['name' => 'Lụa', 'token' => '--c-ground', 'hex' => '#F5F2EC', 'role' => 'Nền chính'],
            ['name' => 'Giấy dó', 'token' => '--c-surface', 'hex' => '#EBE5DA', 'role' => 'Bậc bề mặt'],
            ['name' => 'Ngọc nhạt', 'token' => '--c-field', 'hex' => '#D3DDD4', 'role' => 'Mảng nền giữa trang'],
            ['name' => 'Champagne', 'token' => '--c-champagne', 'hex' => '#C2A878', 'role' => 'Mảng, nút trên nền tối — không làm chữ trên nền sáng'],
            ['name' => 'Sơn mài', 'token' => '--c-ink', 'hex' => '#1E1B18', 'role' => 'Chữ chính, nền tối'],
            ['name' => 'Sơn mài 2', 'token' => '--c-ink-2', 'hex' => '#2B241E', 'role' => 'Bề mặt trên nền tối'],
            ['name' => 'Mực nhạt', 'token' => '--c-muted', 'hex' => '#5C544C', 'role' => 'Chữ phụ trên nền sáng'],
            ['name' => 'Mực nhạt trên tối', 'token' => '--c-muted-dark', 'hex' => '#B8AE9F', 'role' => 'Chữ phụ trên nền tối'],
            ['name' => 'Men ngọc', 'token' => '--c-accent', 'hex' => '#2E5A4C', 'role' => 'Màu nhấn duy nhất: nút chính, link, focus'],
            ['name' => 'Men ngọc đậm', 'token' => '--c-accent-hover', 'hex' => '#234539', 'role' => 'Hover nút chính'],
            ['name' => 'Đỏ son', 'token' => '--c-sale', 'hex' => '#8E2F23', 'role' => 'Giá khuyến mãi, lỗi'],
            ['name' => 'Đỏ son nhạt', 'token' => '--c-error-bg', 'hex' => '#F1DEDA', 'role' => 'Nền khung báo lỗi'],
            ['name' => 'Hổ phách', 'token' => '--c-warning', 'hex' => '#7A5410', 'role' => 'Cảnh báo, sắp hết hàng'],
            ['name' => 'Viền ô nhập', 'token' => '--c-line', 'hex' => '#8A8174', 'role' => 'Chỉ viền ô nhập, bảng'],
        ];
    }

    /**
     * Thang 5 bậc cỡ chữ (DESIGN.md §4).
     *
     * @return list<array{class: string, label: string, spec: string, sample: string}>
     */
    public static function typeSteps(): array
    {
        return [
            ['class' => 't-display', 'label' => 'Lớn nhất', 'spec' => 'Cormorant 600 · 40 → 88px · 1.1', 'sample' => 'Dưỡng ẩm dịu nhẹ từ Đà Lạt'],
            ['class' => 't-title', 'label' => 'Tiêu đề', 'spec' => 'Cormorant 600 · 28 → 40px · 1.15', 'sample' => 'Kem dưỡng Ceramide Phục Hồi'],
            ['class' => 't-lead', 'label' => 'Nhấn', 'spec' => 'Be Vietnam Pro 500 · 20 → 24px · 1.35', 'sample' => 'Làn da đều màu hơn sau bốn tuần sử dụng'],
            ['class' => 't-body', 'label' => 'Nội dung', 'spec' => 'Be Vietnam Pro 400 · 16 → 17px · 1.55', 'sample' => 'Tinh chất mỏng nhẹ, thấm nhanh, không bết dính; dùng buổi sáng trước kem chống nắng. Thử dấu: ệ ữ ễ ẫ ở ổ — Ể Ữ Ỗ Ẫ.'],
            ['class' => 't-small', 'label' => 'Nhỏ', 'spec' => 'Be Vietnam Pro 500 · 13 → 14px · 1.4', 'sample' => 'Dermalis Lab · 30ml · Da hỗn hợp · Còn 4 sản phẩm'],
        ];
    }
}
