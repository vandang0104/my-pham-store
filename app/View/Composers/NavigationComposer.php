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
 * Cache chỉ giữ dữ liệu thô (tên, đường dẫn tương đối); URL tuyệt đối dựng theo request lúc render
 * để không dính host của request đầu tiên (localhost / tên miền tunnel).
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
        $groups = Cache::remember(config('navigation.cache_key'), config('navigation.cache_ttl'), fn () => self::build());

        return array_map(fn (array $group) => [
            'label' => $group['label'],
            'url' => $group['path'] !== null ? url($group['path']) : null,
            'image' => $group['image'] !== null ? Storage::disk('public')->url($group['image']) : null,
            'columns' => array_map(fn (array $column) => [
                'title' => $column['title'],
                'url' => url($column['path']),
                'links' => array_map(fn (array $link) => ['label' => $link['label'], 'url' => url($link['path'])], $column['links']),
            ], $group['columns']),
        ], $groups);
    }

    public static function forget(): void
    {
        Cache::forget(config('navigation.cache_key'));
    }

    /**
     * 3 truy vấn cố định: danh mục cha (kèm con, nạp sẵn) và thương hiệu — không N+1.
     * Trả mảng thô để cache: 'path' là đường dẫn tương đối, 'image' là đường dẫn trên disk public.
     *
     * @return list<array{label: string, path: string|null, image: string|null, columns: list<array{title: string, path: string, links: list<array{label: string, path: string}>}>}>
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
                    'path' => null,
                    'image' => null,
                    'columns' => [[
                        'title' => $group['label'],
                        'path' => '/san-pham',
                        'links' => $brands->map(fn (Brand $b) => ['label' => $b->name, 'path' => '/thuong-hieu/'.$b->slug])->all(),
                    ]],
                ];
            }

            $columns = collect($group['roots'])
                ->map(fn (string $slug) => $roots->get($slug))
                ->filter()
                ->values();

            return [
                'label' => $group['label'],
                'path' => $columns->isNotEmpty() ? '/danh-muc/'.$columns->first()->slug : null,
                'image' => $columns->first()?->image ?: null,
                'columns' => $columns->map(fn (Category $root) => [
                    'title' => $root->name,
                    'path' => '/danh-muc/'.$root->slug,
                    'links' => $root->children->map(fn (Category $c) => ['label' => $c->name, 'path' => '/danh-muc/'.$c->slug])->all(),
                ])->all(),
            ];
        })->filter(fn (array $group) => $group['columns'] !== [])->values()->all();
    }
}
