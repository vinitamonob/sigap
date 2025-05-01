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
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('kode_nomor_surat');
            $table->enum('perihal_surat', ['Keterangan Kematian', 'Keterangan Lain', 'Pendaftaran Baptis', 'Pendaftaran Kanonik Perkawinan']);
            $table->string('atas_nama');
            $table->string('nama_lingkungan');
            $table->string('file_surat');
            $table->enum('status', ['Menunggu', 'Selesai'])->default('Menunggu');
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
