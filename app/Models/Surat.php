<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_detail_id',
        'kode_nomor_surat',
        'nama_lingkungan',
        'perihal',
        'atas_nama',
        'file_surat',
        'status',
    ];

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class);
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

    public function pendaftaranKanonik()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class);
    }
}
