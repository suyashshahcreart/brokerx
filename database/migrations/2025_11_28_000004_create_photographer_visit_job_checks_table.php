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
        if (Schema::hasTable('photographer_visit_job_checks')) {
            return;
        }

        Schema::create('photographer_visit_job_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_visit_job_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->string('photo')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('location_timestamp')->nullable();
            $table->decimal('location_accuracy', 8, 2)->nullable();
            $table->string('location_source', 50)->nullable();
            $table->integer('photos_taken')->nullable();
            $table->text('work_summary')->nullable();
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['photographer_visit_job_id', 'type']);
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_visit_job_checks');
    }
};
