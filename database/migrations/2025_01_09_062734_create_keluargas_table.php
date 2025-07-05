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
        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('agama_ayah', 20)->nullable();
            $table->string('pekerjaan_ayah', 50)->nullable();
            $table->text('alamat_ayah')->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('agama_ibu', 20)->nullable();
            $table->string('pekerjaan_ibu', 50)->nullable();
            $table->text('alamat_ibu')->nullable();
            $table->string('ttd_ayah')->nullable();
            $table->string('ttd_ibu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluargas');
    }
};
