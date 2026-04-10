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
            $table->string('bookmark_title')->nullable();
            $table->string('bookmark_ribbon_background_color')->nullable();
            $table->string('bookmark_ribbon_text_color')->nullable();
            $table->boolean('bookmark_show_on_tour_load')->default(false);
            $table->integer('bookmark_show_on_tour_load_delay_ms')->default(0);
            $table->string('bookmark_action')->nullable();
            $table->json('bookmark_modal_title')->nullable();
            $table->json('bookmark_modal_description')->nullable();
            $table->json('bookmark_info_modal_footer_button_title')->nullable();
            $table->string('bookmark_info_modal_footer_button_link')->nullable();
            $table->json('bookmark_info_modal_footer_text')->nullable();
            $table->string('bookmark_open_link_url')->nullable();
            $table->string('bookmark_document_url')->nullable();
            $table->string('bookmark_video_url')->nullable();
            $table->string('bookmark_image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'bookmark_title',
                'bookmark_ribbon_background_color',
                'bookmark_ribbon_text_color',
                'bookmark_show_on_tour_load',
                'bookmark_show_on_tour_load_delay_ms',
                'bookmark_action',
                'bookmark_modal_title',
                'bookmark_modal_description',
                'bookmark_info_modal_footer_button_title',
                'bookmark_info_modal_footer_button_link',
                'bookmark_info_modal_footer_text',
            ]);
        });
    }
};
