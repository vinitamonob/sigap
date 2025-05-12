<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KetuaLingkungan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'mulai_jabatan',
        'akhir_jabatan',
        'aktif',
    ];

    protected $casts = [
        'mulai_jabatan' => 'date',
        'akhir_jabatan' => 'date',
        'aktif' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function surats()
    {
        return $this->hasMany(Surat::class);
    }

        public function calonPasangans()
    {
        return $this->hasMany(CalonPasangan::class);
    }

    public function keteranganKematians()
    {
        return $this->hasMany(KeteranganKematian::class);
    }

    public function keteranganLains()
    {
        return $this->hasMany(KeteranganLain::class);
    }

    public function pendaftaranBaptis()
    {
        return $this->hasMany(PendaftaranBaptis::class);
    }
}
