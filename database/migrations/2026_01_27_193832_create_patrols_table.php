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
        Schema::create('patrols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('area_name');
            $table->string('area_code');
            $table->enum('status', ['Aman', 'Tidak Aman'])->default('Aman');
            $table->text('note')->nullable();
            $table->string('photo');
            $table->date('patrol_date');
            $table->time('patrol_time');
            $table->timestamp('submitted_at');
            $table->timestamps();
            
            $table->index(['user_id', 'patrol_date']);
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrols');
    }
};
