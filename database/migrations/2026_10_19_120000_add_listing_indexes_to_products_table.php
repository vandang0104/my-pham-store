<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index cho các cột lọc/sắp xếp của trang /san-pham, trang chủ và báo cáo.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Trang chủ: Product::featured()
            $table->index('is_featured');
            // Bộ lọc "loại da"
            $table->index('skin_type');
            // Sắp xếp "mới nhất" (mặc định của danh sách)
            $table->index('created_at');
            // Sắp xếp "bán chạy" và top sản phẩm trên dashboard
            $table->index('sold_count');
            // Sắp xếp "đánh giá cao"
            $table->index('rating_avg');
            // Lọc "còn hàng", báo cáo/lọc admin "sắp hết hàng" (stock <= ngưỡng)
            $table->index('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['skin_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['sold_count']);
            $table->dropIndex(['rating_avg']);
            $table->dropIndex(['stock']);
        });
    }
};
