<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which side of a hero slide its text card sits on, so an admin can keep
     * the overlay off the photo's subject (some images have the product on
     * the left, some on the right). Additive; existing rows default to 'left'
     * — exactly how they render today.
     */
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->string('text_position', 10)->default('left')->after('button_text');
        });
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropColumn('text_position');
        });
    }
};
