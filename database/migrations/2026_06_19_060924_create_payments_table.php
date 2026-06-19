<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('transaction_id')
                ->nullable();

            $table->enum('payment_method', [
                'cod',
                'upi',
                'bank_transfer'
            ]);

            $table->decimal('amount', 12, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');

            $table->text('admin_note')
                ->nullable();

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamp('refunded_at')
                ->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};