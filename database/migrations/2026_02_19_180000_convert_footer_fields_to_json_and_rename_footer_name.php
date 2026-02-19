<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tours')) {
            return;
        }

        if (Schema::hasColumn('tours', 'footer_name') && !Schema::hasColumn('tours', 'footer_title')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_name` = JSON_QUOTE(`footer_name`)
                WHERE `footer_name` IS NOT NULL
                  AND JSON_VALID(`footer_name`) = 0
            ");

            DB::statement("ALTER TABLE `tours` CHANGE `footer_name` `footer_title` JSON NULL");
        } elseif (Schema::hasColumn('tours', 'footer_title')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_title` = JSON_QUOTE(`footer_title`)
                WHERE `footer_title` IS NOT NULL
                  AND JSON_VALID(`footer_title`) = 0
            ");

            DB::statement("ALTER TABLE `tours` MODIFY `footer_title` JSON NULL");
        }

        if (Schema::hasColumn('tours', 'footer_subtitle')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_subtitle` = JSON_QUOTE(`footer_subtitle`)
                WHERE `footer_subtitle` IS NOT NULL
                  AND JSON_VALID(`footer_subtitle`) = 0
            ");

            DB::statement("ALTER TABLE `tours` MODIFY `footer_subtitle` JSON NULL");
        }

        if (Schema::hasColumn('tours', 'footer_decription')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_decription` = JSON_QUOTE(`footer_decription`)
                WHERE `footer_decription` IS NOT NULL
                  AND JSON_VALID(`footer_decription`) = 0
            ");

            DB::statement("ALTER TABLE `tours` MODIFY `footer_decription` JSON NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tours')) {
            return;
        }

        if (Schema::hasColumn('tours', 'footer_title') && !Schema::hasColumn('tours', 'footer_name')) {
            DB::statement("ALTER TABLE `tours` CHANGE `footer_title` `footer_name` LONGTEXT NULL");

                        DB::statement("\n                UPDATE `tours`
                SET `footer_name` = JSON_UNQUOTE(`footer_name`)
                WHERE `footer_name` IS NOT NULL
                  AND JSON_VALID(`footer_name`) = 1
                  AND JSON_TYPE(`footer_name`) = 'STRING'
            ");
        }

        if (Schema::hasColumn('tours', 'footer_subtitle')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_subtitle` = JSON_UNQUOTE(`footer_subtitle`)
                WHERE `footer_subtitle` IS NOT NULL
                  AND JSON_VALID(`footer_subtitle`) = 1
                  AND JSON_TYPE(`footer_subtitle`) = 'STRING'
            ");

            DB::statement("ALTER TABLE `tours` MODIFY `footer_subtitle` LONGTEXT NULL");
        }

        if (Schema::hasColumn('tours', 'footer_decription')) {
                        DB::statement("\n                UPDATE `tours`
                SET `footer_decription` = JSON_UNQUOTE(`footer_decription`)
                WHERE `footer_decription` IS NOT NULL
                  AND JSON_VALID(`footer_decription`) = 1
                  AND JSON_TYPE(`footer_decription`) = 'STRING'
            ");

            DB::statement("ALTER TABLE `tours` MODIFY `footer_decription` LONGTEXT NULL");
        }
    }
};
