<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranKanonikPerkawinan extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_id',
        'user_detail_id',
        'calon_suami_id',
        'calon_istri_id',
        'nomor_surat',
        'lokasi_gereja',
        'tgl_pernikahan',
        'waktu_pernikahan',
        'nama_pastor',
        'ttd_istri',
        'ttd_ketua_istri',
        'ttd_suami',
        'ttd_ketua_suami',
        'ttd_pastor',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class);
    }

    public function calonSuami()
    {
        return $this->belongsTo(CalonSuami::class);
    }
    
    public function calonIstri()
    {
        return $this->belongsTo(CalonIstri::class);
    }
}
