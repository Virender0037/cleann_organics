<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reels', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('thumbnail')->nullable();
            // Either an uploaded file on the public disk or an external
            // direct-video URL; the Instagram URL is only ever a link out.
            $table->string('video_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reels');
    }
};
