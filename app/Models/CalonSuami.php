<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalonSuami extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'umat_id',
        'alamat_stlh_menikah',
        'pekerjaan',
        'pendidikan_terakhir',
        'agama',
    ];

    public function umat()
    {
        return $this->belongsTo(Umat::class);
    }
}
