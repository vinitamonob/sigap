<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeteranganKematian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'surat_id',
        'user_detail_id',
        'nomor_surat',
        'nama_lengkap',
        'usia',
        'nama_ortu',
        'nama_pasangan',
        'tgl_kematian',
        'tgl_pemakaman',
        'tempat_pemakaman',
        'pelayanan_sakramen',
        'sakramen',
        'ttd_ketua',
        'tgl_surat',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
    
    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class);
    }
}
