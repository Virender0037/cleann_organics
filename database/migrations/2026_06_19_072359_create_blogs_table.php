<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('blog_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('featured_image')->nullable();

            $table->text('short_description')->nullable();

            $table->longText('content');

            $table->unsignedInteger('view_count')->default(0);

            $table->boolean('is_featured')->default(false);

            $table->enum('status', [
                'draft',
                'published',
                'archived'
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('blog_category_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};