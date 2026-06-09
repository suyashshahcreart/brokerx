<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'gtm_tag_2')) {
                $column = $table->string('gtm_tag_2')->nullable();
                if (Schema::hasColumn('tours', 'gtm_tag')) {
                    $column->after('gtm_tag');
                }
            }

            if (! Schema::hasColumn('tours', 'gtm_tag_3')) {
                $column = $table->string('gtm_tag_3')->nullable();
                if (Schema::hasColumn('tours', 'gtm_tag_2')) {
                    $column->after('gtm_tag_2');
                } elseif (Schema::hasColumn('tours', 'gtm_tag')) {
                    $column->after('gtm_tag');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'gtm_tag_3')) {
                $table->dropColumn('gtm_tag_3');
            }
            if (Schema::hasColumn('tours', 'gtm_tag_2')) {
                $table->dropColumn('gtm_tag_2');
            }
        });
    }
};
