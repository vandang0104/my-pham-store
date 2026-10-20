<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Quản lý danh mục (cây 2 cấp). Lưu/xoá danh mục tự xoá cache menu (Category::booted). */
class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        return view('admin.categories.index', [
            'roots' => Category::query()->roots()->orderBy('sort_order')->orderBy('name')
                ->withCount('products')
                ->with(['children' => fn ($q) => $q->withCount('products')])
                ->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('admin.categories.form', ['category' => new Category(['is_active' => true]), 'parents' => $this->parents()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create($this->attributes($request, null));

        return redirect()->route('admin.categories.index')->with('status', "Đã thêm danh mục “{$category->name}”.");
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('admin.categories.form', ['category' => $category, 'parents' => $this->parents($category)]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->attributes($request, $category));

        return redirect()->route('admin.categories.index')->with('status', "Đã cập nhật danh mục “{$category->name}”.");
    }

    /** Chỉ admin; còn danh mục con hoặc sản phẩm (kể cả đã xoá mềm — FK restrict) thì báo lý do, không xoá */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $reason = match (true) {
            $category->children()->exists() => 'Danh mục còn danh mục con, hãy chuyển hoặc xoá danh mục con trước.',
            $category->products()->withTrashed()->exists() => 'Danh mục còn sản phẩm (kể cả sản phẩm đã xoá mềm) nên không xoá được. Có thể tắt danh mục thay vì xoá.',
            default => null,
        };
        if ($reason) {
            return back()->withErrors(['delete' => $reason]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Đã xoá danh mục “{$category->name}”.");
    }

    /** @return array<string, mixed> */
    private function attributes(CategoryRequest $request, ?Category $category): array
    {
        $data = $request->safe()->except(['image', 'slug']);
        $data['slug'] = Slug::unique(Category::class, $request->validated('slug') ?? $data['name'], $category?->id);
        $data['sort_order'] ??= 0;

        if ($request->file('image') instanceof UploadedFile) {
            $data['image'] = $request->file('image')->store('categories', 'public');
            if ($category?->image && ! str_starts_with($category->image, 'placeholders/')) {
                Storage::disk('public')->delete($category->image);
            }
        }

        return $data;
    }

    /** Danh mục gốc để chọn làm cha (trừ chính nó) */
    private function parents(?Category $except = null)
    {
        return Category::query()->roots()->when($except, fn ($q) => $q->whereKeyNot($except->id))->orderBy('sort_order')->get(['id', 'name']);
    }
}
