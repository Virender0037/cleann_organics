<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * home_banners becomes the one generic homepage slider table:
     * `section` says which slider a row belongs to ('hero' slides, 'benefit'
     * trust-strip cards, and any future homepage slider), so no per-section
     * table is ever needed. Existing rows default to 'hero'.
     *
     * Benefit cards may be icon-only, so image is now nullable (the admin
     * request still requires it for hero slides).
     */
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->string('section', 30)->default('hero')->after('id');
            $table->string('icon', 40)->nullable()->after('mobile_image');
            $table->string('alt_text')->nullable()->after('icon');
            $table->boolean('opens_new_tab')->default(false)->after('link_url');
            $table->string('image')->nullable()->change();

            $table->index(['section', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropIndex(['section', 'status', 'sort_order']);
            $table->dropColumn(['section', 'icon', 'alt_text', 'opens_new_tab']);
        });
    }
};
