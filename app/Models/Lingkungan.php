<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lingkungan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nama_lingkungan',
        'kode',
        'wilayah',
        'paroki',
    ];

    public function ketuaLingkungan()
    {
        return $this->hasOne(KetuaLingkungan::class);
    }

    public function userDetail()
    {
        return $this->hasMany(UserDetail::class);
    }
}
