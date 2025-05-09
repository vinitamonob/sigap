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
        Schema::create('pendaftaran_baptis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->foreignId('user_detail_id')->nullable()->constrained('user_details')->nullOnDelete();
            $table->string('nomor_surat')->nullable();
            $table->string('agama_asal')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('nama_keluarga1')->nullable();
            $table->string('hub_keluarga1')->nullable();
            $table->string('nama_keluarga2')->nullable();
            $table->string('hub_keluarga2')->nullable();
            $table->date('tgl_belajar')->nullable();
            $table->date('tgl_baptis')->nullable();
            $table->string('wali_baptis')->nullable();
            $table->text('alasan_masuk')->nullable();
            $table->string('nama_pastor')->nullable();
            $table->string('ttd_ortu')->nullable();
            $table->string('ttd_ketua')->nullable();
            $table->string('ttd_pastor')->nullable();
            $table->date('tgl_surat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_baptis');
    }
};
