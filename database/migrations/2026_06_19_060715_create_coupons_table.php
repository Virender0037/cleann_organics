<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->enum('type', ['fixed', 'percentage']);

            $table->decimal('value', 10, 2);

            $table->decimal('minimum_order_amount', 10, 2)
                ->default(0);

            $table->decimal('maximum_discount_amount', 10, 2)
                ->nullable();

            $table->unsignedInteger('usage_limit')
                ->nullable();

            $table->unsignedInteger('used_count')
                ->default(0);

            $table->dateTime('start_date');
            $table->dateTime('end_date');

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            $table->index('code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};