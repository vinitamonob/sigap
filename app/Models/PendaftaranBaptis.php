<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendaftaranBaptis extends Model
{
    protected $fillable = [
        'nama_lengkap',
        'nama_baptis',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_lengkap',
        'nomor_telepon',
        'agama_asal',
        'pendidikan_terakhir',
        'nama_ayah',
        'agama_ayah',
        'nama_ibu',
        'agama_ibu',
        'nama_keluarga_katolik_1',
        'hubungan_keluarga_katolik_1',
        'nama_keluarga_katolik_2',
        'hubungan_keluarga_katolik_2',
        'alamat_keluarga',
        'tanggal_mulai_belajar',
        'nama_wali_baptis',
        'alasan_masuk_katolik',
        'tanda_tangan_ortu',
        'tanda_tangan_pastor',
        'tanda_tangan_ketua',
        'tanggal_baptis',
        'status_ttd_pastor'
    ];
}
