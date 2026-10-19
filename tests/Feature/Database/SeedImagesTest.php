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
        ->and($paths->countBy()->all())->toBe(['seed/001.jpg' => 84, 'seed/002.png' => 84]);
});
