<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('designation')->nullable();

            $table->string('company')->nullable();

            $table->string('image')->nullable();

            $table->unsignedTinyInteger('rating')
                ->default(5);

            $table->text('message');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_featured')
                ->default(false);

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};