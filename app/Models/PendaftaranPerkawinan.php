<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranPerkawinan extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_id',
        'calon_suami_id',
        'calon_istri_id',
        'lingkungan_suami_id',
        'lingkungan_istri_id',
        'nomor_surat',
        'lokasi_gereja',
        'tgl_pernikahan',
        'waktu_pernikahan',
        'nama_pastor',
        'tgl_surat',
        'ttd_pastor',
        'ttd_calon_suami',
        'ttd_calon_istri',
        'ttd_ketua_suami',
        'ttd_ketua_istri',
    ];

    protected $casts = [
        'tgl_pernikahan' => 'date',
        'waktu_pernikahan' => 'datetime',
        'tgl_surat' => 'date',
    ];

    public function calonSuami()
    {
        return $this->belongsTo(CalonPasangan::class, 'calon_suami_id')->with('user');
    }

    public function calonIstri()
    {
        return $this->belongsTo(CalonPasangan::class, 'calon_istri_id')->with('user');
    }

    public function lingkunganSuami()
    {
        return $this->belongsTo(Lingkungan::class, 'lingkungan_suami_id');
    }

    public function lingkunganIstri()
    {
        return $this->belongsTo(Lingkungan::class, 'lingkungan_istri_id');
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
