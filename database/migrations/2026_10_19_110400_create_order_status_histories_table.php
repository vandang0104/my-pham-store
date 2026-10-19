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
        $statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];

        Schema::create('order_status_histories', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // null: trạng thái khởi tạo khi vừa đặt đơn
            $table->enum('from_status', $statuses)->nullable();
            $table->enum('to_status', $statuses);
            // Giữ nhật ký khi xoá người thao tác
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            // Nhật ký chỉ ghi thêm, không cập nhật
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
