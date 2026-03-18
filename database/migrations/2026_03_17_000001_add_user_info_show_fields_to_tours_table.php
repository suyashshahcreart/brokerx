
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (!Schema::hasColumn('tours', 'contact_user_name')) {
                $table->string('contact_user_name')->nullable()->after('contact_whatsapp_no');
            }
            if (!Schema::hasColumn('tours', 'show_contact_user_name')) {
                $table->boolean('show_contact_user_name')->default(true)->after('contact_user_name');
            }
            if (!Schema::hasColumn('tours', 'show_contact_google_location')) {
                $table->boolean('show_contact_google_location')->default(true)->after('show_contact_user_name');
            }
            if (!Schema::hasColumn('tours', 'show_contact_email')) {
                $table->boolean('show_contact_email')->default(true)->after('show_contact_google_location');
            }
            if (!Schema::hasColumn('tours', 'show_contact_website')) {
                $table->boolean('show_contact_website')->default(false)->after('show_contact_email');
            }
            if (!Schema::hasColumn('tours', 'show_contact_phone_no')) {
                $table->boolean('show_contact_phone_no')->default(true)->after('show_contact_website');
            }
            if (!Schema::hasColumn('tours', 'show_contact_whatsapp_no')) {
                $table->boolean('show_contact_whatsapp_no')->default(true)->after('show_contact_phone_no');
            }
            
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $columns = [
                'contact_user_name',
                'show_contact_user_name',
                'show_contact_google_location',
                'show_contact_email',
                'show_contact_website',
                'show_contact_phone_no',
                'show_contact_whatsapp_no',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tours', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
