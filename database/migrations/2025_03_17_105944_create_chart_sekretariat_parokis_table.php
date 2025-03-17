<?php

use App\Models\ArsipSurat;
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
        Schema::create('chart_sekretariat_parokis', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ArsipSurat::class)->constrained()->cascadeOnDelete();
            $table->integer('jumlah_keterangan_kematian')->default(0);
            $table->integer('jumlah_keterangan_lain')->default(0);
            $table->integer('jumlah_pendaftaran_baptis')->default(0);
            $table->integer('jumlah_pendaftaran_kanonik_perkawinan')->default(0);
            $table->integer('total_surat')->default(0); // Total keseluruhan
            $table->date('periode_bulan'); // Untuk menyimpan periode bulan/tahun (YYYY-MM-01)
            $table->timestamps();
            $table->index('periode_bulan'); // Indeks untuk mempercepat query
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_sekretariat_parokis');
    }
};
