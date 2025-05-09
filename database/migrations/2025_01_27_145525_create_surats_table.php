<?php

use App\Models\User;
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
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_detail_id')->nullable()->constrained('user_details')->nullOnDelete();
            $table->string('kode_nomor_surat')->nullable();
            $table->string('nama_lingkungan')->nullable();
            $table->string('perihal');
            $table->string('atas_nama');
            $table->string('file_surat')->nullable();
            $table->enum('status', ['Menunggu', 'Selesai'])->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_surats');
    }
};
