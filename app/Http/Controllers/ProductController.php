<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Trang khách: danh sách sản phẩm (tìm + lọc), theo danh mục, theo thương hiệu, chi tiết.
 * Ba trang danh sách dùng chung view pages.products.index.
 */
class ProductController extends Controller
{
    public const PER_PAGE = 12;

    public const RELATED_LIMIT = 4;

    public function index(ProductFilterRequest $request): View
    {
        return $this->listing($request->filters(), [
            'title' => $request->filters()['q'] ? 'Kết quả tìm kiếm' : 'Tất cả sản phẩm',
            'breadcrumb' => [['label' => 'Sản phẩm']],
        ]);
    }

    public function category(ProductFilterRequest $request, Category $category): View
    {
        abort_unless($category->is_active, 404);
        $category->load(['parent', 'children' => fn ($q) => $q->active()]);

        return $this->listing(['category' => $category] + $request->filters(), [
            'title' => $category->name,
            'breadcrumb' => [
                ...($category->parent ? [['label' => $category->parent->name, 'url' => route('categories.show', $category->parent)]] : []),
                ['label' => $category->name],
            ],
            'category' => $category,
        ]);
    }

    public function brand(ProductFilterRequest $request, Brand $brand): View
    {
        abort_unless($brand->is_active, 404);

        return $this->listing(['brands' => [$brand->slug]] + $request->filters(), [
            'title' => $brand->name,
            'breadcrumb' => [['label' => 'Thương hiệu'], ['label' => $brand->name]],
            'brand' => $brand,
        ]);
    }

    public function show(Product $product): View
    {
        // Xoá mềm đã bị loại khi bind route; sản phẩm đang ẩn cũng trả 404
        abort_unless($product->is_active, 404);
        $product->load(['images', 'brand', 'category.parent']);
        $category = $product->category;

        return view('pages.products.show', [
            'product' => $product,
            'breadcrumb' => [
                ...($category->parent ? [['label' => $category->parent->name, 'url' => route('categories.show', $category->parent)]] : []),
                ['label' => $category->name, 'url' => route('categories.show', $category)],
                ['label' => $product->name],
            ],
            'description' => self::markdown($product->description),
            'usageGuide' => self::markdown($product->usage_guide),
            'related' => Product::query()
                ->active()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->with(['images', 'brand'])
                ->latest('id')
                ->limit(self::RELATED_LIMIT)
                ->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $page  title, breadcrumb, category?, brand?
     */
    private function listing(array $filters, array $page): View
    {
        $products = Product::query()
            ->active()
            ->filter($filters)
            ->with(['images', 'brand'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.products.index', $page + [
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => $this->categoryOptions(),
            'brandOptions' => Brand::query()->active()->orderBy('name')->get(['id', 'name', 'slug']),
            'category' => null,
            'brand' => null,
        ]);
    }

    /**
     * Danh mục cha kèm con cho ô chọn danh mục của bộ lọc.
     *
     * @return Collection<int, Category>
     */
    private function categoryOptions(): Collection
    {
        return Category::query()
            ->active()
            ->roots()
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->active()])
            ->get(['id', 'name', 'slug']);
    }

    /** Markdown an toàn XSS: bỏ HTML thô, chặn link javascript: (architecture.md quyết định 3) */
    private static function markdown(?string $text): ?string
    {
        return $text ? Str::markdown($text, ['html_input' => 'strip', 'allow_unsafe_links' => false]) : null;
    }
}
