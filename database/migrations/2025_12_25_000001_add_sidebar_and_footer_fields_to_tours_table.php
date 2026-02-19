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
            $table->text('company_address')->nullable();
            // Sidebar section
            $table->text('sidebar_footer_link')->nullable();
            $table->text('sidebar_footer_text')->nullable();
            $table->boolean('sidebar_footer_link_show')->default(true);
            // Footer section
            $table->text('footer_info_type')->nullable();
            $table->text('footer_brand_logo')->nullable();
            $table->text('footer_brand_text')->nullable();
            $table->text('footer_brand_mobile')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'company_address',
                'sidebar_footer_link',
                'sidebar_footer_text',
                'sidebar_footer_link_show',
                'footer_info_type',
                'footer_brand_logo',
                'footer_brand_text',
                'footer_brand_mobile',
            ]);
        });
    }
};
