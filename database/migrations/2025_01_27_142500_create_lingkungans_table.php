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
        Schema::create('lingkungans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lingkungan', 50);
            $table->string('kode', 10)->nullable();
            $table->string('wilayah', 50)->nullable();
            $table->string('paroki', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lingkungans');
    }
};
