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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', ['admin', 'staff', 'customer'])->default('customer')->index()->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->date('birthday')->nullable()->after('avatar');
            // Giá trị loại da chốt bằng Enum ở tầng Model
            $table->string('skin_type', 30)->nullable()->after('birthday');
            $table->boolean('is_active')->default(true)->after('skin_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['phone', 'role', 'avatar', 'birthday', 'skin_type', 'is_active']);
        });
    }
};
