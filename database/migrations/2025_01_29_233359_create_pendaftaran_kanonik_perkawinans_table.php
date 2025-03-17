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
            $table->string('nama_ketua_istri');
            $table->string('nama_lingkungan_istri');
            $table->string('wilayah_istri');
            $table->string('paroki_istri');
            $table->string('nama_istri');
            $table->string('tempat_lahir_istri');
            $table->date('tanggal_lahir_istri');
            $table->text('alamat_sekarang_istri');
            $table->text('alamat_setelah_menikah_istri');
            $table->string('telepon_istri');
            $table->string('pekerjaan_istri');
            $table->string('pendidikan_terakhir_istri');
            $table->string('agama_istri');
            $table->string('tempat_baptis_istri')->nullable();
            $table->date('tanggal_baptis_istri')->nullable();
            $table->string('nama_ayah_istri');
            $table->string('agama_ayah_istri');
            $table->string('pekerjaan_ayah_istri');
            $table->text('alamat_ayah_istri');
            $table->string('nama_ibu_istri');
            $table->string('agama_ibu_istri');
            $table->string('pekerjaan_ibu_istri');
            $table->text('alamat_ibu_istri');
            $table->string('nama_ketua_suami');
            $table->string('nama_lingkungan_suami');
            $table->string('wilayah_suami');
            $table->string('paroki_suami');
            $table->string('nama_suami');
            $table->string('tempat_lahir_suami');
            $table->date('tanggal_lahir_suami');
            $table->text('alamat_sekarang_suami');
            $table->text('alamat_setelah_menikah_suami');
            $table->string('telepon_suami');
            $table->string('pekerjaan_suami');
            $table->string('pendidikan_terakhir_suami');
            $table->string('agama_suami');
            $table->string('tempat_baptis_suami')->nullable();
            $table->date('tanggal_baptis_suami')->nullable();
            $table->string('nama_ayah_suami');
            $table->string('agama_ayah_suami');
            $table->string('pekerjaan_ayah_suami');
            $table->text('alamat_ayah_suami');
            $table->string('nama_ibu_suami');
            $table->string('agama_ibu_suami');
            $table->string('pekerjaan_ibu_suami');
            $table->text('alamat_ibu_suami');
            $table->string('lokasi_gereja');
            $table->date('tanggal_pernikahan');
            $table->time('waktu_pernikahan');
            $table->string('tanda_tangan_calon_istri')->nullable();
            $table->string('tanda_tangan_calon_suami')->nullable();
            $table->string('tanda_tangan_ketua_istri')->nullable();
            $table->string('tanda_tangan_ketua_suami')->nullable();
            $table->string('tanda_tangan_pastor')->nullable();
            $table->enum('status_ttd_pastor', ['Menunggu', 'Selesai'])->default('Menunggu');
            $table->date('tanggal_daftar');
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
