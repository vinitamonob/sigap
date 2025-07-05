<?php

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
        Schema::create('calon_pasangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lingkungan_id')->nullable()->constrained('lingkungans')->nullOnDelete();
            $table->foreignId('ketua_lingkungan_id')->nullable()->constrained('ketua_lingkungans')->nullOnDelete();
            $table->foreignId('keluarga_id')->nullable()->constrained('keluargas')->nullOnDelete();
            $table->string('nama_lingkungan', 50)->nullable();
            $table->string('nama_ketua', 100)->nullable();
            $table->string('wilayah', 50)->nullable();
            $table->string('paroki', 100)->nullable();
            $table->string('agama', 20)->nullable();
            $table->enum('jenis_kelamin', ['Pria', 'Wanita']);
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('pekerjaan', 50)->nullable();
            $table->text('alamat_stlh_menikah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_pasangans');
    }
};
