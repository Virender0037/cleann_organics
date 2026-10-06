<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only — no existing column or row is changed.
     *
     * 1. Package dimensions on variants (centimetres), next to the existing `weight` (kilograms, untouched). Left NULL
     *    for every existing variant: dimensions are never guessed; the admin fills them in.
     * 2. `shipments`: one row per courier booking (NimbusPost). Payment, order and shipment status stay separate —
     *    nothing courier-related is written onto `orders`.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('length_cm', 8, 2)->nullable()->after('weight');
            $table->decimal('width_cm', 8, 2)->nullable()->after('length_cm');
            $table->decimal('height_cm', 8, 2)->nullable()->after('width_cm');
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('provider', 30)->default('nimbuspost');

            // Duplicate guard: holds the order id while this shipment is live (creating/booked/in transit…) and is
            // cleared when it fails or is cancelled. UNIQUE over a nullable column → at most one live shipment per
            // order on both MariaDB and SQLite, while failed/cancelled attempts stay as history.
            $table->unsignedBigInteger('active_order_id')->nullable()->unique();

            $table->string('status', 30);                       // internal, see App\Models\Shipment::STATUSES
            $table->string('provider_status', 40)->nullable();  // raw provider value, for support/debugging
            $table->string('payment_type', 10);                 // as sent to the courier: cod | prepaid
            $table->decimal('cod_amount', 12, 2)->default(0);   // collectable on delivery (0 for prepaid)

            $table->string('provider_order_id', 50)->nullable();
            $table->string('provider_shipment_id', 50)->nullable();
            $table->string('awb_number', 50)->nullable()->index();
            $table->string('courier_id', 20)->nullable();
            $table->string('courier_name', 100)->nullable();
            $table->text('label_url')->nullable();
            $table->text('manifest_url')->nullable();
            $table->boolean('pickup_requested')->default(false);

            $table->unsignedInteger('package_weight_grams');
            $table->decimal('package_length_cm', 8, 2)->nullable();
            $table->decimal('package_width_cm', 8, 2)->nullable();
            $table->decimal('package_height_cm', 8, 2)->nullable();

            $table->json('tracking_history')->nullable();
            $table->string('rto_awb', 50)->nullable();
            $table->string('ndr_reason', 500)->nullable();
            $table->string('failure_reason', 500)->nullable();

            $table->timestamp('booked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['length_cm', 'width_cm', 'height_cm']);
        });
    }
};
