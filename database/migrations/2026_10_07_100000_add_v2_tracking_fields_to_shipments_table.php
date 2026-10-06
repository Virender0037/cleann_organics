<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only. NimbusPost Partner API v2 returns, on booking, an official customer tracking page (`tracking_url`),
     * an estimated delivery date (`edd`) and a pickup id — none of which v1 provided.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->text('tracking_url')->nullable()->after('manifest_url');
            $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_url');
            $table->string('pickup_id', 50)->nullable()->after('pickup_requested');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['tracking_url', 'estimated_delivery_at', 'pickup_id']);
        });
    }
};
