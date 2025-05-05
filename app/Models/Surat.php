<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = [
        'user_id',
        'kode_nomor_surat',
        'perihal_surat',
        'atas_nama',
        'nama_lingkungan',
        'file_surat',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function keteranganKematian()
    {
        return $this->hasMany(KeteranganKematian::class);
    }

    public function keteranganLain()
    {
        return $this->hasMany(KeteranganLain::class);
    }

    public function pendaftaranBaptis()
    {
        return $this->hasMany(PendaftaranBaptis::class);
    }

    public function pendaftaranKanonikPerkawinan()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class);
    }
}
