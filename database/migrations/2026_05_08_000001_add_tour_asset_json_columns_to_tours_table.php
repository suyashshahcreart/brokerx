<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->json('virtual_tour_nodes_json')->nullable()->after('final_json');
            $table->json('tour_data_json')->nullable()->after('virtual_tour_nodes_json');
            $table->longText('tour_data_js')->nullable()->after('tour_data_json');

        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['virtual_tour_nodes_json', 'tour_data_json', 'tour_data_js']);
        });
    }
};
