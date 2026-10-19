<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // restrict: không xoá được danh mục/thương hiệu khi còn sản phẩm (kể cả sản phẩm đã xoá mềm)
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('sku', 50)->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->text('usage_guide')->nullable();
            // Tiền VND: số nguyên không dấu, không phần thập phân
            $table->unsignedInteger('price')->index();
            $table->unsignedInteger('sale_price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('volume', 50)->nullable();
            $table->string('skin_type', 30)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true)->index();
            // Phi chuẩn hoá từ reviews để lọc/sắp xếp nhanh
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
