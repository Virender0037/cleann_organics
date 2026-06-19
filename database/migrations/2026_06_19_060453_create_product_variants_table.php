<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('variant_name')->nullable();

            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();

            $table->enum('unit', [
                'kg',
                'gram',
                'litre',
                'piece',
                'pack'
            ])->nullable();

            $table->string('size')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->string('pack_quantity')->nullable();

            $table->boolean('enable_tiered_pricing')->default(false);

            $table->unsignedInteger('single_quantity')->nullable();
            $table->decimal('single_price', 10, 2)->nullable();

            $table->unsignedInteger('standard_quantity')->nullable();
            $table->decimal('standard_price', 10, 2)->nullable();

            $table->unsignedInteger('discount_quantity')->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_quantity')->default(5);

            $table->enum('stock_status', [
                'in_stock',
                'out_of_stock'
            ])->default('in_stock');

            $table->boolean('is_default')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('sku');
            $table->index('stock_status');
            $table->index('status');
            $table->index('is_default');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};