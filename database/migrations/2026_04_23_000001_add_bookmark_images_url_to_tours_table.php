<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tours', 'bookmark_images_url')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->json('bookmark_images_url')->nullable()->after('bookmark_image_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tours', 'bookmark_images_url')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropColumn('bookmark_images_url');
            });
        }
    }
};
