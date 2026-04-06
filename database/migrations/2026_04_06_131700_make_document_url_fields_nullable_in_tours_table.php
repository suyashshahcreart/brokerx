<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'document_auth_required')) {
                $table->boolean('document_auth_required')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_document_url')) {
                $table->boolean('show_document_url')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_document_url2')) {
                $table->boolean('show_document_url2')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'document_auth_required')) {
                $table->boolean('document_auth_required')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_document_url')) {
                $table->boolean('show_document_url')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_document_url2')) {
                $table->boolean('show_document_url2')->nullable(false)->default(true)->change();
            }
        });
    }
};
