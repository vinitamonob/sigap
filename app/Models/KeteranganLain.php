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
        'umat_id',
        'nomor_surat',
        'nama_pastor',
        'pekerjaan',
        'status_tinggal',
        'keperluan',
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
