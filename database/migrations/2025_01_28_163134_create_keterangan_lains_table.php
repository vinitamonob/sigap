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
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Surat::class)->constrained()->cascadeOnDelete();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('nama_lingkungan')->nullable();
            $table->string('paroki');
            $table->string('nama_pastor')->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('jabatan_pekerjaan');
            $table->text('alamat');
            $table->string('telepon')->nullable();
            $table->enum('status_tinggal', ['Sendiri', 'Bersama Keluarga', 'Bersama Saudara', 'Kos/Kontrak']);
            $table->text('keperluan');
            $table->string('tanda_tangan_pastor')->nullable();
            $table->string('tanda_tangan_ketua')->nullable();
            $table->date('tanggal_surat')->nullable();
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
