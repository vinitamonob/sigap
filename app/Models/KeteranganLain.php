<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeteranganLain extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'surat_id',
        'lingkungan_id',
        'ketua_lingkungan_id',
        'nomor_surat',
        'nama_pastor',
        'ttd_pastor',
        'pekerjaan',
        'status_tinggal',
        'keperluan',
        'tgl_surat',
        'ttd_ketua',
    ];

    protected $casts = [
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
    
    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
