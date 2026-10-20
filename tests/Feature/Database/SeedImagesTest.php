<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('gán luân phiên ảnh thật khi có file trong thư mục seed', function () {
    Storage::fake('public');
    Storage::disk('public')->put('seed/001.jpg', 'a');
    Storage::disk('public')->put('seed/002.png', 'b');
    Storage::disk('public')->put('seed/ghi-chu.txt', 'bỏ qua');

    $this->seed();

    $paths = Product::query()->with('images')->get()->flatMap->images->pluck('path');
    expect($paths->unique()->sort()->values()->all())->toBe(['seed/001.jpg', 'seed/002.png'])
        // 21 sản phẩm: 11 SP × 4 ảnh + 10 SP × 3 ảnh = 74 ảnh, chia đều cho 2 file
        ->and($paths->countBy()->all())->toBe(['seed/001.jpg' => 37, 'seed/002.png' => 37]);
});
