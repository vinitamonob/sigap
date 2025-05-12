<?php

use App\Models\User;
use App\Models\Surat;
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
        Schema::create('keterangan_lains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Langsung ke users
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete();           
            // Data surat
            $table->string('nomor_surat')->nullable();
            $table->string('nama_pastor')->nullable();
            $table->string('ttd_pastor')->nullable();            
            // Data yang bisa diambil dari relasi tapi mungkin perlu override
            $table->string('pekerjaan')->nullable();      
            // Data spesifik
            $table->enum('status_tinggal', ['Sendiri', 'Bersama Keluarga', 'Bersama Saudara', 'Kos/Kontrak'])->nullable();
            $table->text('keperluan')->nullable();
            // Data administrasi
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
        Schema::dropIfExists('keterangan_lains');
    }
};
