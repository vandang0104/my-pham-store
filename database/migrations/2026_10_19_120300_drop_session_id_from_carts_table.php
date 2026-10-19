<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Theo kiến trúc, giỏ khách vãng lai lưu trong session; bảng carts chỉ dành cho user đăng nhập.
     * Bỏ cột session_id (không dùng) và bắt buộc user_id.
     */
    public function up(): void
    {
        // Giỏ không gắn user không còn hợp lệ trong mô hình mới
        DB::table('carts')->whereNull('user_id')->delete();

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropColumn('session_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('session_id')->nullable()->index()->after('user_id');
        });
    }
};
