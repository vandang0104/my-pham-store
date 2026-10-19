<?php

namespace App\View\Composers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Cấp menu chính (mega menu + ngăn kéo điện thoại) cho header.
 * Dựng một lần thành mảng thuần rồi cache — mỗi request chỉ đọc cache, không truy vấn danh mục.
 */
class NavigationComposer
{
    public function compose(View $view): void
    {
        $view->with('navigation', self::menu());
    }

    /**
     * @return list<array{label: string, url: string|null, image: string|null, columns: list<array{title: string, url: string, links: list<array{label: string, url: string}>}>}>
     */
    public static function menu(): array
    {
        return Cache::remember(config('navigation.cache_key'), config('navigation.cache_ttl'), fn () => self::build());
    }

    public static function forget(): void
    {
        Cache::forget(config('navigation.cache_key'));
    }

    /**
     * 3 truy vấn cố định: danh mục cha (kèm con, nạp sẵn) và thương hiệu — không N+1.
     */
    private static function build(): array
    {
        $groups = config('navigation.groups');
        $slugs = collect($groups)->pluck('roots')->filter()->flatten()->all();

        $roots = Category::query()
            ->active()
            ->roots()
            ->whereIn('slug', $slugs)
            ->with(['children' => fn ($q) => $q->active()])
            ->get()
            ->keyBy('slug');

        $brands = collect($groups)->contains(fn ($g) => $g['brands'] ?? false)
            ? Brand::query()->active()->orderBy('name')->get(['name', 'slug'])
            : collect();

        return collect($groups)->map(function (array $group) use ($roots, $brands) {
            if ($group['brands'] ?? false) {
                return [
                    'label' => $group['label'],
                    'url' => null,
                    'image' => null,
                    'columns' => [[
                        'title' => $group['label'],
                        'url' => url('/san-pham'),
                        'links' => $brands->map(fn (Brand $b) => ['label' => $b->name, 'url' => url('/thuong-hieu/'.$b->slug)])->all(),
                    ]],
                ];
            }

            $columns = collect($group['roots'])
                ->map(fn (string $slug) => $roots->get($slug))
                ->filter()
                ->values();

            return [
                'label' => $group['label'],
                'url' => $columns->isNotEmpty() ? url('/danh-muc/'.$columns->first()->slug) : null,
                'image' => $columns->first()?->image ? Storage::disk('public')->url($columns->first()->image) : null,
                'columns' => $columns->map(fn (Category $root) => [
                    'title' => $root->name,
                    'url' => url('/danh-muc/'.$root->slug),
                    'links' => $root->children->map(fn (Category $c) => ['label' => $c->name, 'url' => url('/danh-muc/'.$c->slug)])->all(),
                ])->all(),
            ];
        })->filter(fn (array $group) => $group['columns'] !== [])->values()->all();
    }
}
