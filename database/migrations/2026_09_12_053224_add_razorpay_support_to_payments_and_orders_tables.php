<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payment_method moves from a fixed DB enum to a plain string on both
     * tables so a new gateway (razorpay) never needs another schema
     * migration just to add an allowed value — the accepted set is enforced
     * once, at the application layer (PlaceOrderRequest + CheckoutService),
     * matching how every other "which option is this" field in this app
     * (order_status, coupon type, etc.) is validated.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();

            $table->string('gateway_order_id')->nullable()->after('transaction_id');
            $table->string('gateway_payment_id')->nullable()->unique()->after('gateway_order_id');
            $table->string('gateway_signature')->nullable()->after('gateway_payment_id');
            $table->text('failure_reason')->nullable()->after('admin_note');
            $table->json('meta')->nullable()->after('failure_reason');

            $table->index('gateway_order_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_order_id']);
            $table->dropColumn(['gateway_order_id', 'gateway_payment_id', 'gateway_signature', 'failure_reason', 'meta']);

            $table->enum('payment_method', ['cod', 'upi', 'bank_transfer'])->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cod', 'upi', 'bank_transfer'])->change();
        });
    }
};
