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
        Schema::create('ftp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->unique()->comment('e.g., tour, industry, htl');
            $table->string('display_name')->comment('Display name for the category');
            $table->string('main_url')->comment('Main URL like tour.proppik.in');
            $table->string('driver')->default('ftp')->comment('ftp or sftp');
            $table->string('host');
            $table->string('username');
            $table->string('password');
            $table->integer('port')->default(21);
            $table->string('root')->default('/')->comment('Root directory path');
            $table->boolean('passive')->default(true);
            $table->boolean('ssl')->default(false);
            $table->integer('timeout')->default(30);
            $table->string('remote_path_pattern')->nullable()->comment('Pattern like {customer_id}/{slug}/index.php or qr/{slug}/index.php');
            $table->string('url_pattern')->nullable()->comment('URL pattern like https://{main_url}/{remote_path}');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('category_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ftp_configurations');
    }
};
