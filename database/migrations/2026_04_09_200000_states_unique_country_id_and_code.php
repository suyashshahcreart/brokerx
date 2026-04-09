<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace global unique on states.code with unique (country_id, code) so each country can use standard abbreviations (e.g. US "AR" vs India "AR").
     */
    public function up(): void
    {
        $indiaId = DB::table('countries')->where('name', 'India')->value('id');
        if ($indiaId) {
            DB::table('states')->whereNull('country_id')->update(['country_id' => $indiaId]);
        }

        try {
            Schema::table('states', function (Blueprint $table) {
                $table->dropUnique(['code']);
            });
        } catch (\Throwable) {
            // Already removed (e.g. after 2026_04_08_184532_make_states_code_string_and_not_unique).
        }

        $usId = DB::table('countries')->where('name', 'United States')->value('id');
        if ($usId) {
            $rows = DB::table('states')
                ->where('country_id', $usId)
                ->where('code', 'like', 'US-%')
                ->get(['id', 'code']);

            foreach ($rows as $row) {
                $plain = substr($row->code, 3);
                if ($plain !== '') {
                    DB::table('states')->where('id', $row->id)->update(['code' => $plain]);
                }
            }
        }

        Schema::table('states', function (Blueprint $table) {
            $table->unique(['country_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @note Rollback fails if the same code is used in more than one country (expected after up()).
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique(['country_id', 'code']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
