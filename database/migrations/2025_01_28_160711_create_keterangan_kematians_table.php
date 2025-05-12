<?php

use App\Models\Surat;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keterangan_kematians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Langsung ke users
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete();
            $table->string('nomor_surat')->nullable();
            // Data yang bisa diambil dari relasi
            $table->string('nama_lengkap')->nullable(); // Bisa dari user->name
            $table->integer('usia')->nullable(); // Bisa dihitung dari user->tgl_lahir
            $table->string('tempat_baptis')->nullable(); // Bisa dari detail_users
            $table->string('no_baptis')->nullable(); // Bisa dari detail_users
            // Data spesifik kematian
            $table->string('nama_ortu')->nullable(); // Bisa dari keluarga
            $table->string('nama_pasangan')->nullable();
            $table->date('tgl_kematian')->nullable();
            $table->date('tgl_pemakaman')->nullable();
            $table->string('tempat_pemakaman')->nullable();
            $table->string('pelayanan_sakramen')->nullable();
            $table->string('sakramen')->nullable();
            // Data surat
            $table->date('tgl_surat')->nullable();
            $table->string('ttd_ketua')->nullable(); // Bisa dari ketua_lingkungan->user->tanda_tangan
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
