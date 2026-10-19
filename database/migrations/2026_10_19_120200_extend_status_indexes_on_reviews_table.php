<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trang chi tiết SP: đánh giá đã duyệt của 1 sản phẩm, mới nhất trước → (product_id, status, created_at).
     * Admin: đánh giá chờ duyệt, mới nhất trước → (status, created_at).
     * Thêm index mới trước rồi mới bỏ index cũ: FK product_id luôn còn index có product_id đứng đầu.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'status', 'created_at']);
            $table->dropIndex(['product_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->dropIndex(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'status']);
            $table->dropIndex(['product_id', 'status', 'created_at']);
            $table->index('status');
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
