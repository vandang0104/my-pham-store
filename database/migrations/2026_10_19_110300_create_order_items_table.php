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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // restrict: sản phẩm đã có trong đơn không được xoá cứng (quy tắc 10)
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();
            // SNAPSHOT tại thời điểm đặt: không đọc lại từ products
            $table->string('product_name', 200);
            $table->string('sku', 50);
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
