<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin lọc đơn theo trạng thái + khoảng ngày, báo cáo doanh thu theo ngày đặt của đơn đã giao:
     * cả hai dạng WHERE status = ? AND created_at BETWEEN ? AND ? → index ghép (status, created_at).
     * Index đơn cột status thừa vì đã là tiền tố của index ghép.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->dropIndex(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
