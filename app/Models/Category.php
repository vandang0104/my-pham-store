<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'name', 'slug', 'image', 'sort_order', 'is_active'])]
#[RouteKey('slug')]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function roots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Id của danh mục này và toàn bộ danh mục con cháu (không giới hạn số cấp).
     *
     * @return list<int>
     */
    public function selfAndDescendantIds(): array
    {
        // Bảng danh mục nhỏ: nạp cây 1 lần rồi duyệt trong PHP thay vì truy vấn đệ quy
        $childrenByParent = static::query()->get(['id', 'parent_id'])->groupBy('parent_id');

        $ids = [];
        $queue = [$this->getKey()];
        while ($queue !== []) {
            $id = array_shift($queue);
            $ids[] = $id;
            foreach ($childrenByParent->get($id, []) as $child) {
                $queue[] = $child->id;
            }
        }

        return $ids;
    }
}
