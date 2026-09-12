<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * status moves from a fixed DB enum to a plain string, same rationale as
     * payment_method's earlier enum->string migration — 'rejected' (manual
     * UPI verification) is a new allowed value, enforced at the application
     * layer (ManualUpiPaymentService) rather than another schema change.
     *
     * proof_path deliberately stores a path on the private 'local' disk, not
     * 'public' — payment proof screenshots are never served by a direct
     * public URL, only through an authorized controller route.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();

            $table->string('upi_reference', 100)->nullable()->after('transaction_id');
            $table->string('proof_path')->nullable()->after('upi_reference');
            $table->timestamp('submitted_at')->nullable()->after('paid_at');
            $table->timestamp('rejected_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['upi_reference', 'proof_path', 'submitted_at', 'rejected_at']);

            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->change();
        });
    }
};
