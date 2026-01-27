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
        // Main submission per area
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_room_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('submitted_at');
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Unique constraint: one submission per employee per room per day
            $table->unique(['user_id', 'project_room_id', 'tanggal'], 'unique_daily_submission');
        });

        // Individual task items within a submission
        Schema::create('task_submission_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_list_id')->constrained()->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['task_submission_id', 'task_list_id'], 'unique_submission_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submission_items');
        Schema::dropIfExists('task_submissions');
    }
};
