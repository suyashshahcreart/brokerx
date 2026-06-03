<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'sidebar_config')) {
                $column = $table->json('sidebar_config')->nullable();
                if (Schema::hasColumn('tours', 'sidebar_tag_bg_color')) {
                    $column->after('sidebar_tag_bg_color');
                } elseif (Schema::hasColumn('tours', 'sidebar_links')) {
                    $column->after('sidebar_links');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'sidebar_config')) {
                $table->dropColumn('sidebar_config');
            }
        });
    }
};
