<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            // Bottommark multilingual fields - these will store JSON data
            $table->json('bottommark_property_name')->nullable()->after('sidebar_tag_bg_color')->comment('Property name in multiple languages (en, gu, hi)');
            $table->json('bottommark_room_type')->nullable()->after('bottommark_property_name')->comment('Room type in multiple languages (en, gu, hi)');
            $table->json('bottommark_dimensions')->nullable()->after('bottommark_room_type')->comment('Dimensions in multiple languages (en, gu, hi)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['bottommark_property_name', 'bottommark_room_type', 'bottommark_dimensions']);
        });
    }
};
