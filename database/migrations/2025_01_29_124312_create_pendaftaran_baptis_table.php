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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete();
            $table->foreignId('keluarga_id')->nullable()->constrained('keluargas')->nullOnDelete();           
            // Data administrasi
            $table->string('nomor_surat')->nullable();
            $table->date('tgl_surat')->nullable();            
            // Data pribadi 
            $table->string('agama_asal')->nullable();
            $table->string('pendidikan_terakhir')->nullable();            
            // Data keluarga tambahan
            $table->string('nama_keluarga1')->nullable();
            $table->string('hub_keluarga1')->nullable();
            $table->string('nama_keluarga2')->nullable();
            $table->string('hub_keluarga2')->nullable();            
            // Proses baptis
            $table->date('tgl_belajar')->nullable();
            $table->date('tgl_baptis')->nullable();
            $table->string('wali_baptis')->nullable();
            $table->text('alasan_masuk')->nullable();           
            // Pastor dan tanda tangan
            $table->string('nama_pastor')->nullable();
            $table->string('ttd_pastor')->nullable();
            $table->string('ttd_ketua')->nullable();
            $table->string('ttd_ortu')->nullable(); // Bisa dari keluarga->ttd_ayah/ibu
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
