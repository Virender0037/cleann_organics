<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('address_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('order_number')->unique();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->enum('payment_method', [
                'cod',
                'upi',
                'bank_transfer'
            ]);

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');

            $table->enum('order_status', [
                'pending',
                'confirmed',
                'packed',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->text('cancellation_reason')->nullable();

            $table->text('notes')->nullable();

            $table->string('invoice_number')
                ->nullable()
                ->unique();

            $table->timestamp('invoice_date')
                ->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('order_number');
            $table->index('payment_status');
            $table->index('order_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};