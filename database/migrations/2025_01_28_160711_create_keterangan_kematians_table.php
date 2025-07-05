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
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete(); 
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); 
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete();
            $table->string('nomor_surat', 20)->nullable();
            $table->date('tgl_surat')->nullable();
            $table->string('nama_lengkap', 100)->nullable(); 
            $table->integer('usia')->nullable();
            $table->string('tempat_baptis', 100)->nullable(); 
            $table->string('no_baptis', 20)->nullable(); 
            $table->string('nama_ortu', 100)->nullable(); 
            $table->string('nama_pasangan', 100)->nullable();
            $table->date('tgl_kematian')->nullable();
            $table->date('tgl_pemakaman')->nullable();
            $table->string('tempat_pemakaman', 50)->nullable();
            $table->string('pelayanan_sakramen', 20)->nullable();
            $table->string('sakramen', 20)->nullable();
            $table->string('ttd_ketua')->nullable(); 
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
