<?php

use App\Support\SidebarConfigHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyColumns = SidebarConfigHelper::legacyColumnNames();
        $hasLegacy = collect($legacyColumns)->contains(fn (string $col) => Schema::hasColumn('tours', $col));

        if (! $hasLegacy) {
            return;
        }

        DB::table('tours')
            ->select(array_merge(['id', 'sidebar_config'], $legacyColumns))
            ->orderBy('id')
            ->chunkById(100, function ($tours) {
                foreach ($tours as $tour) {
                    $existing = $tour->sidebar_config;
                    $config = is_string($existing) ? json_decode($existing, true) : (array) ($existing ?? []);
                    if (! is_array($config)) {
                        $config = [];
                    }

                    $merged = SidebarConfigHelper::mergeLegacyRowIntoConfig($config, (array) $tour);

                    if ($merged !== $config) {
                        DB::table('tours')->where('id', $tour->id)->update([
                            'sidebar_config' => json_encode($merged),
                        ]);
                    }
                }
            });

        Schema::table('tours', function (Blueprint $table) use ($legacyColumns) {
            $drop = array_values(array_filter(
                $legacyColumns,
                fn (string $col) => Schema::hasColumn('tours', $col)
            ));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'sidebar_logo')) {
                $table->string('sidebar_logo')->nullable();
            }
            if (! Schema::hasColumn('tours', 'sidebar_footer_link')) {
                $table->text('sidebar_footer_link')->nullable();
            }
            if (! Schema::hasColumn('tours', 'sidebar_footer_text')) {
                $table->text('sidebar_footer_text')->nullable();
            }
            if (! Schema::hasColumn('tours', 'sidebar_footer_link_show')) {
                $table->boolean('sidebar_footer_link_show')->default(true);
            }
            if (! Schema::hasColumn('tours', 'sidebar_tag_text')) {
                $table->string('sidebar_tag_text')->nullable();
            }
            if (! Schema::hasColumn('tours', 'sidebar_tag_color')) {
                $table->string('sidebar_tag_color')->nullable();
            }
            if (! Schema::hasColumn('tours', 'sidebar_tag_bg_color')) {
                $table->string('sidebar_tag_bg_color')->nullable();
            }
        });
    }
};
