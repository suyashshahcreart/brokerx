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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'base_mobile')) {
                $table->string('base_mobile', 20)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->char('country_code', 2)->nullable()->after('base_mobile');
            }
            if (!Schema::hasColumn('users', 'dial_code')) {
                $table->char('dial_code', 6)->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('country_code')
                    ->constrained('countries')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_id')) {
                $table->dropConstrainedForeignId('country_id');
            }
            if (Schema::hasColumn('users', 'country_code')) {
                $table->dropColumn('country_code');
            }
            if (Schema::hasColumn('users', 'dial_code')) {
                $table->dropColumn('dial_code');
            }
            if (Schema::hasColumn('users', 'base_mobile')) {
                $table->dropColumn('base_mobile');
            }
        });
    }
};
