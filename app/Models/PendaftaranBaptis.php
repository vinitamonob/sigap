<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranBaptis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'surat_id',
        'umat_id',
        'nomor_surat',
        'agama_asal',
        'pendidikan_terakhir',
        'nama_keluarga1',
        'hub_keluarga1',
        'nama_keluarga2',
        'hub_keluarga2',
        'tgl_belajar',
        'tgl_baptis',
        'wali_baptis',
        'alasan_masuk',
        'nama_pastor',
        'ttd_ortu',
        'ttd_ketua',
        'ttd_pastor',
        'tgl_surat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
    
    public function umat()
    {
        return $this->belongsTo(Umat::class);
    }
}
