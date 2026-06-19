<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', ['billing', 'shipping'])
                ->default('shipping');

            $table->string('name');
            $table->string('phone', 20);

            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();

            $table->string('city');
            $table->string('state');
            $table->string('country')->default('India');
            $table->string('pincode', 10);

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index('user_id');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};