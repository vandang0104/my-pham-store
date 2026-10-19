<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/** Danh sách cột của các index (không tính khoá chính) */
function indexColumns(string $table): array
{
    return collect(Schema::getIndexes($table))
        ->reject(fn (array $index) => $index['primary'])
        ->map(fn (array $index) => implode(',', $index['columns']))
        ->values()
        ->all();
}

it('có index cho các cột lọc/sắp xếp sản phẩm', function () {
    expect(indexColumns('products'))->toContain('is_featured', 'skin_type', 'created_at', 'sold_count', 'rating_avg', 'stock', 'price', 'is_active');
});

it('dùng index ghép trạng thái + ngày cho đơn hàng và bỏ index đơn cột thừa', function () {
    expect(indexColumns('orders'))->toContain('status,created_at', 'created_at', 'payment_status')
        ->not->toContain('status');
});

it('dùng index ghép cho danh sách đánh giá theo sản phẩm và theo trạng thái', function () {
    expect(indexColumns('reviews'))->toContain('product_id,status,created_at', 'status,created_at', 'user_id,product_id')
        ->not->toContain('product_id,status', 'status');
});

it('giỏ hàng chỉ dành cho user đăng nhập', function () {
    expect(Schema::hasColumn('carts', 'session_id'))->toBeFalse()
        ->and(fn () => DB::table('carts')->insert(['user_id' => null]))->toThrow(QueryException::class);
});
