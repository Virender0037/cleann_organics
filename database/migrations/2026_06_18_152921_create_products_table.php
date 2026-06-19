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
                ->restrictOnDelete();

            $table->foreignId('tax_rate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('brand')->nullable();

            $table->boolean('is_returnable')->default(false);
            $table->unsignedTinyInteger('return_days')->default(7);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);

            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('tax_rate_id');
            $table->index('status');
            $table->index('is_featured');
            $table->index('is_latest');
            $table->index('is_best_seller');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};