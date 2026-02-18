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
            $table->json('enable_language')->nullable()->after('contact_whatsapp_no');
            $table->string('default_language')->nullable()->after('enable_language');
            $table->string('overlay_bg_color')->nullable()->after('default_language');
            $table->string('loader_text')->nullable()->after('overlay_bg_color');
            $table->json('loader_color')->nullable()->after('loader_text');
            $table->json('spinner_color')->nullable()->after('loader_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'enable_language',
                'default_language',
                'overlay_bg_color',
                'loader_text',
                'loader_color',
                'spinner_color',
            ]);
        });
    }
};
