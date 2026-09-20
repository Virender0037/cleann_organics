<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('button_text', 60)->nullable();
            $table->string('image');
            $table->string('mobile_image')->nullable();
            // Destination: product / category / tag resolve by id (so a slug
            // rename never breaks a banner); url is a free-form path/URL.
            $table->string('link_type', 20)->default('url');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained()->nullOnDelete();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_banners');
    }
};
