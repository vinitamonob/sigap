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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); 
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete(); 
            $table->string('nomor_surat')->nullable();
            $table->date('tgl_surat')->nullable();
            $table->string('pekerjaan')->nullable();      
            $table->enum('status_tinggal', ['Sendiri', 'Bersama Keluarga', 'Bersama Saudara', 'Kos/Kontrak'])->nullable();
            $table->text('keperluan')->nullable();
            $table->string('nama_pastor')->nullable();
            $table->string('ttd_pastor')->nullable();            
            $table->string('ttd_ketua')->nullable(); 
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
