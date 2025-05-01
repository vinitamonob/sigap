<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = [
        'user_id',
        'kode_nomor_surat',
        'perihal_surat',
        'atas_nama',
        'nama_lingkungan',
        'file_surat',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
