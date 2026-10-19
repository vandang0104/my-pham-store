<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Test trong thư mục Feature dùng TestCase của Laravel (có app, HTTP, DB).
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Tạo dữ liệu bằng query builder cho test schema (chưa có Model/Factory).
|
*/

function taoUser(array $attrs = []): int
{
    return DB::table('users')->insertGetId($attrs + [
        'name' => 'Nguyễn Thị Lan',
        'email' => Str::random(10).'@example.com',
        'password' => 'x',
    ]);
}

function taoSanPham(array $attrs = []): int
{
    $categoryId = DB::table('categories')->insertGetId(['name' => 'Chăm sóc da', 'slug' => 'cham-soc-da-'.Str::random(8)]);
    $brandId = DB::table('brands')->insertGetId(['name' => 'La Roche-Posay', 'slug' => 'la-roche-posay-'.Str::random(8)]);

    return DB::table('products')->insertGetId($attrs + [
        'category_id' => $categoryId,
        'brand_id' => $brandId,
        'name' => 'Sữa rửa mặt',
        'slug' => 'sua-rua-mat-'.Str::random(8),
        'sku' => 'SKU-'.Str::random(8),
        'price' => 350000,
    ]);
}

function taoDonHang(array $attrs = []): int
{
    return DB::table('orders')->insertGetId($attrs + [
        'code' => 'DH'.Str::upper(Str::random(8)),
        'receiver_name' => 'Nguyễn Thị Lan',
        'phone' => '0901234567',
        'province' => 'TP. Hồ Chí Minh',
        'ward' => 'Phường Bến Thành',
        'detail' => '12 Lê Lợi',
        'subtotal' => 700000,
        'total' => 700000,
    ]);
}
