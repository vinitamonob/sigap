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
        Schema::create('keterangan_lains', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('nama_ketua');
            $table->string('ketua_lingkungan');
            $table->string('paroki');
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('jabatan_pekerjaan');
            $table->text('alamat');
            $table->string('telepon_rumah')->nullable();
            $table->string('telepon_kantor')->nullable();
            $table->enum('status_tinggal', ['Sendiri', 'Bersama Keluarga', 'Bersama Saudara', 'Kos/Kontrak']);
            $table->text('keperluan');
            $table->string('tanda_tangan_pastor')->nullable();
            $table->string('tanda_tangan_ketua')->nullable();
            $table->enum('status_ttd_pastor', ['Menunggu', 'Selesai'])->default('Menunggu');
            $table->date('tanggal_surat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keterangan_lains');
    }
};
