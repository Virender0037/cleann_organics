<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tax_rate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();

            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('brand')->nullable();

            // Product unit and variant-like details
            $table->enum('unit', ['kg', 'gram', 'litre', 'piece', 'pack'])->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->string('pack_quantity')->nullable();

            // Tiered pricing
            $table->boolean('enable_tiered_pricing')->default(false);

            $table->unsignedInteger('single_quantity')->nullable();
            $table->decimal('single_price', 10, 2)->nullable();

            $table->unsignedInteger('standard_quantity')->nullable();
            $table->decimal('standard_price', 10, 2)->nullable();

            $table->unsignedInteger('discount_quantity')->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();

            // Inventory
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_quantity')->default(5);
            $table->enum('stock_status', ['in_stock', 'out_of_stock'])->default('in_stock');

            // Return settings
            $table->boolean('is_returnable')->default(false);
            $table->unsignedTinyInteger('return_days')->default(7);

            // Product display flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->boolean('is_best_seller')->default(false);

            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->timestamps();

            $table->index('category_id');
            $table->index('tax_rate_id');
            $table->index('status');
            $table->index('stock_status');
            $table->index('is_featured');
            $table->index('is_latest');
            $table->index('is_best_seller');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};