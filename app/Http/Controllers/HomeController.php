<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

/**
 * Trang chủ (bản rút gọn): banner tĩnh, sản phẩm nổi bật, danh mục, đang ưu đãi, giới thiệu cửa hàng.
 * Không cache: mỗi khối là 1–3 truy vấn có eager load, dữ liệu (giá, tồn kho) luôn đúng.
 */
class HomeController extends Controller
{
    /** Số sản phẩm tối đa mỗi khối */
    public const PRODUCT_LIMIT = 8;

    public function __invoke(): View
    {
        return view('pages.home', [
            'banner' => $this->banner(),
            'featured' => $this->featured(),
            'categories' => $this->categories(),
            'onSale' => $this->onSale(),
        ]);
    }

    /** Banner hero đầu tiên đang hiển thị (theo sort_order); null → hero dùng mảng màu phẳng */
    private function banner(): ?Banner
    {
        return Banner::query()
            ->visible()
            ->where('position', Banner::POSITION_HOME_HERO)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /** @return Collection<int, Product> */
    private function featured(): Collection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with(['images', 'brand'])
            ->latest('id')
            ->limit(self::PRODUCT_LIMIT)
            ->get();
    }

    /** @return Collection<int, Product> */
    private function onSale(): Collection
    {
        return Product::query()
            ->active()
            ->onSale()
            ->with(['images', 'brand'])
            ->orderByDiscount()
            ->limit(self::PRODUCT_LIMIT)
            ->get();
    }

    /**
     * Danh mục cha đang bật kèm danh mục con và số sản phẩm đang bán (2 truy vấn).
     *
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        $activeProducts = fn (Builder $query) => $query->active();

        return Category::query()
            ->active()
            ->roots()
            ->orderBy('sort_order')
            ->with(['children' => fn ($query) => $query->active()->withCount(['products' => $activeProducts])])
            ->get();
    }
}
