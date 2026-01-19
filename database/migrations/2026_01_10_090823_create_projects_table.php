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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nama_project');
            $table->enum('jenis_project', ['cleaning_services', 'security_services']);
            $table->text('alamat_lengkap');
            $table->decimal('nilai_kontrak', 15, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
