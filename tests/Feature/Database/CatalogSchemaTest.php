<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// Dùng query builder vì Model sẽ tạo ở bước sau
function taoSanPham(array $attrs = []): int
{
    $categoryId = DB::table('categories')->insertGetId(['name' => 'Chăm sóc da', 'slug' => 'cham-soc-da-'.uniqid()]);
    $brandId = DB::table('brands')->insertGetId(['name' => 'La Roche-Posay', 'slug' => 'la-roche-posay-'.uniqid()]);

    return DB::table('products')->insertGetId($attrs + [
        'category_id' => $categoryId,
        'brand_id' => $brandId,
        'name' => 'Sữa rửa mặt',
        'slug' => 'sua-rua-mat-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'price' => 350000,
    ]);
}

it('tạo đủ các bảng và cột của catalog, tài khoản', function () {
    expect(Schema::hasColumns('users', ['phone', 'role', 'avatar', 'birthday', 'skin_type', 'is_active']))->toBeTrue()
        ->and(Schema::hasColumns('addresses', ['user_id', 'receiver_name', 'phone', 'province', 'district', 'ward', 'detail', 'is_default']))->toBeTrue()
        ->and(Schema::hasColumns('categories', ['parent_id', 'name', 'slug', 'image', 'sort_order', 'is_active']))->toBeTrue()
        ->and(Schema::hasColumns('brands', ['name', 'slug', 'logo', 'description', 'is_active']))->toBeTrue()
        ->and(Schema::hasColumns('products', ['category_id', 'brand_id', 'sku', 'price', 'sale_price', 'stock', 'rating_avg', 'sold_count', 'deleted_at']))->toBeTrue()
        ->and(Schema::hasColumns('product_images', ['product_id', 'path', 'is_primary', 'sort_order']))->toBeTrue();
});

it('gán giá trị mặc định cho user mới', function () {
    $id = DB::table('users')->insertGetId(['name' => 'Lan', 'email' => 'lan@example.com', 'password' => 'x']);
    $user = DB::table('users')->find($id);

    expect($user->role)->toBe('customer')
        ->and((bool) $user->is_active)->toBeTrue();
});

it('không cho xoá danh mục hoặc thương hiệu còn sản phẩm', function (string $bang, string $cot) {
    $productId = taoSanPham();
    $id = DB::table('products')->where('id', $productId)->value($cot);

    expect(fn () => DB::table($bang)->delete($id))->toThrow(QueryException::class);
})->with([
    'danh mục' => ['categories', 'category_id'],
    'thương hiệu' => ['brands', 'brand_id'],
]);

it('không cho xoá danh mục cha còn danh mục con', function () {
    $parentId = DB::table('categories')->insertGetId(['name' => 'Trang điểm', 'slug' => 'trang-diem']);
    DB::table('categories')->insert(['parent_id' => $parentId, 'name' => 'Son môi', 'slug' => 'son-moi']);

    expect(fn () => DB::table('categories')->delete($parentId))->toThrow(QueryException::class);
});

it('xoá cứng sản phẩm thì xoá ảnh của sản phẩm', function () {
    $productId = taoSanPham();
    DB::table('product_images')->insert(['product_id' => $productId, 'path' => 'products/a.jpg', 'is_primary' => true]);

    DB::table('products')->delete($productId);

    expect(DB::table('product_images')->where('product_id', $productId)->exists())->toBeFalse();
});

it('xoá user thì xoá sổ địa chỉ của user', function () {
    $userId = DB::table('users')->insertGetId(['name' => 'Lan', 'email' => 'lan@example.com', 'password' => 'x']);
    DB::table('addresses')->insert([
        'user_id' => $userId, 'receiver_name' => 'Nguyễn Thị Lan', 'phone' => '0901234567',
        'province' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Nghé', 'detail' => '12 Lê Lợi',
    ]);

    DB::table('users')->delete($userId);

    expect(DB::table('addresses')->count())->toBe(0);
});
