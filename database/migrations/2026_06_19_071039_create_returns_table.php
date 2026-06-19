<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('return_number')->unique();

            $table->text('reason');

            $table->enum('status', [
                'requested',
                'approved',
                'rejected',
                'picked_up',
                'received',
                'refunded'
            ])->default('requested');

            $table->decimal('refund_amount', 12, 2)
                ->default(0);

            $table->text('admin_note')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('refunded_at')
                ->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};