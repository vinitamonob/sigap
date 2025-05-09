<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeteranganLain extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'surat_id',
        'user_detail_id',
        'nomor_surat',
        'nama_pastor',
        'pekerjaan',
        'status_tinggal',
        'keperluan',
        'ttd_ketua',
        'ttd_pastor',
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
