<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'locale_config')) {
                $column = $table->json('locale_config')->nullable();
                if (Schema::hasColumn('tours', 'default_language')) {
                    $column->after('default_language');
                }
            }

            if (! Schema::hasColumn('tours', 'language_display')) {
                $column = $table->json('language_display')->nullable();
                if (Schema::hasColumn('tours', 'locale_config')) {
                    $column->after('locale_config');
                } elseif (Schema::hasColumn('tours', 'default_language')) {
                    $column->after('default_language');
                }
            }

            if (! Schema::hasColumn('tours', 'language_slot_order')) {
                $column = $table->json('language_slot_order')->nullable();
                if (Schema::hasColumn('tours', 'language_display')) {
                    $column->after('language_display');
                } elseif (Schema::hasColumn('tours', 'locale_config')) {
                    $column->after('locale_config');
                } elseif (Schema::hasColumn('tours', 'default_language')) {
                    $column->after('default_language');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $columns = ['language_slot_order', 'language_display', 'locale_config'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tours', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
