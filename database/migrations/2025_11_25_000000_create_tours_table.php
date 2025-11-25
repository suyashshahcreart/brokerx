<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_participants')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // SEO Meta Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable(); // store image path or URL
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('canonical_url')->nullable(); // canonical link tag
            $table->string('meta_robots')->nullable(); // e.g. 'index, follow'
            $table->string('twitter_title')->nullable(); // Twitter card title
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable(); // Twitter image path/URL
            $table->string('structured_data_type')->nullable(); // e.g. 'Article', 'Place'
            $table->json('structured_data')->nullable(); // optional JSON-LD data
            $table->longText('header_code')->nullable(); // custom HTML or script before </head>
            $table->longText('footer_code')->nullable(); // custom HTML or script before </body>

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
