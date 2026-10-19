<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Thẻ sản phẩm dạng "ảnh + nhãn treo" (DESIGN.md §9).
 * Truyền sản phẩm đã nạp sẵn images và brand (->with(['images', 'brand'])) để tránh N+1.
 * $index quyết định tông màu dải nhãn (xoay vòng 3 tông) để lưới không đơn điệu.
 */
class ProductCard extends Component
{
    /** Tông dải nhãn: 0 giấy dó, 1 ngọc nhạt, 2 sơn mài (chữ sáng) */
    public const TONES = 3;

    public string $url;

    public ?string $imageUrl;

    /** Ảnh là placeholder (mảng màu) → vẽ tên sản phẩm bằng Cormorant lên trên bằng HTML */
    public bool $isPlaceholder;

    public bool $outOfStock;

    public int $tone;

    public string $meta;

    public string $headingTag;

    public function __construct(public Product $product, int $index = 0, int $headingLevel = 3)
    {
        $this->url = url('/san-pham/'.$product->slug);
        $image = $product->primary_image;
        $this->imageUrl = $image?->url;
        $this->isPlaceholder = (bool) $image?->is_placeholder;
        $this->outOfStock = $product->stock <= 0;
        $this->tone = $index % self::TONES;
        $this->meta = collect([$product->volume, $product->skin_type?->label()])->filter()->implode(' · ');
        $this->headingTag = 'h'.max(2, min(6, $headingLevel));
    }

    public function render(): View
    {
        return view('components.product-card');
    }
}
