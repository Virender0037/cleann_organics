<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Earned-voucher support (₹999+ order offer): a coupon may be restricted
     * to one customer (user_id) and remembers the delivered order that
     * earned it (source_order_id, UNIQUE — this is what makes issuing a
     * voucher idempotent at the database level, so a repeated "delivered"
     * event can never mint a second one). Both nullable: every existing
     * admin-created coupon is unaffected.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->foreignId('source_order_id')->nullable()->unique()->after('user_id')->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_order_id');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
