<?php

use App\Models\ArsipSurat;
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
        Schema::create('chart_ketua_lingkungans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ArsipSurat::class)->constrained()->cascadeOnDelete();
            $table->string('nama_lingkungan');
            $table->integer('jumlah_keterangan_kematian')->default(0);
            $table->integer('jumlah_keterangan_lain')->default(0);
            $table->integer('jumlah_pendaftaran_baptis')->default(0);
            $table->integer('jumlah_pendaftaran_kanonik_perkawinan')->default(0);
            $table->date('periode_bulan'); // Untuk menyimpan periode bulan/tahun (YYYY-MM-01)
            $table->timestamps();
            $table->index(['periode_bulan']); // Membuat index composite untuk mempercepat query
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_ketua_lingkungans');
    }
};
