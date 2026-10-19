<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Trang chủ (DESIGN.md §10.1): hero banner, sản phẩm nổi bật, danh mục, đang ưu đãi, câu chuyện, lời mời.
 * Cách cache từng khối ghi ở config/home.php.
 */
class HomeController extends Controller
{
    /** Số tông màu xoay vòng của các thanh danh mục */
    private const CATEGORY_TONES = 4;

    public function __invoke(Request $request): View
    {
        $categories = self::categories();

        return view('pages.home', [
            'banners' => self::banners(),
            'featured' => $this->featured(),
            'categories' => $categories,
            'categoryTotals' => [
                'children' => array_sum(array_map(fn (array $root) => count($root['children']), $categories)),
                'products' => array_sum(array_column($categories, 'count')),
            ],
            'onSale' => $this->onSale(),
            'skinTypeUrl' => $this->skinTypeUrl($request),
        ]);
    }

    /**
     * Banner hero đang hiển thị, theo sort_order.
     * Cache chỉ giữ dữ liệu thô (đường dẫn tương đối); URL tuyệt đối dựng theo request hiện tại
     * để không dính host của request đầu tiên (localhost / tên miền tunnel).
     *
     * @return list<array{title: string, url: string, desktop: ?string, mobile: ?string}>
     */
    public static function banners(): array
    {
        $cache = config('home.cache.banners');

        $rows = Cache::remember($cache['key'], $cache['ttl'], fn () => Banner::query()
            ->visible()
            ->where('position', Banner::POSITION_HOME_HERO)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'link', 'image_desktop', 'image_mobile'])
            ->map(fn (Banner $banner) => $banner->only(['title', 'link', 'image_desktop', 'image_mobile']))
            ->all());

        return array_map(fn (array $row) => [
            'title' => $row['title'],
            'url' => url($row['link'] ?: '/san-pham'),
            'desktop' => Banner::imageUrl($row['image_desktop']),
            // Thiếu ảnh mobile thì dùng ảnh desktop
            'mobile' => Banner::imageUrl($row['image_mobile'] ?: $row['image_desktop']),
        ], $rows);
    }

    /**
     * Danh mục cha đang bật + danh mục con + số sản phẩm đang bán (cha = của chính nó + của các con).
     *
     * @return list<array{name: string, url: string, count: int, tone: int, children: list<array{name: string, url: string, count: int}>}>
     */
    public static function categories(): array
    {
        $cache = config('home.cache.categories');
        $activeProducts = fn (Builder $query) => $query->active();

        $roots = Cache::remember($cache['key'], $cache['ttl'], fn () => Category::query()
            ->active()
            ->roots()
            ->orderBy('sort_order')
            ->withCount(['products' => $activeProducts])
            ->with(['children' => fn ($query) => $query->active()->withCount(['products' => $activeProducts])])
            ->get()
            ->map(fn (Category $root) => [
                'name' => $root->name,
                'slug' => $root->slug,
                'count' => $root->products_count + $root->children->sum('products_count'),
                'children' => $root->children
                    ->map(fn (Category $child) => ['name' => $child->name, 'slug' => $child->slug, 'count' => $child->products_count])
                    ->all(),
            ])
            ->all());

        $withUrl = fn (array $category) => [...$category, 'url' => url('/danh-muc/'.$category['slug'])];

        return array_map(fn (array $root, int $i) => [
            ...$withUrl($root),
            'tone' => $i % self::CATEGORY_TONES,
            'children' => array_map($withUrl, $root['children']),
        ], $roots, array_keys($roots));
    }

    /**
     * Sản phẩm nổi bật cho khối "Đĩa vuông": mỗi danh mục một sản phẩm để hàng tab (tên danh mục) không trùng nhau.
     * Lấy cả nhóm nổi bật (vài chục dòng) rồi lọc trong PHP — vẫn chỉ 1 truy vấn + eager load.
     *
     * @return Collection<int, Product>
     */
    private function featured(): Collection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with(['images', 'brand', 'category'])
            ->orderBy('id')
            ->get()
            ->unique('category_id')
            ->take(config('home.featured_limit'))
            ->values();
    }

    /** @return Collection<int, Product> */
    private function onSale(): Collection
    {
        return Product::query()
            ->active()
            ->onSale()
            ->with(['images', 'brand'])
            ->orderByDiscount()
            ->limit(config('home.on_sale_limit'))
            ->get();
    }

    /** Khách đã lưu loại da trong hồ sơ → mở thẳng danh sách lọc theo loại da đó; còn lại về /san-pham */
    private function skinTypeUrl(Request $request): string
    {
        $skinType = $request->user()?->skin_type;

        return $skinType ? url('/san-pham?'.http_build_query(['loai-da' => $skinType->value])) : url('/san-pham');
    }
}
