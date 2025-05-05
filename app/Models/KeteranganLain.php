<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeteranganLain extends Model
{
    protected $fillable = [
        'user_id',
        'nomor_surat',
        'nama_ketua',
        'nama_lingkungan',
        'paroki',
        'nama_pastor',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jabatan_pekerjaan',
        'alamat',
        'telepon',
        'status_tinggal',
        'keperluan',
        'tanda_tangan_pastor',
        'tanda_tangan_ketua',
        'tanggal_surat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
