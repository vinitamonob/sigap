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
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Surat::class)->constrained()->cascadeOnDelete();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('nama_lingkungan')->nullable();
            $table->string('paroki');
            $table->string('nama_lengkap');
            $table->string('nama_baptis');
            $table->enum('jenis_kelamin', ['Pria', 'Wanita']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat_lengkap');
            $table->string('nomor_telepon');
            $table->enum('agama_asal', ['Islam', 'Budha', 'Hindu', 'Protestan']);
            $table->enum('pendidikan_terakhir', ['TK', 'SD', 'SMP', 'SMA', 'Diploma/Sarjana']);
            $table->string('nama_ayah');
            $table->string('agama_ayah');
            $table->string('nama_ibu');
            $table->string('agama_ibu');
            $table->string('nama_keluarga_katolik_1')->nullable();
            $table->string('hubungan_keluarga_katolik_1')->nullable();
            $table->string('nama_keluarga_katolik_2')->nullable();
            $table->string('hubungan_keluarga_katolik_2')->nullable();
            $table->text('alamat_keluarga');
            $table->date('tanggal_mulai_belajar');
            $table->string('nama_wali_baptis');
            $table->text('alasan_masuk_katolik');
            $table->string('tanda_tangan_ortu')->nullable();
            $table->string('tanda_tangan_pastor')->nullable();
            $table->string('tanda_tangan_ketua')->nullable();
            $table->date('tanggal_baptis')->nullable();
            $table->date('tanggal_daftar')->nullable();
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
