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
        Schema::create('keterangan_kematians', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('nama_lingkungan')->nullable();
            $table->string('paroki')->nullable();
            $table->string('nama_lengkap');
            $table->integer('usia');
            $table->string('nama_orang_tua');
            $table->string('nama_pasangan'); // Nama Suami/Isteri
            $table->date('tanggal_kematian');
            $table->date('tanggal_pemakaman');
            $table->string('tempat_pemakaman');
            $table->string('pelayan_sakramen')->nullable();
            $table->string('sakramen_yang_diberikan')->nullable();
            $table->string('tempat_no_buku_baptis');
            $table->string('tanda_tangan_ketua')->nullable();
            $table->date('tanggal_surat');
            $table->enum('status', ['Menunggu', 'Selesai'])->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keterangan_kematians');
    }
};
