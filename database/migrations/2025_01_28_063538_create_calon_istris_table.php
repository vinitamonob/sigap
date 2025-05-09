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
        Schema::create('calon_istris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umat_id')->constrained('umats')->cascadeOnDelete();
            $table->text('alamat_stlh_menikah')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('agama')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_istris');
    }
};
