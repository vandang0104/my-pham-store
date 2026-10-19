<?php

namespace Database\Seeders;

use App\Enums\SkinType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\Support\PlaceholderImage;
use Database\Seeders\Support\SeedImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Danh mục (cha + con), 10 thương hiệu hư cấu, 48 sản phẩm kèm 3–4 ảnh mỗi sản phẩm.
 */
class CatalogSeeder extends Seeder
{
    /** Tồn kho cố định cho vài sản phẩm để có dữ liệu "sắp hết" / "hết hàng" */
    private const STOCK_OVERRIDES = ['Mặt nạ đất sét Bí Đao' => 4, 'Tinh dầu dưỡng tóc Argan' => 3, 'Xịt thơm toàn thân Đào Hồng' => 0];

    public function run(): void
    {
        PlaceholderImage::reset();

        $categoryColors = $this->seedCategories();
        $brands = $this->seedBrands();
        $this->seedProducts($brands, $categoryColors);
    }

    /**
     * @return array<string, array{0: string, 1: string}> slug danh mục con → màu của danh mục cha
     */
    private function seedCategories(): array
    {
        $colors = [];
        foreach (require __DIR__.'/data/categories.php' as $i => $parentData) {
            $parent = Category::factory()->create([
                'name' => $parentData['ten'],
                'slug' => $parentData['slug'],
                'image' => PlaceholderImage::cover("categories/{$parentData['slug']}.svg", $parentData['ten'], $parentData['mau'], 600, 600),
                'sort_order' => $i + 1,
            ]);

            foreach ($parentData['con'] as $j => $childData) {
                Category::factory()->childOf($parent)->create([
                    'name' => $childData['ten'],
                    'slug' => $childData['slug'],
                    'image' => PlaceholderImage::cover("categories/{$childData['slug']}.svg", $childData['ten'], $parentData['mau'], 600, 600),
                    'sort_order' => $j + 1,
                ]);
                $colors[$childData['slug']] = $parentData['mau'];
            }
        }

        return $colors;
    }

    /**
     * @return array<string, array{model: Brand, sku: string}>
     */
    private function seedBrands(): array
    {
        $brands = [];
        foreach (require __DIR__.'/data/brands.php' as $data) {
            $brands[$data['slug']] = [
                'model' => Brand::factory()->create([
                    'name' => $data['ten'],
                    'slug' => $data['slug'],
                    'logo' => PlaceholderImage::logo("brands/{$data['slug']}.svg", $data['ten']),
                    'description' => $data['mo_ta'],
                ]),
                'sku' => $data['sku'],
            ];
        }

        return $brands;
    }

    /**
     * @param  array<string, array{model: Brand, sku: string}>  $brands
     * @param  array<string, array{0: string, 1: string}>  $categoryColors
     */
    private function seedProducts(array $brands, array $categoryColors): void
    {
        $types = require __DIR__.'/data/product_types.php';
        $categoryIds = Category::query()->pluck('id', 'slug');
        $seedImages = new SeedImages;
        $skuCounters = [];

        foreach (require __DIR__.'/data/products.php' as $i => $data) {
            $brand = $brands[$data['th']];
            $type = $types[$data['loai']];
            $skuCounters[$data['th']] = ($skuCounters[$data['th']] ?? 0) + 1;
            $fill = fn (string $text) => strtr($text, ['{ten}' => $data['ten'], '{hl}' => $data['hl']]);

            $product = Product::factory()->create([
                'category_id' => $categoryIds[$data['dm']],
                'brand_id' => $brand['model']->id,
                'name' => $data['ten'],
                'slug' => Str::slug($data['ten']),
                'sku' => sprintf('%s-%03d', $brand['sku'], $skuCounters[$data['th']]),
                'short_description' => Str::before($fill($type['mo_ta']), '. ').'.',
                'description' => $this->description($fill, $type, $data['da']),
                'ingredients' => Str::ucfirst($fill($type['thanh_phan'])),
                'usage_guide' => collect($type['huong_dan'])->map(fn (string $step, int $k) => ($k + 1).'. '.$step)->implode("\n"),
                'price' => $data['gia'],
                'sale_price' => $data['km'],
                'stock' => self::STOCK_OVERRIDES[$data['ten']] ?? 20 + ($i * 37) % 130,
                'volume' => $data['dt'],
                'skin_type' => $data['da'],
                'is_featured' => $data['nb'],
            ]);

            // 3–4 ảnh/sản phẩm, ảnh đầu là ảnh chính
            $imageCount = $i % 2 === 0 ? 4 : 3;
            for ($k = 0; $k < $imageCount; $k++) {
                $product->images()->create([
                    'path' => $seedImages->available()
                        ? $seedImages->next()
                        : PlaceholderImage::product("products/{$product->slug}-".($k + 1).'.svg', $product->name, $brand['model']->name, $categoryColors[$data['dm']], $k),
                    'is_primary' => $k === 0,
                    'sort_order' => $k,
                ]);
            }
        }
    }

    /**
     * Mô tả Markdown: giới thiệu, công dụng, loại da phù hợp, lưu ý.
     *
     * @param  array<string, mixed>  $type
     */
    private function description(callable $fill, array $type, ?string $skinType): string
    {
        $skin = $skinType === null ? 'Mọi đối tượng.' : SkinType::from($skinType)->label().'.';

        return $fill($type['mo_ta'])
            ."\n\n## Công dụng\n\n".collect($type['cong_dung'])->map(fn (string $line) => '- '.$fill($line))->implode("\n")
            ."\n\n## Phù hợp với\n\n".$skin
            ."\n\n> ".$type['luu_y'];
    }
}
