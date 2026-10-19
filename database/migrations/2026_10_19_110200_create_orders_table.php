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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            // Đơn phải còn khi xoá user: user_id về null (thông tin giao hàng đã lưu trong đơn)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Coupon đã dùng thì không cho xoá (cần để hoàn lượt khi huỷ đơn) — tắt bằng is_active
            $table->foreignId('coupon_id')->nullable()->constrained()->restrictOnDelete();

            // Địa chỉ giao hàng: cùng tên cột với bảng addresses
            $table->string('receiver_name', 100);
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('province', 100);
            $table->string('district', 100)->nullable();
            $table->string('ward', 100);
            $table->string('detail');

            $table->enum('payment_method', ['cod', 'bank_transfer', 'vnpay'])->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->index();
            $table->enum('status', ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'])->default('pending')->index();

            // Tiền VND
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('shipping_fee')->default(0);
            $table->unsignedInteger('total');

            $table->text('note')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
