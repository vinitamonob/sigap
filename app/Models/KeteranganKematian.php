<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeteranganKematian extends Model
{
    protected $fillable = [
        'nomor_surat',
        'nama_ketua',
        'ketua_lingkungan',
        'paroki',
        'nama_lengkap',
        'usia',
        'nama_orang_tua',
        'nama_pasangan',
        'tanggal_kematian',
        'tanggal_pemakaman',
        'tempat_pemakaman',
        'pelayan_sakramen',
        'sakramen_yang_diberikan',
        'tempat_no_buku_baptis',
        'tanggal_surat',
        'tanda_tangan'
    ];
}
