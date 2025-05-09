<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalonIstri extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_detail_id',
        'alamat_stlh_menikah',
        'pekerjaan',
        'pendidikan_terakhir',
        'agama'
    ];

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class);
    }
}
