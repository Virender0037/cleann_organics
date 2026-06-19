<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipping_zone_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('min_weight', 10, 2)->default(0);
            $table->decimal('max_weight', 10, 2)->nullable();

            $table->decimal('shipping_charge', 10, 2)->default(0);

            $table->decimal('free_shipping_above', 10, 2)->nullable();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            $table->index('shipping_zone_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};