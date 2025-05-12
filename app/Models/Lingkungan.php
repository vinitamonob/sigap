<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lingkungan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nama_lingkungan',
        'kode',
        'wilayah',
        'paroki',
    ];

    public function ketuaLingkungans()
    {
        return $this->hasMany(KetuaLingkungan::class);
    }

    public function detailUsers()
    {
        return $this->hasMany(DetailUser::class);
    }

    public function surats()
    {
        return $this->hasMany(Surat::class);
    }

        public function calonPasangans()
    {
        return $this->hasMany(CalonPasangan::class);
    }
}
