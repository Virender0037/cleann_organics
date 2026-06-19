<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            $table->index('state');
            $table->index('city');
            $table->index('pincode');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};