<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalonPasangan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'ketua_lingkungan_id',
        'keluarga_id',
        'alamat_stlh_menikah',
        'pekerjaan',
        'pendidikan_terakhir',
        'agama',
        'jenis_kelamin',
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

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function calonSuami()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class, 'calon_suami_id');
    }

    public function calonIstri()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class, 'calon_istri_id');
    }
}
