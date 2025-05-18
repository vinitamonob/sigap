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
        'lingkungan_id',
        'ketua_lingkungan_id',
        'keluarga_id',
        'nomor_surat',
        'tgl_surat',
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
        'ttd_pastor',
        'ttd_ketua',
        'ttd_ortu',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
        'tgl_belajar' => 'date',
        'tgl_baptis' => 'date',
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

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }
    
    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
