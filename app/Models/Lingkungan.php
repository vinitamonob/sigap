<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lingkungan extends Model
{
    protected $fillable = [
        'nama_lingkungan',
        'user_id',
        'kode'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }
}
