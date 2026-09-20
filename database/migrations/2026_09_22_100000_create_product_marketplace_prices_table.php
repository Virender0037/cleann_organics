<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only. Manually maintained marketplace prices shown beside our own price, plus a tiny
     * click log for the outbound redirect. The marketplace is a key from config/marketplaces.php, so
     * adding Blinkit/Zepto/etc. never needs a schema change.
     */
    public function up(): void
    {
        Schema::create('product_marketplace_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // The variant this listing corresponds to (same pack/size). NULL = product-level fallback.
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            // NOT NULL mirror of product_variant_id (0 = product-level). A unique index over a nullable column
            // permits duplicates on both MariaDB and SQLite, so uniqueness is enforced on this column instead.
            $table->unsignedBigInteger('variant_scope')->default(0);
            $table->string('marketplace', 50);
            $table->string('marketplace_product_name')->nullable();
            $table->decimal('mrp', 12, 2)->nullable();
            // Nullable so an inactive draft can be saved; validation requires it while the listing is active.
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->text('product_url')->nullable();
            $table->text('affiliate_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variant_scope', 'marketplace'], 'pmp_product_scope_marketplace_unique');
            $table->index('marketplace');
            $table->index(['product_id', 'is_active', 'sort_order'], 'pmp_product_active_sort_index');
        });

        Schema::create('marketplace_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_marketplace_price_id')->nullable()->constrained('product_marketplace_prices')->nullOnDelete();
            $table->string('marketplace', 50);
            $table->string('source', 30);
            // Deliberately no IP address, user agent or user id: only what is needed to count clicks.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_marketplace_price_id', 'created_at'], 'mc_price_created_index');
            $table->index(['product_id', 'marketplace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_clicks');
        Schema::dropIfExists('product_marketplace_prices');
    }
};
