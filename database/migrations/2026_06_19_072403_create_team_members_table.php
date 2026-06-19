<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('designation');

            $table->string('image')->nullable();

            $table->text('short_bio')->nullable();

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};