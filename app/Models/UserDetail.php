<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'keluarga_id',
        'nama_baptis',
        'tempat_baptis',
        'tgl_baptis',
        'no_baptis',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon',
        'tanda_tangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function keteranganKematian()
    {
        return $this->hasMany(KeteranganKematian::class);
    }

    public function keteranganLain()
    {
        return $this->hasMany(KeteranganLain::class);
    }

    public function pendaftaranBaptis()
    {
        return $this->hasMany(PendaftaranBaptis::class);
    }

    public function pendaftaranKanonik()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class);
    }
}
