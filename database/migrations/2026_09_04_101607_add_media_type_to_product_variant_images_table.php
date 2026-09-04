<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variant_images', function (Blueprint $table) {
            // Default 'image' classifies every existing row correctly with no backfill.
            $table->enum('media_type', ['image', 'video'])->default('image')->after('image');

            $table->index(['product_variant_id', 'media_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_images', function (Blueprint $table) {
            $table->dropIndex(['product_variant_id', 'media_type']);
            $table->dropColumn('media_type');
        });
    }
};
