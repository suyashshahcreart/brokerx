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
            // Sidebar tag fields
            $table->string('sidebar_tag_text')->nullable()->after('attachment_file')->comment('Text for the sidebar tag');
            $table->string('sidebar_tag_color')->nullable()->after('sidebar_tag_text')->comment('Text color for the sidebar tag');
            $table->string('sidebar_tag_bg_color')->nullable()->after('sidebar_tag_color')->comment('Background color for the sidebar tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['sidebar_tag_text', 'sidebar_tag_color', 'sidebar_tag_bg_color']);
        });
    }
};
