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
            $table->renameColumn('custom_logo_sidebar', 'sidebar_logo');
            $table->renameColumn('custom_logo_footer', 'footer_logo');
            $table->renameColumn('custom_name', 'footer_name');
            $table->renameColumn('custom_email', 'footer_email');
            $table->renameColumn('custom_mobile', 'footer_mobile');
            $table->renameColumn('custom_description', 'footer_decription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->renameColumn('sidebar_logo', 'custom_logo_sidebar');
            $table->renameColumn('footer_logo', 'custom_logo_footer');
            $table->renameColumn('footer_name', 'custom_name');
            $table->renameColumn('footer_email', 'custom_email');
            $table->renameColumn('footer_mobile', 'custom_mobile');
            $table->renameColumn('footer_decription', 'custom_description');
        });
    }
};
