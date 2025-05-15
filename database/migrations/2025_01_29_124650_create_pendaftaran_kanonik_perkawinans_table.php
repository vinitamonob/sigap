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
        Schema::create('pendaftaran_kanonik_perkawinans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calon_suami_id')->nullable()->constrained('calon_pasangans')->nullOnDelete();
            $table->foreignId('calon_istri_id')->nullable()->constrained('calon_pasangans')->nullOnDelete();
            $table->foreignId('lingkungan_suami_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('lingkungan_istri_id')->nullable()->constrained('lingkungans')->nullOnDelete();    
            $table->string('nomor_surat')->nullable();
            $table->date('tgl_surat')->nullable();    
            $table->string('lokasi_gereja')->nullable();
            $table->date('tgl_pernikahan')->nullable();
            $table->time('waktu_pernikahan')->nullable();  
            $table->string('nama_pastor')->nullable();
            $table->string('ttd_pastor')->nullable();
            $table->string('ttd_ketua_suami')->nullable();
            $table->string('ttd_ketua_istri')->nullable();           
            $table->string('ttd_calon_suami')->nullable();
            $table->string('ttd_calon_istri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_kanonik_perkawinans');
    }
};
