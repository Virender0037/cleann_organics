<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable delivery snapshot on the order itself (DB Gap Planning).
 *
 * orders.address_id stays (nullOnDelete) purely as an admin-convenience
 * back-reference to the live row; it is NOT the source of truth for
 * historical delivery information. Everything a past order needs to render
 * its own delivery details is frozen into these columns at placement time,
 * so editing or deleting the address later cannot change what the order
 * shows.
 *
 * Billing is captured with a same_as_shipping flag plus its own snapshot
 * columns so a later phase can collect a distinct billing address by
 * setting the flag false and filling billing_* — no schema redesign needed.
 *
 * shipping_zone_name freezes which ShippingZone the rate was resolved
 * against; the rate's actual charge is already frozen in
 * orders.shipping_amount, and no ShippingMethod is selected at checkout
 * (that admin module isn't wired to orders), so nothing else about
 * shipping needs snapshotting.
 *
 * All columns are nullable — safe for any pre-existing rows (there are none
 * yet) and for the free-shipping / no-zone-configured case.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('address_id');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->string('shipping_address_line_1')->nullable()->after('shipping_phone');
            $table->string('shipping_address_line_2')->nullable()->after('shipping_address_line_1');
            $table->string('shipping_city')->nullable()->after('shipping_address_line_2');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_country')->nullable()->after('shipping_state');
            $table->string('shipping_pincode')->nullable()->after('shipping_country');

            $table->boolean('billing_same_as_shipping')->default(true)->after('shipping_pincode');
            $table->string('billing_name')->nullable()->after('billing_same_as_shipping');
            $table->string('billing_phone')->nullable()->after('billing_name');
            $table->string('billing_address_line_1')->nullable()->after('billing_phone');
            $table->string('billing_address_line_2')->nullable()->after('billing_address_line_1');
            $table->string('billing_city')->nullable()->after('billing_address_line_2');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_country')->nullable()->after('billing_state');
            $table->string('billing_pincode')->nullable()->after('billing_country');

            $table->string('shipping_zone_name')->nullable()->after('billing_pincode');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_address_line_1',
                'shipping_address_line_2',
                'shipping_city',
                'shipping_state',
                'shipping_country',
                'shipping_pincode',
                'billing_same_as_shipping',
                'billing_name',
                'billing_phone',
                'billing_address_line_1',
                'billing_address_line_2',
                'billing_city',
                'billing_state',
                'billing_country',
                'billing_pincode',
                'shipping_zone_name',
            ]);
        });
    }
};
