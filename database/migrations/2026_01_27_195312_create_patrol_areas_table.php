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
        Schema::create('patrol_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('kode_area')->unique();
            $table->string('nama_area');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'non-aktif'])->default('aktif');
            $table->integer('urutan')->default(0);
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrol_areas');
    }
};
