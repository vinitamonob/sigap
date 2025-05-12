<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeteranganKematian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'ketua_lingkungan_id',
        'nomor_surat',
        'nama_lengkap',
        'usia',
        'tempat_baptis',
        'no_baptis',
        'nama_ortu',
        'nama_pasangan',
        'tgl_kematian',
        'tgl_pemakaman',
        'tempat_pemakaman',
        'pelayanan_sakramen',
        'sakramen',
        'tgl_surat',
        'ttd_ketua',
    ];

    protected $casts = [
        'tgl_kematian' => 'date',
        'tgl_pemakaman' => 'date',
        'tgl_surat' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function ketuaLingkungan()
    {
        return $this->belongsTo(KetuaLingkungan::class);
    }
}
