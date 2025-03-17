<?php

use App\Models\KeteranganKematian;
use App\Models\KeteranganLain;
use App\Models\PendaftaranBaptis;
use App\Models\PendaftaranKanonikPerkawinan;
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
        Schema::create('arsip_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KeteranganKematian::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(KeteranganLain::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PendaftaranBaptis::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PendaftaranKanonikPerkawinan::class)->constrained()->cascadeOnDelete();
            $table->string('kode_nomor_surat');
            $table->enum('perihal_surat', ['Keterangan Kematian', 'Keterangan Lain', 'Pendaftaran Baptis', 'Pendaftaran Kanonik Perkawinan']);
            $table->string('atas_nama');
            $table->string('nama_lingkungan');
            $table->string('file_surat');
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
