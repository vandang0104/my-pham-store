<?php

namespace App\Models;

use App\Enums\SkinType;
use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description',
    'ingredients', 'usage_guide', 'price', 'sale_price', 'stock', 'volume', 'skin_type',
    'is_featured', 'is_active',
])]
#[RouteKey('slug')]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /** Biểu thức SQL giá hiện hành, khớp accessor current_price (quy tắc 1) */
    private const CURRENT_PRICE_SQL = 'CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END';

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'skin_type' => SkinType::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'rating_avg' => 'float',
            'rating_count' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ---- Scopes ----

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    #[Scope]
    protected function onSale(Builder $query): void
    {
        $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
    }

    /** Sắp theo phần trăm giảm giảm dần (cùng công thức với discount_percent), hoà thì sản phẩm mới trước */
    #[Scope]
    protected function orderByDiscount(Builder $query): void
    {
        // * 1.0: SQLite (test) chia số nguyên ra 0; MySQL vốn trả số thập phân
        $query->orderByRaw('(price - COALESCE(sale_price, price)) * 1.0 / price DESC')->orderByDesc('id');
    }

    /** Lọc theo giá hiện hành (đã tính giá khuyến mãi); bỏ trống đầu nào thì không giới hạn đầu đó */
    #[Scope]
    protected function priceBetween(Builder $query, ?int $min = null, ?int $max = null): void
    {
        if ($min !== null) {
            $query->whereRaw(self::CURRENT_PRICE_SQL.' >= ?', [$min]);
        }

        if ($max !== null) {
            $query->whereRaw(self::CURRENT_PRICE_SQL.' <= ?', [$max]);
        }
    }

    /** Tìm theo tên, SKU, mô tả và tên thương hiệu */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
            return;
        }

        $like = "%{$term}%";
        $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('short_description', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', $like));
        });
    }

    /**
     * Áp bộ lọc của trang danh sách (ProductFilterRequest::filters()): từ khoá, danh mục (gồm con),
     * thương hiệu (nhiều slug), khoảng giá hiện hành, đang giảm giá. Slug không tồn tại → không có kết quả.
     *
     * @param  array{q?: ?string, category?: Category|string|null, brands?: list<string>, price_from?: ?int, price_to?: ?int, on_sale?: bool}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query->search($filters['q'] ?? null)
            ->priceBetween($filters['price_from'] ?? null, $filters['price_to'] ?? null);

        $category = $filters['category'] ?? null;
        if (is_string($category)) {
            $category = Category::query()->active()->where('slug', $category)->first();
            if ($category === null) {
                $query->whereRaw('1 = 0');
            }
        }
        if ($category instanceof Category) {
            $query->inCategory($category);
        }

        if (($filters['brands'] ?? []) !== []) {
            $query->whereHas('brand', fn (Builder $b) => $b->active()->whereIn('slug', $filters['brands']));
        }

        if ($filters['on_sale'] ?? false) {
            $query->onSale();
        }
    }

    /** Sản phẩm thuộc danh mục, gồm cả mọi danh mục con cháu */
    #[Scope]
    protected function inCategory(Builder $query, Category|int $category): void
    {
        $category = $category instanceof Category ? $category : Category::query()->findOrFail($category);

        $query->whereIn('category_id', $category->selfAndDescendantIds());
    }

    // ---- Accessors ----

    /** Giá hiện hành: sale_price nếu có và nhỏ hơn price, ngược lại price (quy tắc 1) */
    protected function currentPrice(): Attribute
    {
        return Attribute::get(fn (): int => $this->is_on_sale ? $this->sale_price : $this->price);
    }

    protected function isOnSale(): Attribute
    {
        return Attribute::get(fn (): bool => $this->sale_price !== null && $this->sale_price < $this->price);
    }

    /** Phần trăm giảm, làm tròn; 0 nếu không giảm giá */
    protected function discountPercent(): Attribute
    {
        return Attribute::get(fn (): int => $this->is_on_sale && $this->price > 0
            ? (int) round(($this->price - $this->sale_price) * 100 / $this->price)
            : 0);
    }

    /** Ảnh đánh dấu chính, nếu không có thì ảnh đầu tiên */
    protected function primaryImage(): Attribute
    {
        return Attribute::get(fn (): ?ProductImage => $this->images->firstWhere('is_primary', true) ?? $this->images->first());
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => Money::format($this->current_price));
    }

    protected function formattedOriginalPrice(): Attribute
    {
        return Attribute::get(fn (): string => Money::format($this->price));
    }
}
