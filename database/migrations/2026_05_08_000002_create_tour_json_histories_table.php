<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_json_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->unsignedInteger('version');

            $table->json('virtual_tour_nodes_json')->nullable();
            $table->json('tour_data_json')->nullable();
            $table->longText('s3_config_js')->nullable();

            $table->json('virtual_tour_nodes_json_diff')->nullable();
            $table->json('tour_data_json_diff')->nullable();
            $table->json('s3_config_js_diff')->nullable();

            $table->string('type', 64)->default('zip_upload');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['tour_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_json_histories');
    }
};
