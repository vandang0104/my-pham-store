<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SkinType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Quản lý sản phẩm: staff thêm/sửa; chỉ admin xoá mềm / khôi phục (ProductPolicy). 1 ảnh chính/SP
 * (upload thay ảnh chính; các ảnh phụ từ seed giữ nguyên).
 */
class ProductController extends Controller
{
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);
        $status = $request->query('trang-thai');
        $category = Category::query()->find($request->integer('danh-muc') ?: null);

        $products = Product::query()
            ->with(['category', 'brand', 'images'])
            ->when($status === 'da-xoa', fn ($q) => $q->onlyTrashed())
            ->when($status === 'dang-ban', fn ($q) => $q->where('is_active', true))
            ->when($status === 'an', fn ($q) => $q->where('is_active', false))
            ->when(trim((string) $request->query('q')) !== '', function ($q) use ($request) {
                $term = '%'.trim((string) $request->query('q')).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($category, fn ($q) => $q->inCategory($category))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $this->categoryOptions(),
            'filters' => ['q' => $request->query('q'), 'danh-muc' => $category?->id, 'trang-thai' => $status],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('admin.products.form', $this->formData(new Product(['is_active' => true, 'stock' => 0])));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::query()->create($this->attributes($request, null));
            $this->saveImage($request, $product);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('status', "Đã thêm sản phẩm “{$product->name}”.");
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);
        $product->load('images');

        return view('admin.products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->attributes($request, $product));
            $this->saveImage($request, $product);
        });

        return redirect()->route('admin.products.edit', $product)->with('status', "Đã cập nhật sản phẩm “{$product->name}”.");
    }

    /** Xoá mềm (quy tắc 10: không xoá cứng — đơn cũ vẫn tham chiếu được) */
    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', "Đã xoá (mềm) sản phẩm “{$product->name}”. Có thể khôi phục ở bộ lọc “Đã xoá”.");
    }

    public function restore(Product $product): RedirectResponse
    {
        Gate::authorize('restore', $product);
        $product->restore();

        return redirect()->route('admin.products.edit', $product)->with('status', "Đã khôi phục sản phẩm “{$product->name}”.");
    }

    /** @return array<string, mixed> */
    private function attributes(ProductRequest $request, ?Product $product): array
    {
        $data = $request->safe()->except(['image', 'slug']);
        $data['slug'] = Slug::unique(Product::class, $request->validated('slug') ?? $data['name'], $product?->id);

        return $data;
    }

    /** Ảnh mới thay ảnh chính: cập nhật đường dẫn ảnh chính (xoá file cũ nếu không phải ảnh mẫu), chưa có thì tạo */
    private function saveImage(ProductRequest $request, Product $product): void
    {
        if (! $request->file('image') instanceof UploadedFile) {
            return;
        }

        $path = $request->file('image')->store('products', 'public');
        $primary = $product->images()->where('is_primary', true)->first();

        if ($primary === null) {
            $product->images()->create(['path' => $path, 'is_primary' => true, 'sort_order' => 0]);

            return;
        }

        if (! $primary->is_placeholder) {
            Storage::disk('public')->delete($primary->path);
        }
        $primary->update(['path' => $path]);
    }

    /** @return array<string, mixed> */
    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => $this->categoryOptions(),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'skinTypes' => SkinType::cases(),
        ];
    }

    private function categoryOptions()
    {
        return Category::query()->roots()->orderBy('sort_order')->with('children')->get();
    }
}
