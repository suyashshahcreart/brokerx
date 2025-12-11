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
        Schema::create('photographer_visit_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->unsignedBigInteger('tour_id')->nullable();
            $table->foreignId('photographer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('job_code')->unique();
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('instructions')->nullable();
            $table->text('special_requirements')->nullable();
            $table->integer('estimated_duration')->nullable()->comment('in minutes');
            $table->json('metadata')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');

            // Indexes
            $table->index('booking_id');
            $table->index('tour_id');
            $table->index('photographer_id');
            $table->index('status');
            $table->index('priority');
            $table->index('scheduled_date');
            $table->index('job_code');
        });

        // Update photographer_visits table to link to job
        Schema::table('photographer_visits', function (Blueprint $table) {
            $table->foreignId('job_id')->nullable()->after('id')->constrained('photographer_visit_jobs')->onDelete('cascade');
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographer_visits', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->dropIndex(['job_id']);
            $table->dropColumn('job_id');
        });

        Schema::dropIfExists('photographer_visit_jobs');
    }
};
