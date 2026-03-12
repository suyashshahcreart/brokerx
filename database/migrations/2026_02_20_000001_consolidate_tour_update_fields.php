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
            if (!Schema::hasColumn('tours', 'contact_google_location')) {
                $column = $table->string('contact_google_location')->nullable();
                if (Schema::hasColumn('tours', 'footer_brand_mobile')) {
                    $column->after('footer_brand_mobile');
                }
            }

            if (!Schema::hasColumn('tours', 'contact_website')) {
                $column = $table->string('contact_website')->nullable();
                if (Schema::hasColumn('tours', 'contact_google_location')) {
                    $column->after('contact_google_location');
                }
            }

            if (!Schema::hasColumn('tours', 'contact_email')) {
                $column = $table->string('contact_email')->nullable();
                if (Schema::hasColumn('tours', 'contact_website')) {
                    $column->after('contact_website');
                }
            }

            if (!Schema::hasColumn('tours', 'contact_phone_no')) {
                $column = $table->string('contact_phone_no')->nullable();
                if (Schema::hasColumn('tours', 'contact_email')) {
                    $column->after('contact_email');
                }
            }

            if (!Schema::hasColumn('tours', 'contact_whatsapp_no')) {
                $column = $table->string('contact_whatsapp_no')->nullable();
                if (Schema::hasColumn('tours', 'contact_phone_no')) {
                    $column->after('contact_phone_no');
                }
            }

            if (!Schema::hasColumn('tours', 'attachment_file')) {
                $column = $table->json('attachment_file')->nullable()->comment('Store attachment file details as JSON object');
                if (Schema::hasColumn('tours', 'contact_whatsapp_no')) {
                    $column->after('contact_whatsapp_no');
                } elseif (Schema::hasColumn('tours', 'contact_phone_no')) {
                    $column->after('contact_phone_no');
                }
            }

            if (!Schema::hasColumn('tours', 'enable_language')) {
                $column = $table->json('enable_language')->nullable();
                if (Schema::hasColumn('tours', 'contact_whatsapp_no')) {
                    $column->after('contact_whatsapp_no');
                }
            }

            if (!Schema::hasColumn('tours', 'default_language')) {
                $column = $table->string('default_language')->nullable();
                if (Schema::hasColumn('tours', 'enable_language')) {
                    $column->after('enable_language');
                }
            }

            if (!Schema::hasColumn('tours', 'overlay_bg_color')) {
                $column = $table->string('overlay_bg_color')->nullable();
                if (Schema::hasColumn('tours', 'default_language')) {
                    $column->after('default_language');
                }
            }

            if (!Schema::hasColumn('tours', 'loader_text')) {
                $column = $table->string('loader_text')->nullable();
                if (Schema::hasColumn('tours', 'overlay_bg_color')) {
                    $column->after('overlay_bg_color');
                }
            }

            if (!Schema::hasColumn('tours', 'loader_color')) {
                $column = $table->json('loader_color')->nullable();
                if (Schema::hasColumn('tours', 'loader_text')) {
                    $column->after('loader_text');
                }
            }

            if (!Schema::hasColumn('tours', 'spinner_color')) {
                $column = $table->json('spinner_color')->nullable();
                if (Schema::hasColumn('tours', 'loader_color')) {
                    $column->after('loader_color');
                }
            }

            if (!Schema::hasColumn('tours', 'sidebar_tag_text')) {
                $column = $table->string('sidebar_tag_text')->nullable()->comment('Text for the sidebar tag');
                if (Schema::hasColumn('tours', 'attachment_file')) {
                    $column->after('attachment_file');
                }
            }

            if (!Schema::hasColumn('tours', 'sidebar_tag_color')) {
                $column = $table->string('sidebar_tag_color')->nullable()->comment('Text color for the sidebar tag');
                if (Schema::hasColumn('tours', 'sidebar_tag_text')) {
                    $column->after('sidebar_tag_text');
                }
            }

            if (!Schema::hasColumn('tours', 'sidebar_tag_bg_color')) {
                $column = $table->string('sidebar_tag_bg_color')->nullable()->comment('Background color for the sidebar tag');
                if (Schema::hasColumn('tours', 'sidebar_tag_color')) {
                    $column->after('sidebar_tag_color');
                }
            }

            if (!Schema::hasColumn('tours', 'bottommark_property_name')) {
                $column = $table->json('bottommark_property_name')->nullable()->comment('Property name in multiple languages (en, gu, hi)');
                if (Schema::hasColumn('tours', 'sidebar_tag_bg_color')) {
                    $column->after('sidebar_tag_bg_color');
                } elseif (Schema::hasColumn('tours', 'attachment_file')) {
                    $column->after('attachment_file');
                }
            }

            if (!Schema::hasColumn('tours', 'bottommark_room_type')) {
                $column = $table->json('bottommark_room_type')->nullable()->comment('Room type in multiple languages (en, gu, hi)');
                if (Schema::hasColumn('tours', 'bottommark_property_name')) {
                    $column->after('bottommark_property_name');
                }
            }

            if (!Schema::hasColumn('tours', 'bottommark_dimensions')) {
                $column = $table->json('bottommark_dimensions')->nullable()->comment('Dimensions in multiple languages (en, gu, hi)');
                if (Schema::hasColumn('tours', 'bottommark_room_type')) {
                    $column->after('bottommark_room_type');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [];

        foreach ([
            'bottommark_dimensions',
            'bottommark_room_type',
            'bottommark_property_name',
            'sidebar_tag_bg_color',
            'sidebar_tag_color',
            'sidebar_tag_text',
            'spinner_color',
            'loader_color',
            'loader_text',
            'overlay_bg_color',
            'default_language',
            'enable_language',
            'attachment_file',
            'contact_whatsapp_no',
            'contact_phone_no',
            'contact_email',
            'contact_website',
            'contact_google_location',
        ] as $column) {
            if (Schema::hasColumn('tours', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('tours', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }
};
