<?php

use App\Models\User;
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
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->enum('jenis_surat', ['keterangan_kematian', 'keterangan_lain', 'pendaftaran_baptis', 'pendaftaran_perkawinan']);
            $table->string('nomor_surat', 20)->nullable();
            $table->string('perihal');
            $table->date('tgl_surat')->nullable();
            $table->enum('status', ['menunggu', 'selesai'])->default('menunggu');
            $table->string('file_surat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_surats');
    }
};
