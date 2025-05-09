<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keluarga extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nama_ayah',
        'agama_ayah',
        'pekerjaan_ayah',
        'alamat_ayah',
        'nama_ibu',
        'agama_ibu',
        'pekerjaan_ibu',
        'alamat_ibu',
        'ttd_ayah',
        'ttd_ibu',
    ];

    public function userDetail()
    {
        return $this->hasMany(UserDetail::class);
    }
}
