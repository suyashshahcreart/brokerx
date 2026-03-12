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
        Schema::table('customers', function (Blueprint $table) {
            // General meta / SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('meta_robots')->nullable();

            // Open Graph
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->nullable();
            $table->string('og_url')->nullable();

            // Twitter Card
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_card')->nullable();

            // Scripts / tracking
            $table->text('header_code')->nullable();
            $table->text('footer_code')->nullable();
            $table->string('gtm_tag')->nullable();

            $table->string('slug')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title',
                'meta_description',
                'meta_keywords',
                'meta_image',
                'canonical_url',
                'meta_robots',
                'og_title',
                'og_description',
                'og_image',
                'og_type',
                'og_url',
                'twitter_title',
                'twitter_description',
                'twitter_image',
                'twitter_card',
                'header_code',
                'footer_code',
                'gtm_tag',
            ]);
            // dropping unique index for slug
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};